<?php
/**
 * =====================================================================
 * Archivo: config/database.php
 * Descripcion: Conexion a MySQL mediante PDO (patron Singleton).
 *              Todas las consultas usan sentencias preparadas para
 *              prevenir inyeccion SQL (RNF-001).
 * =====================================================================
 */

class Database
{
    private static ?PDO $conexion = null;

    /**
     * Devuelve la instancia unica de PDO.
     */
    public static function obtener(): PDO
    {
        if (self::$conexion === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                DB_HOST,
                DB_PUERTO,
                DB_NOMBRE,
                DB_CHARSET
            );

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
            ];

            try {
                self::$conexion = new PDO($dsn, DB_USUARIO, DB_CLAVE, $opciones);
            } catch (PDOException $e) {
                self::registrarFallo($e->getMessage());
                die('No fue posible conectar con la base de datos. '
                  . 'Verifique que MySQL este iniciado y que la base de datos "'
                  . DB_NOMBRE . '" exista.');
            }
        }

        return self::$conexion;
    }

    /**
     * Ejecuta una consulta preparada y devuelve la sentencia.
     */
    public static function consultar(string $sql, array $parametros = []): PDOStatement
    {
        $stmt = self::obtener()->prepare($sql);
        $stmt->execute($parametros);
        return $stmt;
    }

    /**
     * Devuelve todos los registros de una consulta.
     */
    public static function todos(string $sql, array $parametros = []): array
    {
        return self::consultar($sql, $parametros)->fetchAll();
    }

    /**
     * Devuelve un unico registro o null.
     */
    public static function uno(string $sql, array $parametros = []): ?array
    {
        $fila = self::consultar($sql, $parametros)->fetch();
        return $fila === false ? null : $fila;
    }

    /**
     * Devuelve el valor de la primera columna del primer registro.
     */
    public static function valor(string $sql, array $parametros = [])
    {
        $fila = self::consultar($sql, $parametros)->fetch(PDO::FETCH_NUM);
        return $fila === false ? null : $fila[0];
    }

    /**
     * Inserta un registro y devuelve el id generado.
     */
    public static function insertar(string $tabla, array $datos): int
    {
        $columnas     = implode(', ', array_keys($datos));
        $marcadores   = implode(', ', array_fill(0, count($datos), '?'));

        $sql = "INSERT INTO {$tabla} ({$columnas}) VALUES ({$marcadores})";
        self::consultar($sql, array_values($datos));

        return (int) self::obtener()->lastInsertId();
    }

    /**
     * Actualiza registros y devuelve la cantidad de filas afectadas.
     */
    public static function actualizar(string $tabla, array $datos, string $condicion, array $parametrosCondicion = []): int
    {
        $asignaciones = implode(', ', array_map(fn($c) => "{$c} = ?", array_keys($datos)));

        $sql = "UPDATE {$tabla} SET {$asignaciones} WHERE {$condicion}";
        $stmt = self::consultar($sql, array_merge(array_values($datos), $parametrosCondicion));

        return $stmt->rowCount();
    }

    /**
     * Elimina registros y devuelve la cantidad de filas afectadas.
     */
    public static function eliminar(string $tabla, string $condicion, array $parametros = []): int
    {
        $sql = "DELETE FROM {$tabla} WHERE {$condicion}";
        return self::consultar($sql, $parametros)->rowCount();
    }

    /**
     * Inicia una transaccion.
     */
    public static function iniciarTransaccion(): void
    {
        self::obtener()->beginTransaction();
    }

    /**
     * Confirma la transaccion activa.
     */
    public static function confirmar(): void
    {
        self::obtener()->commit();
    }

    /**
     * Revierte la transaccion activa.
     */
    public static function revertir(): void
    {
        if (self::obtener()->inTransaction()) {
            self::obtener()->rollBack();
        }
    }

    /**
     * Guarda el fallo de conexion en el archivo de log.
     */
    private static function registrarFallo(string $mensaje): void
    {
        $dir = dirname(RUTA_LOG_ERRORES);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents(
            RUTA_LOG_ERRORES,
            sprintf("[%s] CONEXION: %s%s", date('Y-m-d H:i:s'), $mensaje, PHP_EOL),
            FILE_APPEND
        );
    }
}
