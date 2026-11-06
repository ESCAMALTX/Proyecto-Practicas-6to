<?php
/**
 * =====================================================================
 * Archivo: app/models/Respaldo.php
 * Descripcion: Generacion de copias de seguridad y consulta de la
 *              bitacora de auditoria (RF-017, RF-019, RNF-011).
 * =====================================================================
 */

class Respaldo
{
    /**
     * Genera un respaldo de la base de datos en formato SQL.
     *
     * Estrategia: se leen los datos con PDO y se escriben sentencias
     * INSERT. No requiere mysqldump ni permisos especiales, por lo que
     * funciona en cualquier instalacion de XAMPP.
     *
     * @param string $tipo COMPLETO (estructura + datos) o SOLO_DATOS
     */
    public static function generar(int $idUsuario, string $tipo = 'COMPLETO'): array
    {
        if (!is_dir(RUTA_RESPALDOS) && !@mkdir(RUTA_RESPALDOS, 0775, true)) {
            return ['ok' => false, 'error' => 'No fue posible crear la carpeta de respaldos.'];
        }

        $marca         = date('Ymd_His');
        $nombreArchivo = "respaldo_{$marca}.sql";
        $rutaCompleta  = RUTA_RESPALDOS . DIRECTORY_SEPARATOR . $nombreArchivo;

        $idRespaldo = Database::insertar('respaldos', [
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo'   => 'respaldos/' . $nombreArchivo,
            'tipo'           => $tipo,
            'estado'         => 'EN_PROCESO',
            'id_usuario'     => $idUsuario,
        ]);

        try {
            $archivo = fopen($rutaCompleta, 'w');
            if ($archivo === false) {
                throw new RuntimeException('No fue posible abrir el archivo de respaldo.');
            }

            self::escribirEncabezado($archivo, $tipo);

            if ($tipo === 'COMPLETO') {
                self::escribirEstructura($archivo);
            }

            self::escribirDatos($archivo);
            self::escribirEpilogo($archivo);

            fclose($archivo);

            $peso = (int) filesize($rutaCompleta);

            Database::actualizar('respaldos', [
                'estado'     => 'COMPLETADO',
                'peso_bytes' => $peso,
            ], 'id_respaldo = ?', [$idRespaldo]);

            return ['ok' => true, 'id_respaldo' => $idRespaldo,
                    'archivo' => $nombreArchivo, 'peso' => $peso];
        } catch (Throwable $e) {
            if (isset($archivo) && is_resource($archivo)) {
                fclose($archivo);
            }
            @unlink($rutaCompleta);

            Database::actualizar('respaldos', [
                'estado'      => 'FALLIDO',
                'observacion' => mb_substr($e->getMessage(), 0, 300),
            ], 'id_respaldo = ?', [$idRespaldo]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    // -----------------------------------------------------------------
    // Escritura del archivo
    // -----------------------------------------------------------------

    private static function escribirEncabezado($archivo, string $tipo): void
    {
        $usuario = Sesion::dato('usuario', 'sistema');

        fwrite($archivo, "-- =====================================================\n");
        fwrite($archivo, "-- RESPALDO: " . NOMBRE_SISTEMA . "\n");
        fwrite($archivo, "-- Tipo: {$tipo}\n");
        fwrite($archivo, "-- Fecha: " . date('Y-m-d H:i:s') . "\n");
        fwrite($archivo, "-- Generado por: {$usuario}\n");
        fwrite($archivo, "-- Base de datos: " . DB_NOMBRE . "\n");
        fwrite($archivo, "-- =====================================================\n\n");
        fwrite($archivo, "SET NAMES utf8mb4;\n");
        fwrite($archivo, "SET FOREIGN_KEY_CHECKS = 0;\n\n");
    }

    private static function escribirEpilogo($archivo): void
    {
        fwrite($archivo, "\nSET FOREIGN_KEY_CHECKS = 1;\n");
        fwrite($archivo, "-- Fin del respaldo\n");
    }

    /**
     * Escribe las sentencias CREATE TABLE leyendo INFORMATION_SCHEMA.
     */
    private static function escribirEstructura($archivo): void
    {
        $tablas = Database::todos(
            'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
              WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = "BASE TABLE"
              ORDER BY TABLE_NAME',
            [DB_NOMBRE]
        );

        fwrite($archivo, "-- -----------------------------------------------------\n");
        fwrite($archivo, "-- ESTRUCTURA DE TABLAS\n");
        fwrite($archivo, "-- -----------------------------------------------------\n\n");

        foreach ($tablas as $tabla) {
            $nombre = $tabla['TABLE_NAME'];
            $crear  = Database::uno("SHOW CREATE TABLE `{$nombre}`");

            if ($crear) {
                fwrite($archivo, "DROP TABLE IF EXISTS `{$nombre}`;\n");
                fwrite($archivo, ($crear['Create Table'] ?? '') . ";\n\n");
            }
        }
    }

    /**
     * Escribe los datos de todas las tablas como sentencias INSERT.
     */
    private static function escribirDatos($archivo): void
    {
        $tablas = Database::todos(
            'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
              WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = "BASE TABLE"
              ORDER BY TABLE_NAME',
            [DB_NOMBRE]
        );

        fwrite($archivo, "-- -----------------------------------------------------\n");
        fwrite($archivo, "-- DATOS\n");
        fwrite($archivo, "-- -----------------------------------------------------\n\n");

        foreach ($tablas as $tabla) {
            $nombre = $tabla['TABLE_NAME'];
            $filas  = Database::todos("SELECT * FROM `{$nombre}`");

            if (empty($filas)) {
                continue;
            }

            fwrite($archivo, "-- Tabla: {$nombre} (" . count($filas) . " registros)\n");

            foreach ($filas as $fila) {
                $columnas = array_map(fn($col) => "`{$col}`", array_keys($fila));
                $valores  = array_map(
                    function ($valor) {
                        if ($valor === null) {
                            return 'NULL';
                        }
                        if (is_int($valor) || is_float($valor)) {
                            return (string) $valor;
                        }
                        return Database::obtener()->quote((string) $valor);
                    },
                    array_values($fila)
                );

                fwrite($archivo, "INSERT INTO `{$nombre}` ("
                    . implode(', ', $columnas) . ') VALUES ('
                    . implode(', ', $valores) . ");\n");
            }

            fwrite($archivo, "\n");
        }
    }

    // -----------------------------------------------------------------
    // Consulta y gestion
    // -----------------------------------------------------------------

    public static function listar(int $limite = 50): array
    {
        return Database::todos(
            'SELECT r.*, u.nombre_completo AS usuario
               FROM respaldos r
               INNER JOIN usuarios u ON u.id_usuario = r.id_usuario
              ORDER BY r.fecha_respaldo DESC
              LIMIT ?',
            [$limite]
        );
    }

    public static function porId(int $id): ?array
    {
        return Database::uno('SELECT * FROM respaldos WHERE id_respaldo = ?', [$id]);
    }

    /**
     * Ruta absoluta del archivo de respaldo.
     */
    public static function rutaAbsoluta(array $respaldo): string
    {
        return RUTA_BASE . DIRECTORY_SEPARATOR
             . str_replace('/', DIRECTORY_SEPARATOR, $respaldo['ruta_archivo']);
    }

    /**
     * Elimina un archivo de respaldo del disco y su registro.
     */
    public static function eliminar(int $id): array
    {
        $respaldo = self::porId($id);

        if (!$respaldo) {
            return ['ok' => false, 'error' => 'El respaldo indicado no existe.'];
        }

        $ruta = self::rutaAbsoluta($respaldo);

        if (is_file($ruta) && !@unlink($ruta)) {
            return ['ok' => false, 'error' => 'No fue posible eliminar el archivo del servidor.'];
        }

        Database::eliminar('respaldos', 'id_respaldo = ?', [$id]);

        return ['ok' => true];
    }

    /**
     * Lista los respaldos fisicos presentes en la carpeta.
     */
    public static function archivosEnDisco(): array
    {
        if (!is_dir(RUTA_RESPALDOS)) {
            return [];
        }

        $archivos = [];
        foreach (glob(RUTA_RESPALDOS . DIRECTORY_SEPARATOR . '*.sql') as $ruta) {
            $archivos[] = [
                'nombre' => basename($ruta),
                'peso'   => filesize($ruta),
                'fecha'  => date('Y-m-d H:i:s', filemtime($ruta)),
            ];
        }

        usort($archivos, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));

        return $archivos;
    }
}
