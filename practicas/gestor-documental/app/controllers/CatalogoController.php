<?php
/**
 * =====================================================================
 * Archivo: app/controllers/CatalogoController.php
 * Descripcion: CRUD de categorias, casos y areas (RF-016).
 * =====================================================================
 */

class CatalogoController
{
    // =================================================================
    // CATEGORIAS
    // =================================================================

    public function categorias(): void
    {
        Sesion::exigirPermiso('categorias', 'ver');

        $busqueda  = Helper::get('q');
        $categorias = Catalogo::categoriasConTotales();

        // Filtro en memoria para no complicar la consulta agregada
        if ($busqueda !== '') {
            $categorias = array_values(array_filter($categorias,
                fn($c) => mb_stripos($c['nombre'], $busqueda) !== false
                       || mb_stripos((string) $c['descripcion'], $busqueda) !== false));
        }

        $tituloPagina = 'Categorias documentales';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/categorias/index.php';
    }

    public function guardarCategoria(): void
    {
        Sesion::exigirPermiso('categorias', Helper::entero('id') > 0 ? 'editar' : 'crear');

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('categorias'));
        }

        $id = Helper::entero('id', 0, 'post');

        $validador = (new Validador($_POST, ['nombre' => 'nombre de la categoria']))
            ->requerido('nombre')
            ->largoMinimo('nombre', 3)
            ->largoMaximo('nombre', 100)
            ->largoMaximo('descripcion', 250);

        if (Catalogo::existeCategoria(Helper::post('nombre'), $id)) {
            Sesion::mensajeFlash('error', 'Ya existe una categoria con ese nombre.');
            Helper::redirigir(Helper::url('categorias'));
        }

        if (!$validador->pasa()) {
            Sesion::mensajeFlash('error', $validador->primerError());
            Helper::redirigir(Helper::url('categorias'));
        }

        $datos = [
            'nombre'      => Helper::post('nombre'),
            'descripcion' => Helper::post('descripcion'),
            'activo'      => Helper::post('activo') === '1' ? 1 : 0,
        ];

        if ($id > 0) {
            $antes = Catalogo::categoriaPorId($id);

            Catalogo::actualizarCategoria($id, $datos);

            Auditoria::registrar('categorias', 'ACTUALIZAR',
                'Actualizacion de la categoria "' . $datos['nombre'] . '".',
                ['entidad' => 'categorias', 'id_entidad' => $id,
                 'datos_antes'   => $antes,
                 'datos_despues' => $datos]);

            Sesion::mensajeFlash('exito', 'La categoria se actualizo correctamente.');
        } else {
            $nuevoId = Catalogo::crearCategoria($datos);

            Auditoria::registrar('categorias', 'CREAR',
                'Creacion de la categoria "' . $datos['nombre'] . '".',
                ['entidad' => 'categorias', 'id_entidad' => $nuevoId,
                 'datos_despues' => $datos]);

            Sesion::mensajeFlash('exito', 'La categoria se creo correctamente.');
        }

        Helper::redirigir(Helper::url('categorias'));
    }

    public function eliminarCategoria(): void
    {
        Sesion::exigirPermiso('categorias', 'eliminar');

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('categorias'));
        }

        $id      = Helper::entero('id', 0, 'post');
        $categoria = Catalogo::categoriaPorId($id);

        if (!$categoria) {
            Sesion::mensajeFlash('error', 'La categoria indicada no existe.');
            Helper::redirigir(Helper::url('categorias'));
        }

        $resultado = Catalogo::desactivarCategoria($id);

        Auditoria::registrar('categorias', $resultado['desactivada'] ? 'ACTUALIZAR' : 'ELIMINAR',
            'Categoria "' . $categoria['nombre'] . '": ' . $resultado['mensaje'],
            ['entidad' => 'categorias', 'id_entidad' => $id,
             'datos_antes' => $categoria]);

        Sesion::mensajeFlash('exito', $resultado['mensaje']);
        Helper::redirigir(Helper::url('categorias'));
    }

    // =================================================================
    // CASOS
    // =================================================================

    public function casos(): void
    {
        Sesion::exigirPermiso('casos', 'ver');

        $busqueda = Helper::get('q');
        $estado   = Helper::get('estado');
        $pagina   = max(1, Helper::entero('pagina', 1));

        $total      = Catalogo::contarCasos($busqueda, $estado);
        $paginacion = Helper::paginar($total, $pagina);

        $casos = Catalogo::casos($busqueda, $estado,
            $paginacion['por_pagina'], $paginacion['offset']);

        $areas = Catalogo::areas();

        $tituloPagina = 'Casos y expedientes';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/categorias/casos.php';
    }

    public function guardarCaso(): void
    {
        Sesion::exigirPermiso('casos', Helper::entero('id') > 0 ? 'editar' : 'crear');

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('casos'));
        }

        $id = Helper::entero('id', 0, 'post');

        $validador = (new Validador($_POST, [
            'titulo'         => 'titulo del caso',
            'fecha_apertura' => 'fecha de apertura',
        ]))
            ->requerido('titulo')
            ->largoMinimo('titulo', 5)
            ->largoMaximo('titulo', 200)
            ->requerido('fecha_apertura')
            ->fecha('fecha_apertura')
            ->enLista('estado', ['ABIERTO', 'EN_PROCESO', 'CERRADO', 'ARCHIVADO'])
            ->personalizado('fecha_apertura',
                strtotime(Helper::post('fecha_apertura')) <= strtotime(date('Y-m-d')),
                'La fecha de apertura no puede ser posterior a hoy.');

        if (!$validador->pasa()) {
            Sesion::mensajeFlash('error', $validador->primerError());
            Helper::redirigir(Helper::url('casos'));
        }

        // El codigo se genera automaticamente si no se envia
        $codigo = Helper::post('codigo');
        if ($codigo === '' || $id > 0) {
            $codigo = $id > 0
                ? (string) Database::valor('SELECT codigo FROM casos WHERE id_caso = ?', [$id])
                : Helper::generarCodigo('casos', 'codigo', 'CASO');
        }

        if (Catalogo::existeCaso($codigo, $id)) {
            Sesion::mensajeFlash('error', 'Ya existe un caso con ese codigo.');
            Helper::redirigir(Helper::url('casos'));
        }

        $datos = [
            'codigo'         => $codigo,
            'titulo'         => Helper::post('titulo'),
            'descripcion'    => Helper::post('descripcion'),
            'id_area'        => Helper::entero('id_area', 0, 'post'),
            'estado'         => Helper::post('estado', 'ABIERTO'),
            'fecha_apertura' => Helper::post('fecha_apertura'),
            'fecha_cierre'   => Helper::post('fecha_cierre'),
        ];

        if ($id > 0) {
            $antes = Catalogo::casoPorId($id);
            Catalogo::actualizarCaso($id, $datos);

            Auditoria::registrar('casos', 'ACTUALIZAR',
                'Actualizacion del caso ' . $codigo . '.',
                ['entidad' => 'casos', 'id_entidad' => $id,
                 'datos_antes' => $antes, 'datos_despues' => $datos]);

            Sesion::mensajeFlash('exito', 'El caso se actualizo correctamente.');
        } else {
            $nuevoId = Catalogo::crearCaso($datos);

            Auditoria::registrar('casos', 'CREAR',
                'Creacion del caso ' . $codigo . '.',
                ['entidad' => 'casos', 'id_entidad' => $nuevoId, 'datos_despues' => $datos]);

            Sesion::mensajeFlash('exito', 'El caso se creo correctamente.');
        }

        Helper::redirigir(Helper::url('casos'));
    }

    public function eliminarCaso(): void
    {
        Sesion::exigirPermiso('casos', 'eliminar');

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('casos'));
        }

        $id   = Helper::entero('id', 0, 'post');
        $caso = Catalogo::casoPorId($id);

        if (!$caso) {
            Sesion::mensajeFlash('error', 'El caso indicado no existe.');
            Helper::redirigir(Helper::url('casos'));
        }

        $resultado = Catalogo::desactivarCaso($id);

        Auditoria::registrar('casos', 'ELIMINAR',
            'Caso ' . $caso['codigo'] . ': ' . $resultado['mensaje'],
            ['entidad' => 'casos', 'id_entidad' => $id, 'datos_antes' => $caso]);

        Sesion::mensajeFlash('exito', $resultado['mensaje']);
        Helper::redirigir(Helper::url('casos'));
    }

    // =================================================================
    // AREAS
    // =================================================================

    public function areas(): void
    {
        Sesion::exigirNivel(3);

        $areas = Database::todos(
            'SELECT a.*,
                    (SELECT COUNT(*) FROM usuarios u WHERE u.id_area = a.id_area) AS total_usuarios,
                    (SELECT COUNT(*) FROM documentos d WHERE d.id_area = a.id_area
                      AND d.estado <> "ELIMINADO") AS total_documentos
               FROM areas a
              ORDER BY a.nombre'
        );

        $tituloPagina = 'Areas de la organizacion';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/usuarios/areas.php';
    }

    public function guardarArea(): void
    {
        Sesion::exigirNivel(3);

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('areas'));
        }

        $id = Helper::entero('id', 0, 'post');

        $validador = (new Validador($_POST, ['nombre' => 'nombre del area']))
            ->requerido('nombre')
            ->largoMinimo('nombre', 3)
            ->largoMaximo('nombre', 100);

        if (Catalogo::existeArea(Helper::post('nombre'), $id)) {
            Sesion::mensajeFlash('error', 'Ya existe un area con ese nombre.');
            Helper::redirigir(Helper::url('areas'));
        }

        if (!$validador->pasa()) {
            Sesion::mensajeFlash('error', $validador->primerError());
            Helper::redirigir(Helper::url('areas'));
        }

        $datos = [
            'nombre'      => Helper::post('nombre'),
            'descripcion' => Helper::post('descripcion'),
            'activo'      => Helper::post('activo') === '1' ? 1 : 0,
        ];

        if ($id > 0) {
            $antes = Catalogo::areaPorId($id);
            Catalogo::actualizarArea($id, $datos);

            Auditoria::registrar('areas', 'ACTUALIZAR',
                'Actualizacion del area "' . $datos['nombre'] . '".',
                ['entidad' => 'areas', 'id_entidad' => $id,
                 'datos_antes' => $antes, 'datos_despues' => $datos]);

            Sesion::mensajeFlash('exito', 'El area se actualizo correctamente.');
        } else {
            $nuevoId = Catalogo::crearArea($datos);

            Auditoria::registrar('areas', 'CREAR',
                'Creacion del area "' . $datos['nombre'] . '".',
                ['entidad' => 'areas', 'id_entidad' => $nuevoId, 'datos_despues' => $datos]);

            Sesion::mensajeFlash('exito', 'El area se creo correctamente.');
        }

        Helper::redirigir(Helper::url('areas'));
    }

    // =================================================================
    // CONFIGURACION
    // =================================================================

    public function configuracion(): void
    {
        Sesion::exigirNivel(3);

        $parametros = Catalogo::configuracionDetallada();

        $tituloPagina = 'Configuracion del sistema';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/usuarios/configuracion.php';
    }

    public function guardarConfiguracion(): void
    {
        Sesion::exigirNivel(3);

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('configuracion'));
        }

        $antes   = Catalogo::configuracion();
        $valores = $_POST['config'] ?? [];

        if (!is_array($valores) || empty($valores)) {
            Sesion::mensajeFlash('advertencia', 'No se recibio ningun cambio.');
            Helper::redirigir(Helper::url('configuracion'));
        }

        // Validaciones puntuales sobre valores criticos
        if (isset($valores['peso_maximo_archivo_mb'])) {
            $peso = (int) $valores['peso_maximo_archivo_mb'];
            if ($peso < 1 || $peso > 100) {
                Sesion::mensajeFlash('error',
                    'El peso maximo por archivo debe estar entre 1 y 100 MB.');
                Helper::redirigir(Helper::url('configuracion'));
            }
        }

        if (isset($valores['max_intentos_login'])) {
            $intentos = (int) $valores['max_intentos_login'];
            if ($intentos < 3 || $intentos > 10) {
                Sesion::mensajeFlash('error',
                    'El numero de intentos debe estar entre 3 y 10.');
                Helper::redirigir(Helper::url('configuracion'));
            }
        }

        Catalogo::guardarConfiguracion($valores);

        Auditoria::registrar('configuracion', 'ACTUALIZAR',
            'Actualizacion de parametros del sistema (' . count($valores) . ' valores).',
            ['entidad' => 'configuracion',
             'datos_antes' => $antes, 'datos_despues' => $valores]);

        Sesion::mensajeFlash('exito', 'La configuracion se guardo correctamente.');
        Helper::redirigir(Helper::url('configuracion'));
    }
}
