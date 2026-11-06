<?php
/**
 * =====================================================================
 * Archivo: app/controllers/DocumentoController.php
 * Descripcion: Carga, consulta, edicion, versionado, descarga y
 *              eliminacion de documentos.
 *              RF-004, RF-005, RF-006, RF-016, RF-021, RF-029.
 * =====================================================================
 */

class DocumentoController
{
    /**
     * Listado de documentos con filtros y paginacion.
     */
    public function index(): void
    {
        Sesion::exigirPermiso('documentos', 'ver');

        $usuario = Sesion::usuario();
        $pagina  = max(1, Helper::entero('pagina', 1));

        $filtros = array_filter([
            'busqueda'         => Helper::get('q'),
            'id_categoria'     => Helper::entero('categoria'),
            'id_area'          => Helper::entero('area'),
            'id_caso'          => Helper::entero('caso'),
            'id_responsable'   => Helper::entero('responsable'),
            'estado'           => Helper::get('estado'),
            'confidencialidad' => Helper::get('confidencialidad'),
            'fecha_desde'      => Helper::get('fecha_desde'),
            'fecha_hasta'      => Helper::get('fecha_hasta'),
            'etiqueta'         => Helper::get('etiqueta'),
        ], fn($valor) => $valor !== '' && $valor !== 0 && $valor !== null);

        $total      = Documento::contar($filtros, $usuario);
        $paginacion = Helper::paginar($total, $pagina);

        $documentos = Documento::listar(
            $filtros, $usuario,
            $paginacion['por_pagina'], $paginacion['offset']
        );

        // Datos para el panel de filtros
        $categorias = Catalogo::categorias();
        $areas      = Catalogo::areas();
        $casos      = Catalogo::casos();
        $etiquetas  = Documento::listarEtiquetas();

        $responsables = Database::todos(
            "SELECT DISTINCT id_responsable, responsable
               FROM v_documentos_completo
              WHERE id_responsable IS NOT NULL
              ORDER BY responsable"
        );

        $tituloPagina = 'Gestion documental';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/documentos/index.php';
    }

    /**
     * Formulario de carga de un documento nuevo (RF-004).
     */
    public function crear(): void
    {
        Sesion::exigirPermiso('documentos', 'crear');

        $errores = [];
        $datos   = [];

        if (Helper::esPost()) {
            $datos = $_POST;

            // ---------------------------------------------------------
            // 1. Token CSRF
            // ---------------------------------------------------------
            if (!Sesion::validarCsrf(Helper::post('csrf_token'))) {
                $errores['general'] = 'La sesion del formulario expiro. Intente nuevamente.';
            } else {
                // -----------------------------------------------------
                // 2. Metadatos obligatorios (RF-006)
                // -----------------------------------------------------
                $validador = (new Validador($_POST, [
                    'titulo'          => 'titulo',
                    'id_categoria'    => 'categoria',
                    'fecha_documento' => 'fecha del documento',
                ]))
                    ->requerido('titulo')
                    ->largoMinimo('titulo', 5)
                    ->largoMaximo('titulo', 250)
                    ->largoMaximo('descripcion', 2000)
                    ->requerido('id_categoria')
                    ->enteroPositivo('id_categoria')
                    ->requerido('fecha_documento')
                    ->fecha('fecha_documento')
                    ->fechaNoFutura('fecha_documento')
                    ->enLista('nivel_confidencialidad',
                              ['PUBLICO', 'INTERNO', 'RESERVADO', 'CONFIDENCIAL'])
                    ->enLista('estado',
                              ['BORRADOR', 'EN_PROCESO', 'PUBLICADO']);

                // -----------------------------------------------------
                // 3. Validacion del archivo (RF-005)
                // -----------------------------------------------------
                if (empty($_FILES['archivo']['name'])) {
                    $errores['archivo'] = 'Debe adjuntar el archivo del documento.';
                }

                if (!$validador->pasa()) {
                    $errores = array_merge($validador->errores(), $errores);
                }

                // -----------------------------------------------------
                // 4. Guardado
                // -----------------------------------------------------
                if (empty($errores)) {
                    $subida = GestorArchivos::subir($_FILES['archivo'], 'documentos');

                    if (!$subida['ok']) {
                        $errores['archivo'] = $subida['error'];
                    } else {
                        $archivo = $subida['datos'];

                        // Verificacion de archivo duplicado por hash
                        $duplicado = Documento::hashDuplicado($archivo['hash_archivo'], 0);
                        if ($duplicado) {
                            GestorArchivos::eliminar($archivo['ruta_archivo']);
                            $errores['archivo'] =
                                'Este archivo ya fue cargado en el documento '
                              . $duplicado['codigo'] . ' — ' . $duplicado['titulo'] . '.';
                        } else {
                            Database::iniciarTransaccion();

                            try {
                                // 4a. Extraccion del texto (RF-007)
                                $archivo['texto_extraido'] =
                                    GestorArchivos::extraerTexto(
                                        $archivo['ruta_absoluta'], $archivo['extension']);

                                // 4b. Registro maestro
                                $codigo = Helper::generarCodigo('documentos', 'codigo', 'DOC');

                                $idDocumento = Documento::crear([
                                    'codigo'                 => $codigo,
                                    'titulo'                 => Helper::post('titulo'),
                                    'descripcion'            => Helper::post('descripcion'),
                                    'id_categoria'           => (int) Helper::post('id_categoria'),
                                    'id_caso'                => Helper::entero('id_caso', 0, 'post'),
                                    'id_area'                => Helper::entero('id_area', 0, 'post'),
                                    'id_responsable'         => (int) Sesion::dato('id_usuario'),
                                    'autor'                  => Helper::post('autor'),
                                    'fecha_documento'        => Helper::post('fecha_documento'),
                                    'nivel_confidencialidad' => Helper::post('nivel_confidencialidad', 'INTERNO'),
                                    'palabra_clave'          => Helper::post('palabra_clave'),
                                    'estado'                 => Helper::post('estado', 'BORRADOR'),
                                ]);

                                // 4c. Primera version
                                Documento::agregarVersion(
                                    $idDocumento,
                                    $archivo,
                                    (int) Sesion::dato('id_usuario'),
                                    'Version inicial.'
                                );

                                // 4d. Etiquetas
                                $etiquetas = array_filter(
                                    array_map('trim', explode(',', Helper::post('etiquetas')))
                                );
                                if (!empty($etiquetas)) {
                                    Documento::guardarEtiquetas($idDocumento, $etiquetas);
                                }

                                // 4e. Permisos para documentos reservados
                                $this->guardarPermisosDesdeFormulario($idDocumento);

                                Database::confirmar();

                                // 4f. Auditoria (RF-017)
                                Auditoria::registrar('documentos', 'CREAR',
                                    "Carga del documento {$codigo}: " . Helper::post('titulo'),
                                    ['entidad' => 'documentos', 'id_entidad' => $idDocumento,
                                     'datos_despues' => [
                                         'codigo'  => $codigo,
                                         'archivo' => $archivo['nombre_original'],
                                         'peso'    => $archivo['peso_bytes'],
                                         'hash'    => $archivo['hash_archivo'],
                                     ]]);

                                // 4g. Notificacion al responsable
                                Database::insertar('notificaciones', [
                                    'id_usuario' => (int) Sesion::dato('id_usuario'),
                                    'titulo'     => 'Documento cargado',
                                    'mensaje'    => "El documento {$codigo} se registro y su archivo "
                                                  . 'fue procesado correctamente.',
                                    'tipo'       => 'EXITO',
                                    'enlace'     => Helper::url('documentos', 'ver', ['id' => $idDocumento]),
                                ]);

                                Sesion::mensajeFlash('exito',
                                    "Documento {$codigo} cargado correctamente como version 1.");
                                Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $idDocumento]));
                            } catch (Throwable $e) {
                                Database::revertir();
                                GestorArchivos::eliminar($archivo['ruta_archivo']);
                                $errores['general'] = 'No fue posible registrar el documento. '
                                                    . 'Detalle: ' . $e->getMessage();
                            }
                        }
                    }
                }
            }
        }

        $categorias = Catalogo::categorias();
        $areas      = Catalogo::areas();
        $casos      = Catalogo::casos('', 'ABIERTO');
        $etiquetas  = Documento::listarEtiquetas();

        $tituloPagina = 'Cargar documento';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/documentos/crear.php';
    }

    /**
     * Ficha completa del documento con sus versiones y fuentes.
     */
    public function ver(): void
    {
        Sesion::exigirPermiso('documentos', 'ver');

        $id        = Helper::entero('id');
        $documento = Documento::porId($id);

        if (!$documento) {
            Sesion::mensajeFlash('error', 'El documento solicitado no existe.');
            Helper::redirigir(Helper::url('documentos'));
        }

        // Control de acceso (RF-014)
        if (!Documento::puedeAcceder($documento, Sesion::usuario())) {
            Auditoria::permisoDenegado(
                'Intento de acceso al documento ' . $documento['codigo']
                . ' sin permisos suficientes.');
            Sesion::mensajeFlash('error', 'No tiene permisos para consultar este documento.');
            Helper::redirigir(Helper::url('documentos'));
        }

        $versiones    = Documento::versiones($id);
        $etiquetas    = Documento::etiquetas($id);
        $versionActual= Documento::versionActual($id);

        // Verificacion de integridad del archivo (RNF-009)
        $integridadOk = $versionActual ? Documento::verificarIntegridad($versionActual) : false;

        // Permisos explicitos otorgados
        $permisos = Database::todos(
            'SELECT pd.*, u.nombre_completo AS usuario, a.nombre AS area
               FROM permisos_documento pd
               LEFT JOIN usuarios u ON u.id_usuario = pd.id_usuario
               LEFT JOIN areas a    ON a.id_area     = pd.id_area
              WHERE pd.id_documento = ?',
            [$id]
        );

        // Quien puede descargar
        $puedeDescargar = Sesion::nivel() >= 2
            || (int) Database::valor(
                'SELECT COUNT(*) FROM permisos_documento
                  WHERE id_documento = ? AND puede_descargar = 1
                    AND (id_usuario = ? OR id_area = ?)',
                [$id, Sesion::dato('id_usuario'), Sesion::dato('id_area')]
            ) > 0;

        // Historial de descargas
        $descargas = Database::todos(
            'SELECT hd.*, u.nombre_completo AS usuario
               FROM historial_descargas hd
               INNER JOIN usuarios u ON u.id_usuario = hd.id_usuario
              WHERE hd.id_documento = ?
              ORDER BY hd.fecha_descarga DESC
              LIMIT 10',
            [$id]
        );

        $tituloPagina = $documento['titulo'];
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/documentos/ver.php';
    }

    /**
     * Formulario de edicion de metadatos (RF-016).
     */
    public function editar(): void
    {
        Sesion::exigirPermiso('documentos', 'editar');

        $id        = Helper::entero('id');
        $documento = Documento::porId($id);

        if (!$documento) {
            Sesion::mensajeFlash('error', 'El documento solicitado no existe.');
            Helper::redirigir(Helper::url('documentos'));
        }

        if (!Documento::puedeAcceder($documento, Sesion::usuario())) {
            Auditoria::permisoDenegado('Intento de edicion no autorizada del documento ' . $documento['codigo']);
            Sesion::mensajeFlash('error', 'No tiene permisos para editar este documento.');
            Helper::redirigir(Helper::url('documentos'));
        }

        $errores = [];

        if (Helper::esPost()) {
            if (!Sesion::validarCsrf(Helper::post('csrf_token'))) {
                $errores['general'] = 'La sesion del formulario expiro. Intente nuevamente.';
            } else {
                $validador = (new Validador($_POST, [
                    'titulo'          => 'titulo',
                    'id_categoria'    => 'categoria',
                    'fecha_documento' => 'fecha del documento',
                ]))
                    ->requerido('titulo')
                    ->largoMinimo('titulo', 5)
                    ->largoMaximo('titulo', 250)
                    ->largoMaximo('descripcion', 2000)
                    ->requerido('id_categoria')
                    ->enteroPositivo('id_categoria')
                    ->requerido('fecha_documento')
                    ->fecha('fecha_documento')
                    ->enLista('nivel_confidencialidad',
                              ['PUBLICO', 'INTERNO', 'RESERVADO', 'CONFIDENCIAL'])
                    ->enLista('estado',
                              ['BORRADOR', 'EN_PROCESO', 'PUBLICADO', 'ARCHIVADO']);

                if ($validador->pasa()) {
                    Database::iniciarTransaccion();

                    try {
                        // Estado previo para la auditoria
                        $antes = [
                            'titulo'                 => $documento['titulo'],
                            'estado'                 => $documento['estado'],
                            'nivel_confidencialidad' => $documento['nivel_confidencialidad'],
                            'id_categoria'           => $documento['id_categoria'],
                        ];

                        Documento::actualizar($id, [
                            'titulo'                 => Helper::post('titulo'),
                            'descripcion'            => Helper::post('descripcion'),
                            'id_categoria'           => (int) Helper::post('id_categoria'),
                            'id_caso'                => Helper::entero('id_caso', 0, 'post'),
                            'id_area'                => Helper::entero('id_area', 0, 'post'),
                            'autor'                  => Helper::post('autor'),
                            'fecha_documento'        => Helper::post('fecha_documento'),
                            'nivel_confidencialidad' => Helper::post('nivel_confidencialidad'),
                            'palabra_clave'          => Helper::post('palabra_clave'),
                            'estado'                 => Helper::post('estado'),
                        ]);

                        // Etiquetas
                        $etiquetas = array_filter(
                            array_map('trim', explode(',', Helper::post('etiquetas')))
                        );
                        Documento::guardarEtiquetas($id, $etiquetas);

                        // Permisos de acceso a documentos reservados
                        Database::eliminar('permisos_documento', 'id_documento = ?', [$id]);
                        $this->guardarPermisosDesdeFormulario($id);

                        Database::confirmar();

                        Auditoria::registrar('documentos', 'ACTUALIZAR',
                            'Actualizacion de metadatos del documento ' . $documento['codigo'],
                            ['entidad' => 'documentos', 'id_entidad' => $id,
                             'datos_antes'   => $antes,
                             'datos_despues' => [
                                 'titulo' => Helper::post('titulo'),
                                 'estado' => Helper::post('estado'),
                             ]]);

                        Sesion::mensajeFlash('exito', 'Los metadatos del documento se actualizaron.');
                        Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
                    } catch (Throwable $e) {
                        Database::revertir();
                        $errores['general'] = 'No fue posible actualizar el documento. '
                                            . 'Detalle: ' . $e->getMessage();
                    }
                } else {
                    $errores = $validador->errores();
                }
            }
        }

        $categorias   = Catalogo::categorias();
        $areas        = Catalogo::areas();
        $casos        = Catalogo::casos();
        $etiquetasDoc = array_column(Documento::etiquetas($id), 'nombre');

        $permisosUsuario = Database::todos(
            'SELECT id_usuario FROM permisos_documento
              WHERE id_documento = ? AND id_usuario IS NOT NULL',
            [$id]
        );
        $permisosArea = Database::todos(
            'SELECT id_area FROM permisos_documento
              WHERE id_documento = ? AND id_area IS NOT NULL',
            [$id]
        );

        $tituloPagina = 'Editar documento';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/documentos/editar.php';
    }

    /**
     * Agrega una nueva version a un documento (RF-021).
     */
    public function nuevaVersion(): void
    {
        Sesion::exigirPermiso('documentos', 'editar');

        $id        = Helper::entero('id');
        $documento = Documento::porId($id);

        if (!$documento) {
            Sesion::mensajeFlash('error', 'El documento solicitado no existe.');
            Helper::redirigir(Helper::url('documentos'));
        }

        if (!Helper::esPost()) {
            Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
        }

        if (!Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'La sesion del formulario expiro. Intente nuevamente.');
            Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
        }

        if (empty($_FILES['archivo']['name'])) {
            Sesion::mensajeFlash('error', 'Debe seleccionar el archivo de la nueva version.');
            Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
        }

        $subida = GestorArchivos::subir($_FILES['archivo'], 'documentos');

        if (!$subida['ok']) {
            Sesion::mensajeFlash('error', $subida['error']);
            Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
        }

        $archivo = $subida['datos'];

        // El archivo no puede ser identico a la version vigente
        $actual = Documento::versionActual($id);
        if ($actual && $actual['hash_archivo'] === $archivo['hash_archivo']) {
            GestorArchivos::eliminar($archivo['ruta_archivo']);
            Sesion::mensajeFlash('advertencia',
                'El archivo es identico a la version vigente. No se genero una nueva version.');
            Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
        }

        Database::iniciarTransaccion();

        try {
            $archivo['texto_extraido'] =
                GestorArchivos::extraerTexto($archivo['ruta_absoluta'], $archivo['extension']);

            $idVersion = Documento::agregarVersion(
                $id,
                $archivo,
                (int) Sesion::dato('id_usuario'),
                Helper::post('observacion') ?: null
            );

            // Si el documento estaba archivado o en borrador, vuelve a publicado
            if (in_array($documento['estado'], ['ARCHIVADO', 'BORRADOR'], true)) {
                Documento::cambiarEstado($id, 'PUBLICADO');
            }

            Database::confirmar();

            Auditoria::registrar('documentos', 'ACTUALIZAR',
                'Nueva version cargada para el documento ' . $documento['codigo'],
                ['entidad' => 'documentos', 'id_entidad' => $id,
                 'datos_despues' => ['archivo' => $archivo['nombre_original'],
                                     'hash'    => $archivo['hash_archivo']]]);

            Database::insertar('notificaciones', [
                'id_usuario' => (int) Sesion::dato('id_usuario'),
                'titulo'     => 'Nueva version registrada',
                'mensaje'    => 'Se agrego la version ' . $archivo['nombre_original']
                              . ' al documento ' . $documento['codigo'] . '.',
                'tipo'       => 'EXITO',
                'enlace'     => Helper::url('documentos', 'ver', ['id' => $id]),
            ]);

            Sesion::mensajeFlash('exito', 'La nueva version se registro correctamente.');
        } catch (Throwable $e) {
            Database::revertir();
            GestorArchivos::eliminar($archivo['ruta_archivo']);
            Sesion::mensajeFlash('error',
                'No fue posible registrar la version. Detalle: ' . $e->getMessage());
        }

        Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
    }

    /**
     * Descarga un archivo del documento (version actual o indicada).
     */
    public function descargar(): void
    {
        Sesion::exigirAutenticacion();

        $id        = Helper::entero('id');
        $idVersion = Helper::entero('version');
        $documento = Documento::porId($id);

        if (!$documento) {
            Sesion::mensajeFlash('error', 'El documento solicitado no existe.');
            Helper::redirigir(Helper::url('documentos'));
        }

        if (!Documento::puedeAcceder($documento, Sesion::usuario())) {
            Auditoria::permisoDenegado('Intento de descarga no autorizada del documento '
                . $documento['codigo']);
            Sesion::mensajeFlash('error', 'No tiene permisos para descargar este documento.');
            Helper::redirigir(Helper::url('documentos'));
        }

        // Verificacion del permiso explicito de descarga
        $puedeDescargar = Sesion::nivel() >= 2
            || (int) Database::valor(
                'SELECT COUNT(*) FROM permisos_documento
                  WHERE id_documento = ? AND puede_descargar = 1
                    AND (id_usuario = ? OR id_area = ?)',
                [$id, Sesion::dato('id_usuario'), Sesion::dato('id_area')]
            ) > 0;

        if (!$puedeDescargar) {
            Auditoria::permisoDenegado('Descarga denegada por falta de permiso explicito: '
                . $documento['codigo']);
            Sesion::mensajeFlash('error',
                'Su rol puede consultar este documento pero no descargarlo.');
            Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
        }

        $version = $idVersion > 0
            ? Documento::versionPorId($idVersion)
            : Documento::versionActual($id);

        if (!$version) {
            Sesion::mensajeFlash('error', 'La version solicitada no existe.');
            Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
        }

        $ruta = RUTA_BASE . DIRECTORY_SEPARATOR
              . str_replace('/', DIRECTORY_SEPARATOR, $version['ruta_archivo']);

        if (!is_file($ruta)) {
            Sesion::mensajeFlash('error',
                'El archivo no se encuentra en el servidor. Contacte al administrador.');
            Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
        }

        // Verificacion de integridad antes de entregar (RNF-009)
        if (hash_file('sha256', $ruta) !== $version['hash_archivo']) {
            Auditoria::registrar('documentos', 'DESCARGAR',
                'Integridad comprometida en ' . $documento['codigo']
                . ' version ' . $version['numero_version'],
                ['entidad' => 'documentos', 'id_entidad' => $id, 'resultado' => 'ADVERTENCIA']);

            Sesion::mensajeFlash('error',
                'La verificacion de integridad del archivo fallo. Se cancelo la descarga.');
            Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
        }

        // Registro de la descarga
        Consulta::registrarDescarga($id, (int) $version['id_version'], (int) Sesion::dato('id_usuario'));
        Auditoria::descarga($id, $documento['titulo']);

        // Envio del archivo
        $nombreDescarga = $documento['codigo']
            . '_v' . $version['numero_version'] . '_'
            . Helper::limpiarNombreArchivo($version['nombre_archivo']);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $version['tipo_mime']);
        header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
        header('Content-Length: ' . filesize($ruta));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('X-Content-Type-Options: nosniff');

        readfile($ruta);
        exit;
    }

    /**
     * Archiva o elimina un documento (RF-016).
     */
    public function eliminar(): void
    {
        Sesion::exigirPermiso('documentos', 'eliminar');

        $id        = Helper::entero('id');
        $documento = Documento::porId($id);

        if (!$documento) {
            Sesion::mensajeFlash('error', 'El documento solicitado no existe.');
            Helper::redirigir(Helper::url('documentos'));
        }

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
        }

        $modo = Helper::post('modo', 'archivar');

        if ($modo === 'definitivo') {
            // La eliminacion definitiva exige nivel de administracion
            if (Sesion::nivel() < 3) {
                Auditoria::permisoDenegado('Intento de eliminacion definitiva sin nivel de administracion.');
                Sesion::mensajeFlash('error',
                    'Solo el administrador puede eliminar definitivamente un documento.');
                Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
            }

            // Confirmacion explicita escribiendo el codigo del documento
            if (Helper::post('confirmacion') !== $documento['codigo']) {
                Sesion::mensajeFlash('error',
                    'El codigo de confirmacion no coincide. No se elimino nada.');
                Helper::redirigir(Helper::url('documentos', 'ver', ['id' => $id]));
            }

            $versiones = Documento::versiones($id);

            Documento::eliminarDefinitivo($id);

            Auditoria::registrar('documentos', 'ELIMINAR',
                'Eliminacion definitiva del documento ' . $documento['codigo']
                . ' y sus ' . count($versiones) . ' version(es).',
                ['entidad' => 'documentos', 'id_entidad' => $id,
                 'datos_antes' => ['codigo' => $documento['codigo'],
                                   'titulo' => $documento['titulo'],
                                   'versiones' => count($versiones)]]);

            Sesion::mensajeFlash('exito', 'El documento fue eliminado definitivamente.');
            Helper::redirigir(Helper::url('documentos'));
        }

        // Eliminacion logica: se conserva la trazabilidad
        $estadoAnterior = $documento['estado'];

        if ($modo === 'archivar') {
            Documento::archivar($id);
            $mensaje = 'El documento fue archivado. Sigue disponible para auditoria.';
        } else {
            Documento::eliminarLogico($id);
            $mensaje = 'El documento fue marcado como eliminado. Los archivos se conservan.';
        }

        Auditoria::registrar('documentos',
            $modo === 'archivar' ? 'ARCHIVAR' : 'ELIMINAR',
            'Documento ' . $documento['codigo'] . ' pasado de ' . $estadoAnterior
            . ' a ' . strtoupper($modo === 'archivar' ? 'ARCHIVADO' : 'ELIMINADO') . '.',
            ['entidad' => 'documentos', 'id_entidad' => $id,
             'datos_antes'   => ['estado' => $estadoAnterior],
             'datos_despues' => ['estado' => $modo === 'archivar' ? 'ARCHIVADO' : 'ELIMINADO']]);

        Sesion::mensajeFlash('exito', $mensaje);
        Helper::redirigir(Helper::url('documentos'));
    }

    /**
     * Guarda los permisos explicitos enviados desde el formulario
     * para los documentos con nivel reservado o confidencial.
     */
    private function guardarPermisosDesdeFormulario(int $idDocumento): void
    {
        $nivel = Helper::post('nivel_confidencialidad', 'INTERNO');

        if (!in_array($nivel, ['RESERVADO', 'CONFIDENCIAL'], true)) {
            return;
        }

        $puedeDescargar = Helper::post('permiso_descargar') === '1' ? 1 : 0;

        // Permisos por area
        foreach ((array) ($_POST['areas_permitidas'] ?? []) as $idArea) {
            if ((int) $idArea > 0) {
                Database::insertar('permisos_documento', [
                    'id_documento'    => $idDocumento,
                    'id_area'         => (int) $idArea,
                    'puede_consultar' => 1,
                    'puede_descargar' => $puedeDescargar,
                ]);
            }
        }

        // Permisos por usuario
        foreach ((array) ($_POST['usuarios_permitidos'] ?? []) as $idUsuario) {
            if ((int) $idUsuario > 0) {
                Database::insertar('permisos_documento', [
                    'id_documento'    => $idDocumento,
                    'id_usuario'      => (int) $idUsuario,
                    'puede_consultar' => 1,
                    'puede_descargar' => $puedeDescargar,
                ]);
            }
        }

        // El responsable siempre conserva el acceso
        Database::insertar('permisos_documento', [
            'id_documento'    => $idDocumento,
            'id_usuario'      => (int) Sesion::dato('id_usuario'),
            'puede_consultar' => 1,
            'puede_descargar' => 1,
        ]);
    }
}
