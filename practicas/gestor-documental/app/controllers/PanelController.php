<?php
/**
 * =====================================================================
 * Archivo: app/controllers/PanelController.php
 * Descripcion: Panel de indicadores (RF-018, RF-028) y busqueda
 *              documental (RF-009, RF-024).
 * =====================================================================
 */

class PanelController
{
    /**
     * Panel principal con los indicadores del sistema.
     */
    public function index(): void
    {
        Sesion::exigirAutenticacion();

        $usuario = Sesion::usuario();
        $nivel   = Sesion::nivel();

        // Indicadores globales: solo para administracion
        $indicadores = [];
        if ($nivel >= 3) {
            $indicadores = Documento::indicadores();
        }

        // Totales de documentos visibles para este usuario
        $totales = Documento::totales();

        // Conteo de documentos que el usuario puede ver realmente
        $visibles = Documento::contar([], $usuario);

        // Listados de apoyo
        $recientes       = Documento::recientes(5);
        $masConsultados  = Documento::masConsultados(5);
        $porCategoria    = Documento::porCategoria();
        $consultas       = Consulta::masFrecuentes(5);
        $sinResultados   = Consulta::sinResultados(5);

        // Consultas propias del usuario
        $misConsultas = Consulta::historial($usuario, '', 5, 0);

        // Notificaciones sin leer
        $notificaciones = $this->notificacionesNoLeidas((int) $usuario['id_usuario']);

        // Actividad reciente (solo administracion)
        $actividad = [];
        if ($nivel >= 3) {
            $actividad = AuditoriaModelo::listar([], 8, 0);
        }

        $tituloPagina = 'Panel de control';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/panel/index.php';
    }

    /**
     * Buscador documental. Sustituye al antiguo chat con IA: el usuario
     * escribe palabras clave y recibe los documentos y fragmentos que
     * coinciden, con enlace directo a la fuente (RF-009, RF-010, RF-013).
     */
    public function buscar(): void
    {
        Sesion::exigirAutenticacion();

        $usuario    = Sesion::usuario();
        $termino    = Helper::get('q');
        $pagina     = max(1, Helper::entero('pagina', 1));

        // Filtros avanzados (RF-024)
        $filtros = array_filter([
            'busqueda'         => $termino,
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

        $resultados = [];
        $paginacion = Helper::paginar(0, 1);
        $tiempoMs   = 0;

        if ($termino !== '' || count($filtros) > 1) {
            $inicio = microtime(true);

            $total      = Documento::contarBusqueda($termino, $filtros, $usuario);
            $paginacion = Helper::paginar($total, $pagina);

            $resultados = $termino !== ''
                ? array_slice(
                    Documento::buscar($termino, $filtros, $usuario, 200),
                    $paginacion['offset'],
                    $paginacion['por_pagina']
                  )
                : Documento::listar($filtros, $usuario, $paginacion['por_pagina'], $paginacion['offset']);

            $tiempoMs = (int) round((microtime(true) - $inicio) * 1000);

            // La consulta se registra solo si hay un termino escrito
            if ($termino !== '') {
                $idConsulta = Consulta::registrar(
                    (int) $usuario['id_usuario'],
                    $termino,
                    $resultados,
                    array_merge($filtros, ['tipo' => count($filtros) > 1 ? 'AVANZADA' : 'RAPIDA']),
                    $tiempoMs
                );

                Auditoria::registrar('consultas', 'CONSULTAR',
                    'Consulta documental: "' . mb_substr($termino, 0, 100) . '"',
                    ['entidad' => 'consultas', 'id_entidad' => $idConsulta]);

                $_SESSION['ultima_consulta_id'] = $idConsulta;
            }
        }

        // Datos para el formulario de filtros
        $categorias = Catalogo::categorias();
        $areas      = Catalogo::areas();
        $casos      = Catalogo::casos();
        $etiquetas  = Documento::listarEtiquetas();

        $tituloPagina = 'Consulta documental';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/panel/buscar.php';
    }

    /**
     * Muestra el detalle de una consulta anterior con sus resultados.
     */
    public function detalleConsulta(): void
    {
        Sesion::exigirAutenticacion();

        $idConsulta = Helper::entero('id');

        if ($idConsulta <= 0) {
            Sesion::mensajeFlash('error', 'La consulta indicada no es valida.');
            Helper::redirigir(Helper::url('panel', 'buscar'));
        }

        $detalle = Consulta::detalle($idConsulta);

        if (empty($detalle)) {
            Sesion::mensajeFlash('error', 'No se encontro la consulta solicitada.');
            Helper::redirigir(Helper::url('panel', 'buscar'));
        }

        // Un asesor solo puede ver sus propias consultas
        if (Sesion::nivel() < 2
            && (int) $detalle['consulta']['id_usuario'] !== (int) Sesion::dato('id_usuario')) {
            Auditoria::permisoDenegado('Intento de acceso a una consulta de otro usuario.');
            Sesion::mensajeFlash('error', 'No tiene permisos para ver esa consulta.');
            Helper::redirigir(Helper::url('panel', 'buscar'));
        }

        $tituloPagina = 'Detalle de la consulta';

        require RUTA_VISTAS . '/panel/detalle_consulta.php';
    }

    /**
     * Perfil del usuario en sesion.
     */
    public function perfil(): void
    {
        Sesion::exigirAutenticacion();

        $usuario = Usuario::porId((int) Sesion::dato('id_usuario'));

        // Sesiones activas del usuario
        $sesiones = Database::todos(
            'SELECT direccion_ip, fecha_inicio, ultima_actividad
               FROM sesiones_usuario
              WHERE id_usuario = ? AND activa = 1
              ORDER BY ultima_actividad DESC',
            [Sesion::dato('id_usuario')]
        );

        // Actividad propia reciente
        $actividad = Database::todos(
            'SELECT fecha_evento, modulo, accion, descripcion, direccion_ip
               FROM auditoria
              WHERE id_usuario = ?
              ORDER BY fecha_evento DESC
              LIMIT 10',
            [Sesion::dato('id_usuario')]
        );

        $totalConsultas = (int) Database::valor(
            'SELECT COUNT(*) FROM consultas WHERE id_usuario = ?',
            [Sesion::dato('id_usuario')]
        );

        $tituloPagina = 'Mi perfil';

        require RUTA_VISTAS . '/panel/perfil.php';
    }

    /**
     * Marca todas las notificaciones del usuario como leidas.
     */
    public function leerNotificaciones(): void
    {
        Sesion::exigirAutenticacion();

        Database::actualizar('notificaciones',
            ['leida' => 1], 'id_usuario = ?', [Sesion::dato('id_usuario')]);

        Sesion::mensajeFlash('exito', 'Se marcaron todas las notificaciones como leidas.');
        Helper::redirigir(Helper::url('panel'));
    }

    /**
     * Obtiene las notificaciones sin leer de un usuario.
     */
    private function notificacionesNoLeidas(int $idUsuario): array
    {
        return Database::todos(
            'SELECT * FROM notificaciones
              WHERE id_usuario = ? AND leida = 0
              ORDER BY fecha_creacion DESC
              LIMIT 10',
            [$idUsuario]
        );
    }
}
