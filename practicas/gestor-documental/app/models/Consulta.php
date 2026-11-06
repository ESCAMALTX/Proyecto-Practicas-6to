<?php
/**
 * =====================================================================
 * Archivo: app/models/Consulta.php
 * Descripcion: Registro de consultas realizadas y auditoria.
 *
 * IMPORTANTE: este modulo sustituye al antiguo chat con IA. El usuario
 * formula una consulta en lenguaje natural y el sistema devuelve los
 * documentos y fragmentos que coinciden por palabras clave, sin
 * generar texto ni interpretar el contenido.
 * =====================================================================
 */

class Consulta
{
    /**
     * Registra una consulta y sus resultados.
     *
     * @param string $texto       Lo que escribio el usuario.
     * @param array  $resultados  Documentos encontrados.
     * @param array  $filtros     Filtros aplicados.
     * @param int    $tiempoMs    Duracion de la busqueda.
     */
    public static function registrar(
        int $idUsuario,
        string $texto,
        array $resultados,
        array $filtros,
        int $tiempoMs
    ): int {
        $idConsulta = Database::insertar('consultas', [
            'id_usuario'         => $idUsuario,
            'texto_consulta'     => mb_substr($texto, 0, 500),
            'tipo_consulta'      => $filtros['tipo'] ?? 'RAPIDA',
            'filtros_aplicados'  => empty($filtros) ? null : Helper::aJson($filtros),
            'total_resultados'   => count($resultados),
            'resultado_encontrado' => empty($resultados) ? 0 : 1,
            'tiempo_ms'          => $tiempoMs,
            'direccion_ip'       => Helper::ip(),
        ]);

        // Se guardan hasta 20 resultados por consulta
        foreach (array_slice($resultados, 0, 20) as $resultado) {
            $idVersion = $resultado['id_version'] ?? null;

            Database::insertar('consulta_resultados', [
                'id_consulta'  => $idConsulta,
                'id_documento' => (int) $resultado['id_documento'],
                'id_version'   => $idVersion ? (int) $idVersion : null,
                'fragmento'    => self::fragmento($resultado, $texto),
                'pagina'       => $resultado['paginas'] ?? null,
                'relevancia'   => $resultado['puntaje'] ?? 0,
            ]);
        }

        return $idConsulta;
    }

    /**
     * Extrae el fragmento donde aparece el termino buscado.
     */
    private static function fragmento(array $resultado, string $termino): string
    {
        $fuente = $resultado['texto_extraido']
               ?? $resultado['descripcion']
               ?? $resultado['palabra_clave']
               ?? '';

        return mb_substr(Helper::extracto($fuente, $termino, 300), 0, 500);
    }

    /**
     * Historial de consultas. El asesor ve solo las suyas; el
     * administrador ve todas (RF-015, RF-030).
     */
    public static function historial(array $usuario, string $busqueda = '', int $limite = ITEMS_POR_PAGINA, int $offset = 0): array
    {
        [$where, $params] = self::filtroHistorial($usuario, $busqueda);

        $sql = "SELECT c.*, u.nombre_completo AS usuario, u.id_area
                  FROM consultas c
                  INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
                 WHERE {$where}
                 ORDER BY c.fecha_consulta DESC
                 LIMIT ? OFFSET ?";

        $params[] = $limite;
        $params[] = $offset;

        return Database::todos($sql, $params);
    }

    public static function contarHistorial(array $usuario, string $busqueda = ''): int
    {
        [$where, $params] = self::filtroHistorial($usuario, $busqueda);

        return (int) Database::valor(
            "SELECT COUNT(*) FROM consultas c INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
              WHERE {$where}",
            $params
        );
    }

    private static function filtroHistorial(array $usuario, string $busqueda): array
    {
        $condiciones = [];
        $params      = [];

        // Un usuario de nivel 1 solo ve su propio historial
        if ((int) ($usuario['nivel_acceso'] ?? 1) < 2) {
            $condiciones[] = 'c.id_usuario = ?';
            $params[] = $usuario['id_usuario'];
        }

        if ($busqueda !== '') {
            $condiciones[] = 'c.texto_consulta LIKE ?';
            $params[] = "%{$busqueda}%";
        }

        return [empty($condiciones) ? '1=1' : implode(' AND ', $condiciones), $params];
    }

    /**
     * Detalle de una consulta con los documentos que devolvio.
     */
    public static function detalle(int $idConsulta): array
    {
        $encabezado = Database::uno(
            'SELECT c.*, u.nombre_completo AS usuario
               FROM consultas c
               INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
              WHERE c.id_consulta = ?',
            [$idConsulta]
        );

        if (!$encabezado) {
            return [];
        }

        $resultados = Database::todos(
            'SELECT cr.*, d.codigo, d.titulo, d.estado, d.nivel_confidencialidad,
                    c.nombre AS categoria
               FROM consulta_resultados cr
               INNER JOIN documentos d ON d.id_documento = cr.id_documento
               INNER JOIN categorias c ON c.id_categoria = d.id_categoria
              WHERE cr.id_consulta = ?
              ORDER BY cr.relevancia DESC',
            [$idConsulta]
        );

        return ['consulta' => $encabezado, 'resultados' => $resultados];
    }

    /**
     * Marca un resultado como abierto (el usuario reviso la fuente).
     */
    public static function marcarAbierto(int $idResultado): void
    {
        Database::actualizar('consulta_resultados',
            ['fue_abierto' => 1], 'id_resultado = ?', [$idResultado]);
    }

    /**
     * Consultas mas frecuentes, para el panel de indicadores.
     */
    public static function masFrecuentes(int $limite = 5): array
    {
        return Database::todos(
            'SELECT texto_consulta, COUNT(*) AS veces
               FROM consultas
              GROUP BY texto_consulta
              ORDER BY veces DESC
              LIMIT ?',
            [$limite]
        );
    }

    /**
     * Consultas que no arrojaron resultados: sirven para detectar
     * vacios de informacion (RF-020, RNF-012).
     */
    public static function sinResultados(int $limite = 10): array
    {
        return Database::todos(
            'SELECT texto_consulta, COUNT(*) AS veces, MAX(fecha_consulta) AS ultima
               FROM consultas
              WHERE resultado_encontrado = 0
              GROUP BY texto_consulta
              ORDER BY veces DESC
              LIMIT ?',
            [$limite]
        );
    }

    /**
     * Promedio de tiempo de respuesta de las consultas.
     */
    public static function tiempoPromedio(): int
    {
        return (int) Database::valor('SELECT COALESCE(AVG(tiempo_ms), 0) FROM consultas');
    }

    /**
     * Consultas agrupadas por dia (ultimos 7 dias).
     */
    public static function porDia(int $dias = 7): array
    {
        return Database::todos(
            'SELECT DATE(fecha_consulta) AS dia, COUNT(*) AS total
               FROM consultas
              WHERE fecha_consulta >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
              GROUP BY DATE(fecha_consulta)
              ORDER BY dia',
            [$dias]
        );
    }

    /**
     * Elimina el historial anterior a una fecha (politica de retencion).
     */
    public static function depurarAnteriorA(string $fecha): int
    {
        return Database::eliminar('consultas', 'fecha_consulta < ?', [$fecha]);
    }

    // =================================================================
    // DESCARGAS
    // =================================================================

    public static function registrarDescarga(int $idDocumento, ?int $idVersion, int $idUsuario): int
    {
        return Database::insertar('historial_descargas', [
            'id_documento' => $idDocumento,
            'id_version'   => $idVersion,
            'id_usuario'   => $idUsuario,
            'direccion_ip' => Helper::ip(),
        ]);
    }

    public static function descargasPorDocumento(int $limite = 10): array
    {
        return Database::todos(
            'SELECT d.codigo, d.titulo, COUNT(hd.id_descarga) AS total_descargas
               FROM documentos d
               LEFT JOIN historial_descargas hd ON hd.id_documento = d.id_documento
              GROUP BY d.id_documento
              HAVING total_descargas > 0
              ORDER BY total_descargas DESC
              LIMIT ?',
            [$limite]
        );
    }
}
