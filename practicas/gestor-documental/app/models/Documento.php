<?php
/**
 * =====================================================================
 * Archivo: app/models/Documento.php
 * Descripcion: Acceso a datos del registro documental, versiones,
 *              permisos y busqueda.
 * =====================================================================
 */

class Documento
{
    // =================================================================
    // CONSULTA
    // =================================================================

    public static function porId(int $id): ?array
    {
        return Database::uno(
            'SELECT * FROM v_documentos_completo WHERE id_documento = ?',
            [$id]
        );
    }

    /**
     * Listado paginado de documentos visibles para un usuario.
     *
     * Regla de negocio (RF-014, RNF-002): el nivel de acceso y el area
     * del usuario determinan que documentos puede ver.
     */
    public static function listar(array $filtros, array $usuario, int $limite = ITEMS_POR_PAGINA, int $offset = 0): array
    {
        [$where, $params] = self::construirFiltros($filtros, $usuario);

        $sql = "SELECT * FROM v_documentos_completo
                 WHERE {$where}
                 ORDER BY fecha_creacion DESC
                 LIMIT ? OFFSET ?";

        $params[] = $limite;
        $params[] = $offset;

        return Database::todos($sql, $params);
    }

    /**
     * Cuenta los documentos visibles segun los mismos filtros.
     */
    public static function contar(array $filtros, array $usuario): int
    {
        [$where, $params] = self::construirFiltros($filtros, $usuario);

        return (int) Database::valor(
            "SELECT COUNT(*) FROM v_documentos_completo WHERE {$where}",
            $params
        );
    }

    /**
     * Construye la clausula WHERE aplicando el control de acceso.
     *
     * @param string $alias Prefijo de tabla usado cuando la consulta
     *                      combina la vista con otras tablas.
     */
    private static function construirFiltros(array $filtros, array $usuario, string $alias = ''): array
    {
        // Prefijo de columna: "d." o cadena vacia
        $p = $alias !== '' ? $alias . '.' : '';

        $condiciones = [];
        $params      = [];

        // -----------------------------------------------------------------
        // Control de acceso por rol y area
        // -----------------------------------------------------------------
        $nivel   = (int) ($usuario['nivel_acceso'] ?? 1);
        $idArea  = $usuario['id_area'] ?? null;

        if ($nivel < 3) {
            // Nunca se muestran documentos eliminados a usuarios no administradores
            $condiciones[] = "{$p}estado <> 'ELIMINADO'";
        }

        if ($nivel === 2) {
            // Responsable documental: su area, los internos y los que el mismo creo
            $condiciones[] = "({$p}id_area = ? OR {$p}nivel_confidencialidad = ? OR {$p}id_responsable = ?)";
            array_push($params, $idArea, 'INTERNO', $usuario['id_usuario']);
        } elseif ($nivel <= 1) {
            // Asesor: solo su area con nivel publico o interno
            $condiciones[] = "({$p}id_area = ? OR {$p}id_area IS NULL)";
            $params[] = $idArea;
            $condiciones[] = "{$p}nivel_confidencialidad IN ('PUBLICO','INTERNO')";
            $condiciones[] = "{$p}estado = 'PUBLICADO'";
        }

        // -----------------------------------------------------------------
        // Filtros opcionales del formulario
        // -----------------------------------------------------------------
        if (!empty($filtros['busqueda'])) {
            $condiciones[] = "({$p}titulo LIKE ? OR {$p}codigo LIKE ? OR {$p}descripcion LIKE ?
                               OR {$p}palabra_clave LIKE ? OR {$p}responsable LIKE ?)";
            $comodin = '%' . $filtros['busqueda'] . '%';
            array_push($params, $comodin, $comodin, $comodin, $comodin, $comodin);
        }

        if (!empty($filtros['id_categoria'])) {
            $condiciones[] = "{$p}id_categoria = ?";
            $params[] = (int) $filtros['id_categoria'];
        }

        if (!empty($filtros['id_area'])) {
            $condiciones[] = "{$p}id_area = ?";
            $params[] = (int) $filtros['id_area'];
        }

        if (!empty($filtros['id_caso'])) {
            $condiciones[] = "{$p}id_caso = ?";
            $params[] = (int) $filtros['id_caso'];
        }

        if (!empty($filtros['id_responsable'])) {
            $condiciones[] = "{$p}id_responsable = ?";
            $params[] = (int) $filtros['id_responsable'];
        }

        if (!empty($filtros['estado'])) {
            $condiciones[] = "{$p}estado = ?";
            $params[] = $filtros['estado'];
        }

        if (!empty($filtros['confidencialidad'])) {
            $condiciones[] = "{$p}nivel_confidencialidad = ?";
            $params[] = $filtros['confidencialidad'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $condiciones[] = "{$p}fecha_documento >= ?";
            $params[] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $condiciones[] = "{$p}fecha_documento <= ?";
            $params[] = $filtros['fecha_hasta'];
        }

        if (!empty($filtros['etiqueta'])) {
            $condiciones[] = "{$p}id_documento IN (
                                SELECT de.id_documento
                                  FROM documento_etiquetas de
                                  INNER JOIN etiquetas e ON e.id_etiqueta = de.id_etiqueta
                                 WHERE e.nombre = ?)";
            $params[] = $filtros['etiqueta'];
        }

        return [empty($condiciones) ? '1=1' : implode(' AND ', $condiciones), $params];
    }

    /**
     * Busqueda de texto completo sobre titulo, descripcion, palabras
     * clave y contenido extraido de las versiones (RF-009, RF-024).
     * Reemplaza al antiguo motor semantico: usa LIKE y FULLTEXT.
     */
    public static function buscar(string $termino, array $filtros, array $usuario, int $limite = 20): array
    {
        if (trim($termino) === '') {
            return [];
        }

        [$where, $params] = self::construirFiltros($filtros, $usuario, 'd');

        $comodin = '%' . $termino . '%';

        $sql = "SELECT d.*,
                       v.texto_extraido,
                       v.nombre_archivo,
                       v.paginas,
                       (
                           CASE WHEN d.titulo LIKE ?           THEN 100 ELSE 0 END +
                           CASE WHEN d.palabra_clave LIKE ?    THEN 60  ELSE 0 END +
                           CASE WHEN d.descripcion LIKE ?      THEN 30  ELSE 0 END +
                           CASE WHEN d.codigo LIKE ?           THEN 20  ELSE 0 END +
                           CASE WHEN v.texto_extraido LIKE ?   THEN 10  ELSE 0 END
                       ) AS puntaje
                  FROM v_documentos_completo d
                  LEFT JOIN versiones_documento v
                         ON v.id_documento = d.id_documento AND v.es_version_actual = 1
                 WHERE {$where}
                   AND (d.titulo LIKE ?
                        OR d.codigo LIKE ?
                        OR d.descripcion LIKE ?
                        OR d.palabra_clave LIKE ?
                        OR v.texto_extraido LIKE ?)
                 ORDER BY puntaje DESC, d.fecha_documento DESC
                 LIMIT ?";

        $parametros = array_merge(
            [$comodin, $comodin, $comodin, $comodin, $comodin],
            $params,
            [$comodin, $comodin, $comodin, $comodin, $comodin, $limite]
        );

        return Database::todos($sql, $parametros);
    }

    /**
     * Cuenta los resultados de una busqueda (para la paginacion).
     */
    public static function contarBusqueda(string $termino, array $filtros, array $usuario): int
    {
        if (trim($termino) === '') {
            return 0;
        }

        // Los filtros se construyen con el alias "d" porque esta consulta
        // une la vista de documentos con la tabla de versiones.
        [$where, $params] = self::construirFiltros($filtros, $usuario, 'd');
        $comodin = '%' . $termino . '%';

        $sql = "SELECT COUNT(*)
                  FROM v_documentos_completo d
                  LEFT JOIN versiones_documento v
                         ON v.id_documento = d.id_documento AND v.es_version_actual = 1
                 WHERE {$where}
                   AND (d.titulo LIKE ?
                        OR d.codigo LIKE ?
                        OR d.descripcion LIKE ?
                        OR d.palabra_clave LIKE ?
                        OR v.texto_extraido LIKE ?)";

        return (int) Database::valor($sql, array_merge($params,
            [$comodin, $comodin, $comodin, $comodin, $comodin]));
    }

    /**
     * Documentos mas consultados, para el panel de indicadores.
     */
    public static function masConsultados(int $limite = 5): array
    {
        return Database::todos(
            "SELECT codigo, titulo, total_consultas, categoria
               FROM v_documentos_completo
              WHERE estado = 'PUBLICADO'
              ORDER BY total_consultas DESC, fecha_creacion DESC
              LIMIT ?",
            [$limite]
        );
    }

    /**
     * Documentos creados recientemente.
     */
    public static function recientes(int $limite = 5): array
    {
        return Database::todos(
            "SELECT codigo, titulo, categoria, estado, fecha_creacion, responsable
               FROM v_documentos_completo
              ORDER BY fecha_creacion DESC
              LIMIT ?",
            [$limite]
        );
    }

    // =================================================================
    // ESCRITURA
    // =================================================================

    /**
     * Crea el registro maestro del documento.
     */
    public static function crear(array $datos): int
    {
        return Database::insertar('documentos', [
            'codigo'                => $datos['codigo'],
            'titulo'                => $datos['titulo'],
            'descripcion'           => $datos['descripcion'] ?? null,
            'id_categoria'          => (int) $datos['id_categoria'],
            'id_caso'               => !empty($datos['id_caso']) ? (int) $datos['id_caso'] : null,
            'id_area'               => !empty($datos['id_area']) ? (int) $datos['id_area'] : null,
            'id_responsable'        => (int) $datos['id_responsable'],
            'autor'                 => $datos['autor'] ?? null,
            'fecha_documento'       => $datos['fecha_documento'],
            'nivel_confidencialidad'=> $datos['nivel_confidencialidad'] ?? 'INTERNO',
            'palabra_clave'         => $datos['palabra_clave'] ?? null,
            'estado'                => $datos['estado'] ?? 'BORRADOR',
            'version_actual'        => 0,
            'total_versiones'       => 0,
        ]);
    }

    /**
     * Actualiza los metadatos de un documento.
     */
    public static function actualizar(int $id, array $datos): int
    {
        return Database::actualizar('documentos', [
            'titulo'                => $datos['titulo'],
            'descripcion'           => $datos['descripcion'] ?? null,
            'id_categoria'          => (int) $datos['id_categoria'],
            'id_caso'               => !empty($datos['id_caso']) ? (int) $datos['id_caso'] : null,
            'id_area'               => !empty($datos['id_area']) ? (int) $datos['id_area'] : null,
            'autor'                 => $datos['autor'] ?? null,
            'fecha_documento'       => $datos['fecha_documento'],
            'nivel_confidencialidad'=> $datos['nivel_confidencialidad'] ?? 'INTERNO',
            'palabra_clave'         => $datos['palabra_clave'] ?? null,
            'estado'                => $datos['estado'] ?? 'BORRADOR',
        ], 'id_documento = ?', [$id]);
    }

    /**
     * Cambia unicamente el estado del documento.
     */
    public static function cambiarEstado(int $id, string $estado): int
    {
        return Database::actualizar('documentos',
            ['estado' => $estado], 'id_documento = ?', [$id]);
    }

    /**
     * Marca el documento como eliminado logicamente (borrado trazado).
     * Regla de negocio: la eliminacion definitiva esta restringida.
     */
    public static function archivar(int $id): int
    {
        return self::cambiarEstado($id, 'ARCHIVADO');
    }

    /**
     * Eliminacion logica: el registro y sus archivos se conservan.
     */
    public static function eliminarLogico(int $id): int
    {
        return self::cambiarEstado($id, 'ELIMINADO');
    }

    /**
     * Eliminacion definitiva. Solo el administrador y dejando traza.
     */
    public static function eliminarDefinitivo(int $id): bool
    {
        // Primero se borran los archivos fisicos
        $versiones = self::versiones($id);
        foreach ($versiones as $version) {
            GestorArchivos::eliminar($version['ruta_archivo']);
        }

        // Las llaves foraneas con ON DELETE CASCADE limpian el resto
        return Database::eliminar('documentos', 'id_documento = ?', [$id]) > 0;
    }

    // =================================================================
    // VERSIONES
    // =================================================================

    /**
     * Lista las versiones de un documento, la mas reciente primero.
     */
    public static function versiones(int $idDocumento): array
    {
        return Database::todos(
            'SELECT v.*, u.nombre_completo AS usuario_carga
               FROM versiones_documento v
               INNER JOIN usuarios u ON u.id_usuario = v.id_usuario_carga
              WHERE v.id_documento = ?
              ORDER BY v.numero_version DESC',
            [$idDocumento]
        );
    }

    public static function versionPorId(int $idVersion): ?array
    {
        return Database::uno(
            'SELECT v.*, u.nombre_completo AS usuario_carga
               FROM versiones_documento v
               INNER JOIN usuarios u ON u.id_usuario = v.id_usuario_carga
              WHERE v.id_version = ?',
            [$idVersion]
        );
    }

    public static function versionActual(int $idDocumento): ?array
    {
        return Database::uno(
            'SELECT * FROM versiones_documento
              WHERE id_documento = ? AND es_version_actual = 1 LIMIT 1',
            [$idDocumento]
        );
    }

    /**
     * Agrega una nueva version a un documento existente.
     */
    public static function agregarVersion(int $idDocumento, array $archivo, int $idUsuario, ?string $observacion = null): int
    {
        $siguiente = ((int) Database::valor(
            'SELECT COALESCE(MAX(numero_version), 0) FROM versiones_documento WHERE id_documento = ?',
            [$idDocumento]
        )) + 1;

        return Database::insertar('versiones_documento', [
            'id_documento'      => $idDocumento,
            'numero_version'    => $siguiente,
            'nombre_archivo'    => $archivo['nombre_original'],
            'nombre_almacenado' => $archivo['nombre_almacenado'],
            'ruta_archivo'      => $archivo['ruta_archivo'],
            'extension'         => $archivo['extension'],
            'tipo_mime'         => $archivo['tipo_mime'],
            'peso_bytes'        => $archivo['peso_bytes'],
            'hash_archivo'      => $archivo['hash_archivo'],
            'texto_extraido'    => $archivo['texto_extraido'] ?? null,
            'es_version_actual' => 1,
            'estado'            => 'DISPONIBLE',
            'observacion'       => $observacion,
            'id_usuario_carga'  => $idUsuario,
        ]);
    }

    /**
     * Verifica si un archivo ya fue cargado antes (mismo hash SHA-256).
     */
    public static function hashDuplicado(string $hash, int $exceptoDocumento = 0): ?array
    {
        $sql    = 'SELECT v.id_documento, d.codigo, d.titulo, v.numero_version
                     FROM versiones_documento v
                     INNER JOIN documentos d ON d.id_documento = v.id_documento
                    WHERE v.hash_archivo = ?';
        $params = [$hash];

        if ($exceptoDocumento > 0) {
            $sql .= ' AND v.id_documento <> ?';
            $params[] = $exceptoDocumento;
        }

        return Database::uno($sql . ' LIMIT 1', $params);
    }

    /**
     * Comprueba la integridad de una version comparando su hash.
     */
    public static function verificarIntegridad(array $version): bool
    {
        $ruta = RUTA_BASE . DIRECTORY_SEPARATOR
              . str_replace('/', DIRECTORY_SEPARATOR, $version['ruta_archivo']);

        if (!is_file($ruta)) {
            return false;
        }

        return hash_file('sha256', $ruta) === $version['hash_archivo'];
    }

    // =================================================================
    // ETIQUETAS Y PERMISOS
    // =================================================================

    public static function etiquetas(int $idDocumento): array
    {
        return Database::todos(
            'SELECT e.id_etiqueta, e.nombre
               FROM etiquetas e
               INNER JOIN documento_etiquetas de ON de.id_etiqueta = e.id_etiqueta
              WHERE de.id_documento = ?
              ORDER BY e.nombre',
            [$idDocumento]
        );
    }

    public static function listarEtiquetas(): array
    {
        return Database::todos('SELECT * FROM etiquetas ORDER BY nombre');
    }

    /**
     * Reemplaza las etiquetas de un documento.
     */
    public static function guardarEtiquetas(int $idDocumento, array $nombres): void
    {
        Database::eliminar('documento_etiquetas', 'id_documento = ?', [$idDocumento]);

        foreach (array_unique(array_filter(array_map('trim', $nombres))) as $nombre) {
            $idEtiqueta = Database::valor('SELECT id_etiqueta FROM etiquetas WHERE nombre = ?', [$nombre]);

            if (!$idEtiqueta) {
                $idEtiqueta = Database::insertar('etiquetas', ['nombre' => $nombre]);
            }

            Database::insertar('documento_etiquetas', [
                'id_documento' => $idDocumento,
                'id_etiqueta'  => (int) $idEtiqueta,
            ]);
        }
    }

    /**
     * Comprueba si un usuario puede acceder a un documento concreto.
     * Tiene en cuenta el nivel de confidencialidad y los permisos
     * especificos otorgados en permisos_documento.
     */
    public static function puedeAcceder(array $documento, array $usuario): bool
    {
        $nivel = (int) ($usuario['nivel_acceso'] ?? 1);

        // El administrador accede a todo
        if ($nivel >= 3) {
            return true;
        }

        // El responsable accede a lo de su area y a los documentos internos
        if ($nivel === 2) {
            if ((int) $documento['id_area'] === (int) $usuario['id_area']) {
                return true;
            }
            if ($documento['nivel_confidencialidad'] === 'INTERNO') {
                return true;
            }
            if ((int) $documento['id_responsable'] === (int) $usuario['id_usuario']) {
                return true;
            }
        }

        // Documentos reservados o confidenciales: se revisa el permiso explicito
        if (in_array($documento['nivel_confidencialidad'], ['RESERVADO', 'CONFIDENCIAL'], true)) {
            $tienePermiso = (int) Database::valor(
                'SELECT COUNT(*) FROM permisos_documento
                  WHERE id_documento = ?
                    AND puede_consultar = 1
                    AND (id_usuario = ? OR id_area = ?)',
                [$documento['id_documento'], $usuario['id_usuario'], $usuario['id_area']]
            ) > 0;

            if (!$tienePermiso) {
                return false;
            }
        }

        // Solo documentos publicados o el propio borrador
        if ($documento['estado'] !== 'PUBLICADO'
            && (int) $documento['id_responsable'] !== (int) $usuario['id_usuario']) {
            return false;
        }

        if ($nivel <= 1) {
            $mismaArea = (int) $documento['id_area'] === (int) $usuario['id_area']
                      || $documento['id_area'] === null;
            $nivelOk   = in_array($documento['nivel_confidencialidad'], ['PUBLICO', 'INTERNO'], true);

            return $mismaArea && $nivelOk;
        }

        return true;
    }

    // =================================================================
    // INDICADORES
    // =================================================================

    /**
     * Resumen de indicadores para el panel principal.
     */
    public static function indicadores(): array
    {
        return Database::uno('CALL sp_indicadores_panel()') ?? [];
    }

    /**
     * Distribucion de documentos por categoria.
     * Los nombres de las claves coinciden con las de la vista
     * v_estadisticas_documentos para poder usarlas en las vistas.
     */
    public static function porCategoria(): array
    {
        return Database::todos(
            'SELECT id_categoria,
                    categoria,
                    total_documentos,
                    publicados,
                    borradores,
                    archivados,
                    total_consultas
               FROM v_estadisticas_documentos
              ORDER BY total_documentos DESC'
        );
    }

    /**
     * Distribucion de documentos por area.
     */
    public static function porArea(): array
    {
        return Database::todos('SELECT * FROM v_documentos_por_area ORDER BY total_documentos DESC');
    }

    /**
     * Totales generales usados en la cabecera del panel.
     */
    public static function totales(): array
    {
        return [
            'total'      => (int) Database::valor("SELECT COUNT(*) FROM documentos WHERE estado <> 'ELIMINADO'"),
            'publicados' => (int) Database::valor("SELECT COUNT(*) FROM documentos WHERE estado = 'PUBLICADO'"),
            'reservados' => (int) Database::valor("SELECT COUNT(*) FROM documentos WHERE nivel_confidencialidad IN ('RESERVADO','CONFIDENCIAL')"),
            'peso'       => (int) Database::valor('SELECT COALESCE(SUM(peso_bytes),0) FROM versiones_documento'),
            'versiones'  => (int) Database::valor('SELECT COUNT(*) FROM versiones_documento'),
        ];
    }
}
