<?php
/**
 * =====================================================================
 * Archivo: app/controllers/AuditoriaController.php
 * Descripcion: Consulta de la bitacora de auditoria y generacion de
 *              copias de seguridad. RF-017, RF-019, RNF-007, RNF-020.
 * =====================================================================
 */

class AuditoriaController
{
    /**
     * Listado de la bitacora con filtros.
     */
    public function index(): void
    {
        Sesion::exigirPermiso('auditoria', 'ver');

        $pagina = max(1, Helper::entero('pagina', 1));

        $filtros = array_filter([
            'usuario'     => Helper::get('usuario'),
            'modulo'      => Helper::get('modulo'),
            'accion'      => Helper::get('accion'),
            'resultado'   => Helper::get('resultado'),
            'fecha_desde' => Helper::get('fecha_desde'),
            'fecha_hasta' => Helper::get('fecha_hasta'),
            'busqueda'    => Helper::get('q'),
        ], fn($valor) => $valor !== '' && $valor !== null);

        $total      = AuditoriaModelo::contar($filtros);
        $paginacion = Helper::paginar($total, $pagina);

        $eventos = AuditoriaModelo::listar($filtros,
            $paginacion['por_pagina'], $paginacion['offset']);

        // Datos para los filtros
        $modulos  = AuditoriaModelo::modulos();
        $acciones = AuditoriaModelo::acciones();

        // Resumenes de apoyo
        $resumenModulo  = AuditoriaModelo::resumenPorModulo(30);
        $eventosSeguridad = AuditoriaModelo::eventosSeguridad(10);
        $masActivos     = AuditoriaModelo::usuariosMasActivos(5);

        $tituloPagina = 'Bitacora de auditoria';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/auditoria/index.php';
    }

    /**
     * Detalle de un evento con los cambios registrados.
     */
    public function detalle(): void
    {
        Sesion::exigirPermiso('auditoria', 'ver');

        $id    = Helper::entero('id');
        $evento = AuditoriaModelo::detalle($id);

        if (!$evento) {
            Sesion::mensajeFlash('error', 'El evento indicado no existe.');
            Helper::redirigir(Helper::url('auditoria'));
        }

        $tituloPagina = 'Detalle del evento';

        require RUTA_VISTAS . '/auditoria/detalle.php';
    }

    /**
     * Exporta la bitacora filtrada a CSV (RF-025).
     */
    public function exportar(): void
    {
        Sesion::exigirPermiso('auditoria', 'ver');

        $filtros = array_filter([
            'usuario'     => Helper::get('usuario'),
            'modulo'      => Helper::get('modulo'),
            'accion'      => Helper::get('accion'),
            'resultado'   => Helper::get('resultado'),
            'fecha_desde' => Helper::get('fecha_desde'),
            'fecha_hasta' => Helper::get('fecha_hasta'),
            'busqueda'    => Helper::get('q'),
        ], fn($valor) => $valor !== '' && $valor !== null);

        $csv = AuditoriaModelo::exportarCsv($filtros);

        Auditoria::registrar('auditoria', 'EXPORTAR',
            'Exportacion de la bitacora de auditoria a CSV.',
            ['entidad' => 'auditoria']);

        $nombre = 'auditoria_' . date('Ymd_His') . '.csv';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        header('Content-Length: ' . strlen($csv));
        header('X-Content-Type-Options: nosniff');

        echo $csv;
        exit;
    }

    /**
     * Depura registros antiguos segun la politica de retencion.
     */
    public function depurar(): void
    {
        Sesion::exigirNivel(3);

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('auditoria'));
        }

        $meses = max(12, Helper::entero('meses', 12, 'post'));
        $fecha = date('Y-m-d H:i:s', strtotime("-{$meses} months"));

        $eliminados = AuditoriaModelo::depurarAnteriorA($fecha);

        Auditoria::registrar('auditoria', 'DEPURAR',
            "Depuracion de {$eliminados} registro(s) de auditoria anteriores a {$fecha}.",
            ['entidad' => 'auditoria', 'resultado' => 'ADVERTENCIA']);

        Sesion::mensajeFlash('exito',
            "Se eliminaron {$eliminados} registro(s) anteriores al " . date('d/m/Y', strtotime($fecha)) . '.');
        Helper::redirigir(Helper::url('auditoria'));
    }

    // =================================================================
    // RESPALDOS
    // =================================================================

    /**
     * Panel de respaldos (RF-019).
     */
    public function respaldos(): void
    {
        Sesion::exigirPermiso('respaldo', 'ver');

        $respaldos = Respaldo::listar(50);
        $enDisco   = Respaldo::archivosEnDisco();

        // Espacio ocupado por todos los respaldos
        $pesoTotal = array_sum(array_column($enDisco, 'peso'));

        $tituloPagina = 'Copias de seguridad';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/auditoria/respaldos.php';
    }

    /**
     * Genera un respaldo nuevo.
     */
    public function generarRespaldo(): void
    {
        Sesion::exigirPermiso('respaldo', 'crear');

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('respaldos'));
        }

        $tipo = Helper::post('tipo', 'COMPLETO');
        if (!in_array($tipo, ['COMPLETO', 'SOLO_DATOS'], true)) {
            $tipo = 'COMPLETO';
        }

        // El respaldo puede tardar: se amplia el limite de ejecucion
        @set_time_limit(300);

        $resultado = Respaldo::generar((int) Sesion::dato('id_usuario'), $tipo);

        if ($resultado['ok']) {
            Auditoria::registrar('respaldo', 'RESPALDO',
                'Respaldo ' . $tipo . ' generado: ' . $resultado['archivo']
                . ' (' . Helper::pesoLegible($resultado['peso']) . ').',
                ['entidad' => 'respaldos', 'id_entidad' => $resultado['id_respaldo']]);

            Sesion::mensajeFlash('exito',
                'El respaldo se genero correctamente: ' . $resultado['archivo']
              . ' (' . Helper::pesoLegible($resultado['peso']) . ').');
        } else {
            Auditoria::registrar('respaldo', 'RESPALDO',
                'Fallo la generacion del respaldo: ' . $resultado['error'],
                ['entidad' => 'respaldos', 'resultado' => 'FALLO']);

            Sesion::mensajeFlash('error',
                'No fue posible generar el respaldo. Detalle: ' . $resultado['error']);
        }

        Helper::redirigir(Helper::url('respaldos'));
    }

    /**
     * Descarga un archivo de respaldo.
     */
    public function descargarRespaldo(): void
    {
        Sesion::exigirPermiso('respaldo', 'ver');

        $id       = Helper::entero('id');
        $respaldo = Respaldo::porId($id);

        if (!$respaldo) {
            Sesion::mensajeFlash('error', 'El respaldo indicado no existe.');
            Helper::redirigir(Helper::url('respaldos'));
        }

        $ruta = Respaldo::rutaAbsoluta($respaldo);

        if (!is_file($ruta)) {
            Sesion::mensajeFlash('error', 'El archivo del respaldo no se encuentra en el servidor.');
            Helper::redirigir(Helper::url('respaldos'));
        }

        Auditoria::registrar('respaldo', 'DESCARGAR',
            'Descarga del respaldo ' . $respaldo['nombre_archivo'] . '.',
            ['entidad' => 'respaldos', 'id_entidad' => $id]);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $respaldo['nombre_archivo'] . '"');
        header('Content-Length: ' . filesize($ruta));
        header('X-Content-Type-Options: nosniff');

        readfile($ruta);
        exit;
    }

    /**
     * Elimina un respaldo del disco y su registro.
     */
    public function eliminarRespaldo(): void
    {
        Sesion::exigirNivel(3);

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('respaldos'));
        }

        $id       = Helper::entero('id', 0, 'post');
        $respaldo = Respaldo::porId($id);

        if (!$respaldo) {
            Sesion::mensajeFlash('error', 'El respaldo indicado no existe.');
            Helper::redirigir(Helper::url('respaldos'));
        }

        $resultado = Respaldo::eliminar($id);

        if ($resultado['ok']) {
            Auditoria::registrar('respaldo', 'ELIMINAR',
                'Eliminacion del respaldo ' . $respaldo['nombre_archivo'] . '.',
                ['entidad' => 'respaldos', 'id_entidad' => $id,
                 'datos_antes' => $respaldo, 'resultado' => 'ADVERTENCIA']);

            Sesion::mensajeFlash('exito', 'El respaldo se elimino correctamente.');
        } else {
            Sesion::mensajeFlash('error', $resultado['error']);
        }

        Helper::redirigir(Helper::url('respaldos'));
    }
}
