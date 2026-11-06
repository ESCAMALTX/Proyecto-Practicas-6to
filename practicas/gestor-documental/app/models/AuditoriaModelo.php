<?php
/**
 * =====================================================================
 * Archivo: app/models/Auditoria.php
 * Descripcion: Consulta de la bitacora de auditoria (RF-017).
 *
 * NOTA: la clase que ESCRIBE los eventos es app/core/Auditoria.php.
 *       Esta clase solo LEE y filtra los registros.
 * =====================================================================
 */

class AuditoriaModelo
{
    /**
     * Listado paginado de la bitacora con filtros.
     */
    public static function listar(array $filtros, int $limite = ITEMS_POR_PAGINA, int $offset = 0): array
    {
        [$where, $params] = self::construirFiltros($filtros);

        $sql = "SELECT * FROM v_auditoria_detalle
                 WHERE {$where}
                 ORDER BY fecha_evento DESC
                 LIMIT ? OFFSET ?";

        $params[] = $limite;
        $params[] = $offset;

        return Database::todos($sql, $params);
    }

    public static function contar(array $filtros): int
    {
        [$where, $params] = self::construirFiltros($filtros);

        return (int) Database::valor(
            "SELECT COUNT(*) FROM v_auditoria_detalle WHERE {$where}",
            $params
        );
    }

    private static function construirFiltros(array $filtros): array
    {
        $condiciones = [];
        $params      = [];

        if (!empty($filtros['usuario'])) {
            $condiciones[] = 'usuario LIKE ?';
            $params[] = '%' . $filtros['usuario'] . '%';
        }

        if (!empty($filtros['modulo'])) {
            $condiciones[] = 'modulo = ?';
            $params[] = $filtros['modulo'];
        }

        if (!empty($filtros['accion'])) {
            $condiciones[] = 'accion = ?';
            $params[] = $filtros['accion'];
        }

        if (!empty($filtros['resultado'])) {
            $condiciones[] = 'resultado = ?';
            $params[] = $filtros['resultado'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $condiciones[] = 'DATE(fecha_evento) >= ?';
            $params[] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $condiciones[] = 'DATE(fecha_evento) <= ?';
            $params[] = $filtros['fecha_hasta'];
        }

        if (!empty($filtros['busqueda'])) {
            $condiciones[] = 'descripcion LIKE ?';
            $params[] = '%' . $filtros['busqueda'] . '%';
        }

        return [empty($condiciones) ? '1=1' : implode(' AND ', $condiciones), $params];
    }

    /**
     * Detalle completo de un evento, incluyendo los JSON de cambio.
     */
    public static function detalle(int $idAuditoria): ?array
    {
        return Database::uno(
            'SELECT a.*, u.nombre_completo AS usuario_nombre, u.correo
               FROM auditoria a
               LEFT JOIN usuarios u ON u.id_usuario = a.id_usuario
              WHERE a.id_auditoria = ?',
            [$idAuditoria]
        );
    }

    /**
     * Modulos registrados, para poblar el filtro.
     */
    public static function modulos(): array
    {
        return Database::todos(
            'SELECT DISTINCT modulo FROM auditoria ORDER BY modulo'
        );
    }

    /**
     * Acciones registradas, para poblar el filtro.
     */
    public static function acciones(): array
    {
        return Database::todos(
            'SELECT DISTINCT accion FROM auditoria ORDER BY accion'
        );
    }

    /**
     * Resumen de eventos por modulo (ultimos 30 dias).
     */
    public static function resumenPorModulo(int $dias = 30): array
    {
        return Database::todos(
            'SELECT modulo,
                    COUNT(*) AS total,
                    SUM(CASE WHEN resultado = "FALLO" THEN 1 ELSE 0 END) AS fallos,
                    SUM(CASE WHEN resultado = "ADVERTENCIA" THEN 1 ELSE 0 END) AS advertencias
               FROM auditoria
              WHERE fecha_evento >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
              GROUP BY modulo
              ORDER BY total DESC',
            [$dias]
        );
    }

    /**
     * Eventos de seguridad (accesos denegados, intentos fallidos).
     */
    public static function eventosSeguridad(int $limite = 20): array
    {
        return Database::todos(
            "SELECT * FROM v_auditoria_detalle
              WHERE resultado <> 'EXITO'
                 OR accion IN ('LOGIN','LOGOUT','ACCESO_DENEGADO')
              ORDER BY fecha_evento DESC
              LIMIT ?",
            [$limite]
        );
    }

    /**
     * Usuarios con mas actividad registrada.
     */
    public static function usuariosMasActivos(int $limite = 10): array
    {
        return Database::todos(
            'SELECT usuario, COUNT(*) AS total_eventos, MAX(fecha_evento) AS ultima_actividad
               FROM v_auditoria_detalle
              GROUP BY usuario
              ORDER BY total_eventos DESC
              LIMIT ?',
            [$limite]
        );
    }

    /**
     * Exporta la bitacora filtrada a un archivo CSV descargable.
     */
    public static function exportarCsv(array $filtros, int $maximo = 10000): string
    {
        [$where, $params] = self::construirFiltros($filtros);

        $filas = Database::todos(
            "SELECT * FROM v_auditoria_detalle WHERE {$where}
              ORDER BY fecha_evento DESC LIMIT ?",
            array_merge($params, [$maximo])
        );

        $lineas   = [];
        $lineas[] = 'ID;Fecha;Usuario;Modulo;Accion;Entidad;ID Entidad;Descripcion;IP;Resultado';

        foreach ($filas as $fila) {
            $lineas[] = implode(';', [
                $fila['id_auditoria'],
                $fila['fecha_evento'],
                str_replace(';', ',', (string) $fila['usuario']),
                $fila['modulo'],
                $fila['accion'],
                (string) $fila['entidad'],
                (string) $fila['id_entidad'],
                str_replace(';', ',', (string) $fila['descripcion']),
                (string) $fila['direccion_ip'],
                $fila['resultado'],
            ]);
        }

        return "\xEF\xBB\xBF" . implode("\n", $lineas); // BOM para Excel
    }

    /**
     * Elimina registros anteriores a una fecha (politica de retencion).
     * RNF-020: los logs se conservan minimo 12 meses.
     */
    public static function depurarAnteriorA(string $fecha): int
    {
        return Database::eliminar('auditoria', 'fecha_evento < ?', [$fecha]);
    }
}
