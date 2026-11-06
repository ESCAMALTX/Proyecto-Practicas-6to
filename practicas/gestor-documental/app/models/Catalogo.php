<?php
/**
 * =====================================================================
 * Archivo: app/models/Catalogo.php
 * Descripcion: Catalogos maestros (categorias, casos, areas) y
 *              bitacora de auditoria.
 * =====================================================================
 */

class Catalogo
{
    // =================================================================
    // CATEGORIAS
    // =================================================================

    public static function categorias(string $busqueda = '', bool $soloActivas = true): array
    {
        $sql    = 'SELECT * FROM categorias WHERE 1=1';
        $params = [];

        if ($soloActivas) {
            $sql .= ' AND activo = 1';
        }

        if ($busqueda !== '') {
            $sql .= ' AND (nombre LIKE ? OR descripcion LIKE ?)';
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
        }

        return Database::todos($sql . ' ORDER BY nombre', $params);
    }

    public static function categoriaPorId(int $id): ?array
    {
        return Database::uno('SELECT * FROM categorias WHERE id_categoria = ?', [$id]);
    }

    /**
     * Listado con el conteo de documentos asociados.
     */
    public static function categoriasConTotales(): array
    {
        return Database::todos(
            'SELECT c.*,
                    COUNT(d.id_documento) AS total_documentos
               FROM categorias c
               LEFT JOIN documentos d ON d.id_categoria = c.id_categoria
                                     AND d.estado <> "ELIMINADO"
              GROUP BY c.id_categoria
              ORDER BY c.nombre'
        );
    }

    public static function crearCategoria(array $datos): int
    {
        return Database::insertar('categorias', [
            'nombre'      => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'activo'      => $datos['activo'] ?? 1,
        ]);
    }

    public static function actualizarCategoria(int $id, array $datos): int
    {
        return Database::actualizar('categorias', [
            'nombre'      => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'activo'      => $datos['activo'] ?? 1,
        ], 'id_categoria = ?', [$id]);
    }

    /**
     * Desactiva una categoria. Regla de negocio: no se elimina si
     * tiene documentos asociados, para no romper la trazabilidad.
     */
    public static function desactivarCategoria(int $id): array
    {
        $asociados = (int) Database::valor(
            'SELECT COUNT(*) FROM documentos WHERE id_categoria = ?',
            [$id]
        );

        if ($asociados > 0) {
            Database::actualizar('categorias', ['activo' => 0], 'id_categoria = ?', [$id]);
            return ['ok' => true, 'desactivada' => true,
                    'mensaje' => "La categoria tiene {$asociados} documento(s) asociado(s). "
                               . 'Se desactivo en lugar de eliminarse.'];
        }

        Database::eliminar('categorias', 'id_categoria = ?', [$id]);
        return ['ok' => true, 'desactivada' => false, 'mensaje' => 'Categoria eliminada correctamente.'];
    }

    public static function existeCategoria(string $nombre, int $exceptoId = 0): bool
    {
        $sql    = 'SELECT COUNT(*) FROM categorias WHERE nombre = ?';
        $params = [$nombre];

        if ($exceptoId > 0) {
            $sql .= ' AND id_categoria <> ?';
            $params[] = $exceptoId;
        }

        return (int) Database::valor($sql, $params) > 0;
    }

    // =================================================================
    // CASOS
    // =================================================================

    public static function casos(string $busqueda = '', string $estado = '', int $limite = 0, int $offset = 0): array
    {
        $sql = 'SELECT c.*, a.nombre AS area,
                       (SELECT COUNT(*) FROM documentos d WHERE d.id_caso = c.id_caso) AS total_documentos
                  FROM casos c
                  LEFT JOIN areas a ON a.id_area = c.id_area
                 WHERE 1=1';
        $params = [];

        if ($busqueda !== '') {
            $sql .= ' AND (c.codigo LIKE ? OR c.titulo LIKE ?)';
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
        }

        if ($estado !== '') {
            $sql .= ' AND c.estado = ?';
            $params[] = $estado;
        }

        $sql .= ' ORDER BY c.fecha_apertura DESC';

        if ($limite > 0) {
            $sql .= ' LIMIT ? OFFSET ?';
            $params[] = $limite;
            $params[] = $offset;
        }

        return Database::todos($sql, $params);
    }

    public static function contarCasos(string $busqueda = '', string $estado = ''): int
    {
        $sql    = 'SELECT COUNT(*) FROM casos WHERE 1=1';
        $params = [];

        if ($busqueda !== '') {
            $sql .= ' AND (codigo LIKE ? OR titulo LIKE ?)';
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
        }

        if ($estado !== '') {
            $sql .= ' AND estado = ?';
            $params[] = $estado;
        }

        return (int) Database::valor($sql, $params);
    }

    public static function casoPorId(int $id): ?array
    {
        return Database::uno(
            'SELECT c.*, a.nombre AS area
               FROM casos c
               LEFT JOIN areas a ON a.id_area = c.id_area
              WHERE c.id_caso = ?',
            [$id]
        );
    }

    public static function crearCaso(array $datos): int
    {
        return Database::insertar('casos', [
            'codigo'         => $datos['codigo'],
            'titulo'         => $datos['titulo'],
            'descripcion'    => $datos['descripcion'] ?? null,
            'id_area'        => !empty($datos['id_area']) ? (int) $datos['id_area'] : null,
            'estado'         => $datos['estado'] ?? 'ABIERTO',
            'fecha_apertura' => $datos['fecha_apertura'] ?? date('Y-m-d'),
            'fecha_cierre'   => !empty($datos['fecha_cierre']) ? $datos['fecha_cierre'] : null,
        ]);
    }

    public static function actualizarCaso(int $id, array $datos): int
    {
        // Si el caso se cierra y no hay fecha, se registra hoy
        $fechaCierre = $datos['fecha_cierre'] ?? null;
        if (in_array($datos['estado'], ['CERRADO', 'ARCHIVADO'], true) && empty($fechaCierre)) {
            $fechaCierre = date('Y-m-d');
        }

        return Database::actualizar('casos', [
            'titulo'         => $datos['titulo'],
            'descripcion'    => $datos['descripcion'] ?? null,
            'id_area'        => !empty($datos['id_area']) ? (int) $datos['id_area'] : null,
            'estado'         => $datos['estado'] ?? 'ABIERTO',
            'fecha_apertura' => $datos['fecha_apertura'],
            'fecha_cierre'   => $fechaCierre,
        ], 'id_caso = ?', [$id]);
    }

    public static function desactivarCaso(int $id): array
    {
        $asociados = (int) Database::valor('SELECT COUNT(*) FROM documentos WHERE id_caso = ?', [$id]);

        if ($asociados > 0) {
            Database::actualizar('casos', ['estado' => 'ARCHIVADO'], 'id_caso = ?', [$id]);
            return ['ok' => true,
                    'mensaje' => "El caso tiene {$asociados} documento(s). Se archivo en lugar de eliminarse."];
        }

        Database::eliminar('casos', 'id_caso = ?', [$id]);
        return ['ok' => true, 'mensaje' => 'Caso eliminado correctamente.'];
    }

    public static function existeCaso(string $codigo, int $exceptoId = 0): bool
    {
        $sql    = 'SELECT COUNT(*) FROM casos WHERE codigo = ?';
        $params = [$codigo];

        if ($exceptoId > 0) {
            $sql .= ' AND id_caso <> ?';
            $params[] = $exceptoId;
        }

        return (int) Database::valor($sql, $params) > 0;
    }

    // =================================================================
    // AREAS
    // =================================================================

    public static function areas(bool $soloActivas = true): array
    {
        $sql = 'SELECT * FROM areas';
        if ($soloActivas) {
            $sql .= ' WHERE activo = 1';
        }
        return Database::todos($sql . ' ORDER BY nombre');
    }

    public static function areaPorId(int $id): ?array
    {
        return Database::uno('SELECT * FROM areas WHERE id_area = ?', [$id]);
    }

    public static function crearArea(array $datos): int
    {
        return Database::insertar('areas', [
            'nombre'      => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'activo'      => $datos['activo'] ?? 1,
        ]);
    }

    public static function actualizarArea(int $id, array $datos): int
    {
        return Database::actualizar('areas', [
            'nombre'      => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'activo'      => $datos['activo'] ?? 1,
        ], 'id_area = ?', [$id]);
    }

    public static function existeArea(string $nombre, int $exceptoId = 0): bool
    {
        $sql    = 'SELECT COUNT(*) FROM areas WHERE nombre = ?';
        $params = [$nombre];

        if ($exceptoId > 0) {
            $sql .= ' AND id_area <> ?';
            $params[] = $exceptoId;
        }

        return (int) Database::valor($sql, $params) > 0;
    }

    // =================================================================
    // CONFIGURACION
    // =================================================================

    public static function configuracion(): array
    {
        $filas = Database::todos('SELECT clave, valor, descripcion FROM configuracion ORDER BY clave');
        $mapa  = [];

        foreach ($filas as $fila) {
            $mapa[$fila['clave']] = $fila['valor'];
        }

        return $mapa;
    }

    public static function guardarConfiguracion(array $valores): void
    {
        Database::iniciarTransaccion();

        try {
            foreach ($valores as $clave => $valor) {
                Database::consultar(
                    'UPDATE configuracion SET valor = ? WHERE clave = ?',
                    [(string) $valor, $clave]
                );
            }
            Database::confirmar();
        } catch (Throwable $e) {
            Database::revertir();
            throw $e;
        }
    }

    /**
     * Listado completo de la tabla configuracion para el formulario.
     */
    public static function configuracionDetallada(): array
    {
        return Database::todos('SELECT * FROM configuracion ORDER BY clave');
    }
}
