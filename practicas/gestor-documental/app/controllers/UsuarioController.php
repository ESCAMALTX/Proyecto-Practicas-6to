<?php
/**
 * =====================================================================
 * Archivo: app/controllers/UsuarioController.php
 * Descripcion: Gestion de usuarios, roles y permisos.
 *              RF-002, RF-003.
 * =====================================================================
 */

class UsuarioController
{
    /**
     * Listado de usuarios con filtros y paginacion.
     */
    public function index(): void
    {
        Sesion::exigirPermiso('usuarios', 'ver');

        $busqueda = Helper::get('q');
        $idRol    = Helper::entero('rol');
        $estado   = Helper::get('estado');
        $pagina   = max(1, Helper::entero('pagina', 1));

        $total      = Usuario::contar($busqueda, $idRol, $estado);
        $paginacion = Helper::paginar($total, $pagina);

        $usuarios = Usuario::listar($busqueda, $idRol, $estado,
            $paginacion['por_pagina'], $paginacion['offset']);

        $roles = Usuario::listarRoles();

        // Conteo de usuarios por rol para la cabecera
        $porRol = Database::todos(
            'SELECT r.nombre, COUNT(u.id_usuario) AS total
               FROM roles r
               LEFT JOIN usuarios u ON u.id_rol = r.id_rol
              GROUP BY r.id_rol, r.nombre
              ORDER BY r.nivel_acceso DESC'
        );

        $tituloPagina = 'Gestion de usuarios';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/usuarios/index.php';
    }

    /**
     * Crea un usuario nuevo (RF-002).
     */
    public function crear(): void
    {
        Sesion::exigirPermiso('usuarios', 'crear');

        $errores = [];
        $datos   = [];

        if (Helper::esPost()) {
            $datos = $_POST;

            if (!Sesion::validarCsrf(Helper::post('csrf_token'))) {
                $errores['general'] = 'La sesion del formulario expiro. Intente nuevamente.';
            } else {
                $validador = (new Validador($_POST, [
                    'nombre_completo' => 'nombre completo',
                    'documento'       => 'documento',
                    'correo'          => 'correo electronico',
                    'usuario'         => 'nombre de usuario',
                    'id_rol'          => 'rol',
                    'clave'           => 'contrasena',
                    'clave2'          => 'confirmacion',
                ]))
                    ->requerido('nombre_completo')
                    ->largoMinimo('nombre_completo', 5)
                    ->largoMaximo('nombre_completo', 150)
                    ->requerido('documento')
                    ->soloAlfanumerico('documento')
                    ->largoMinimo('documento', 5)
                    ->requerido('correo')
                    ->correo('correo')
                    ->requerido('usuario')
                    ->soloAlfanumerico('usuario')
                    ->largoMinimo('usuario', 4)
                    ->largoMaximo('usuario', 50)
                    ->requerido('id_rol')
                    ->enteroPositivo('id_rol')
                    ->requerido('clave')
                    ->claveFuerte('clave')
                    ->requerido('clave2')
                    ->coinciden('clave', 'clave2')
                    ->enLista('estado', ['ACTIVO', 'INACTIVO']);

                if (Usuario::existe('usuario', Helper::post('usuario'))) {
                    $errores['usuario'] = 'Ese nombre de usuario ya esta registrado.';
                }
                if (Usuario::existe('correo', Helper::post('correo'))) {
                    $errores['correo'] = 'Ese correo ya esta registrado.';
                }
                if (Usuario::existe('documento', Helper::post('documento'))) {
                    $errores['documento'] = 'Ese documento ya esta registrado.';
                }

                if ($validador->pasa() && empty($errores)) {
                    $idUsuario = Usuario::crear([
                        'nombre_completo' => Helper::post('nombre_completo'),
                        'documento'       => Helper::post('documento'),
                        'correo'          => Helper::post('correo'),
                        'usuario'         => Helper::post('usuario'),
                        'clave'           => $_POST['clave'],
                        'id_rol'          => (int) Helper::post('id_rol'),
                        'id_area'         => Helper::entero('id_area', 0, 'post'),
                        'telefono'        => Helper::post('telefono'),
                        'cargo'           => Helper::post('cargo'),
                        'estado'          => Helper::post('estado', 'ACTIVO'),
                    ]);

                    Auditoria::registrar('usuarios', 'CREAR',
                        'Creacion del usuario "' . Helper::post('usuario') . '".',
                        ['entidad' => 'usuarios', 'id_entidad' => $idUsuario,
                         'datos_despues' => [
                             'usuario'   => Helper::post('usuario'),
                             'correo'    => Helper::post('correo'),
                             'id_rol'    => Helper::post('id_rol'),
                             'id_area'   => Helper::post('id_area'),
                             'estado'    => Helper::post('estado'),
                         ]]);

                    Sesion::mensajeFlash('exito',
                        'El usuario se creo correctamente. Debera cambiar la contrasena '
                      . 'en su primer ingreso.');
                    Helper::redirigir(Helper::url('usuarios'));
                } else {
                    $errores = array_merge($validador->errores(), $errores);
                }
            }
        }

        $roles = Usuario::listarRoles();
        $areas = Usuario::listarAreas();

        $tituloPagina = 'Crear usuario';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/usuarios/crear.php';
    }

    /**
     * Edita los datos de un usuario.
     */
    public function editar(): void
    {
        Sesion::exigirPermiso('usuarios', 'editar');

        $id      = Helper::entero('id');
        $usuario = Usuario::porId($id);

        if (!$usuario) {
            Sesion::mensajeFlash('error', 'El usuario solicitado no existe.');
            Helper::redirigir(Helper::url('usuarios'));
        }

        $errores = [];

        if (Helper::esPost()) {
            if (!Sesion::validarCsrf(Helper::post('csrf_token'))) {
                $errores['general'] = 'La sesion del formulario expiro. Intente nuevamente.';
            } else {
                $validador = (new Validador($_POST, [
                    'nombre_completo' => 'nombre completo',
                    'documento'       => 'documento',
                    'correo'          => 'correo electronico',
                    'usuario'         => 'nombre de usuario',
                    'id_rol'          => 'rol',
                ]))
                    ->requerido('nombre_completo')
                    ->largoMinimo('nombre_completo', 5)
                    ->largoMaximo('nombre_completo', 150)
                    ->requerido('documento')
                    ->soloAlfanumerico('documento')
                    ->requerido('correo')
                    ->correo('correo')
                    ->requerido('usuario')
                    ->soloAlfanumerico('usuario')
                    ->largoMinimo('usuario', 4)
                    ->requerido('id_rol')
                    ->enteroPositivo('id_rol')
                    ->enLista('estado', ['ACTIVO', 'INACTIVO', 'BLOQUEADO']);

                if (Usuario::existe('usuario', Helper::post('usuario'), $id)) {
                    $errores['usuario'] = 'Ese nombre de usuario ya esta en uso.';
                }
                if (Usuario::existe('correo', Helper::post('correo'), $id)) {
                    $errores['correo'] = 'Ese correo ya esta en uso.';
                }
                if (Usuario::existe('documento', Helper::post('documento'), $id)) {
                    $errores['documento'] = 'Ese documento ya esta en uso.';
                }

                // Proteccion: el administrador no puede quitarse su propio rol
                if ($id === (int) Sesion::dato('id_usuario')
                    && (int) Helper::post('id_rol') !== (int) $usuario['id_rol']) {
                    $errores['id_rol'] = 'No puede cambiar su propio rol de acceso.';
                }

                if ($validador->pasa() && empty($errores)) {
                    $datos = [
                        'nombre_completo' => Helper::post('nombre_completo'),
                        'documento'       => Helper::post('documento'),
                        'correo'          => Helper::post('correo'),
                        'usuario'         => Helper::post('usuario'),
                        'id_rol'          => (int) Helper::post('id_rol'),
                        'id_area'         => Helper::entero('id_area', 0, 'post'),
                        'telefono'        => Helper::post('telefono'),
                        'cargo'           => Helper::post('cargo'),
                        'estado'          => Helper::post('estado'),
                    ];

                    Usuario::actualizar($id, $datos);

                    Auditoria::registrar('usuarios', 'ACTUALIZAR',
                        'Actualizacion del usuario "' . $usuario['usuario'] . '".',
                        ['entidad' => 'usuarios', 'id_entidad' => $id,
                         'datos_antes' => [
                             'nombre_completo' => $usuario['nombre_completo'],
                             'correo'          => $usuario['correo'],
                             'rol'             => $usuario['rol'],
                             'estado'          => $usuario['estado'],
                         ],
                         'datos_despues' => $datos]);

                    Sesion::mensajeFlash('exito', 'El usuario se actualizo correctamente.');
                    Helper::redirigir(Helper::url('usuarios'));
                } else {
                    $errores = array_merge($validador->errores(), $errores);
                }
            }
        }

        $roles = Usuario::listarRoles();
        $areas = Usuario::listarAreas();

        $tituloPagina = 'Editar usuario';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/usuarios/editar.php';
    }

    /**
     * Activa, desactiva o bloquea un usuario (RF-002).
     */
    public function cambiarEstado(): void
    {
        Sesion::exigirPermiso('usuarios', 'editar');

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('usuarios'));
        }

        $id     = Helper::entero('id', 0, 'post');
        $estado = strtoupper(Helper::post('estado', 'ACTIVO'));
        $usuario = Usuario::porId($id);

        if (!$usuario) {
            Sesion::mensajeFlash('error', 'El usuario indicado no existe.');
            Helper::redirigir(Helper::url('usuarios'));
        }

        if (!in_array($estado, ['ACTIVO', 'INACTIVO', 'BLOQUEADO'], true)) {
            Sesion::mensajeFlash('error', 'El estado indicado no es valido.');
            Helper::redirigir(Helper::url('usuarios'));
        }

        // Protecciones sobre la propia cuenta y el ultimo administrador
        if ($id === (int) Sesion::dato('id_usuario') && $estado !== 'ACTIVO') {
            Sesion::mensajeFlash('error', 'No puede desactivar su propia cuenta.');
            Helper::redirigir(Helper::url('usuarios'));
        }

        if ($usuario['rol'] === 'Administrador' && $estado !== 'ACTIVO') {
            $otrosAdmins = (int) Database::valor(
                "SELECT COUNT(*) FROM usuarios u
                  INNER JOIN roles r ON r.id_rol = u.id_rol
                 WHERE r.nombre = 'Administrador' AND u.estado = 'ACTIVO' AND u.id_usuario <> ?",
                [$id]
            );

            if ($otrosAdmins === 0) {
                Sesion::mensajeFlash('error',
                    'No puede desactivar al ultimo administrador activo del sistema.');
                Helper::redirigir(Helper::url('usuarios'));
            }
        }

        Usuario::cambiarEstado($id, $estado);

        // Al desactivar se cierran las sesiones abiertas del usuario
        if ($estado !== 'ACTIVO') {
            Database::actualizar('sesiones_usuario',
                ['activa' => 0, 'fecha_cierre' => date('Y-m-d H:i:s')],
                'id_usuario = ? AND activa = 1', [$id]);
        }

        Auditoria::registrar('usuarios', 'ACTUALIZAR',
            'Cambio de estado del usuario "' . $usuario['usuario'] . '" a ' . $estado . '.',
            ['entidad' => 'usuarios', 'id_entidad' => $id,
             'datos_antes'   => ['estado' => $usuario['estado']],
             'datos_despues' => ['estado' => $estado]]);

        Sesion::mensajeFlash('exito', 'El estado del usuario se actualizo a ' . $estado . '.');
        Helper::redirigir(Helper::url('usuarios'));
    }

    /**
     * Restablece la contrasena de un usuario.
     */
    public function restablecerClave(): void
    {
        Sesion::exigirPermiso('usuarios', 'editar');

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('usuarios'));
        }

        $id      = Helper::entero('id', 0, 'post');
        $usuario = Usuario::porId($id);

        if (!$usuario) {
            Sesion::mensajeFlash('error', 'El usuario indicado no existe.');
            Helper::redirigir(Helper::url('usuarios'));
        }

        $nuevaClave = Helper::post('nueva_clave');

        if (!Helper::claveFuerte($nuevaClave)) {
            Sesion::mensajeFlash('error',
                'La contrasena debe tener minimo 8 caracteres, una mayuscula, '
              . 'una minuscula, un numero y un simbolo.');
            Helper::redirigir(Helper::url('usuarios'));
        }

        Usuario::restablecerClave($id, $nuevaClave);

        Auditoria::registrar('usuarios', 'ACTUALIZAR',
            'Restablecimiento de contrasena del usuario "' . $usuario['usuario'] . '".',
            ['entidad' => 'usuarios', 'id_entidad' => $id]);

        Sesion::mensajeFlash('exito',
            'La contrasena se restablecio. El usuario debera cambiarla en su proximo ingreso.');
        Helper::redirigir(Helper::url('usuarios'));
    }

    /**
     * Matriz de roles y permisos (RF-003).
     */
    public function roles(): void
    {
        Sesion::exigirNivel(3);

        $roles   = Usuario::listarRoles();
        $detalle = [];

        foreach ($roles as $rol) {
            $detalle[$rol['id_rol']] = Usuario::permisosDeRol((int) $rol['id_rol']);
        }

        // Modulos registrados en la matriz de permisos
        $modulos = Database::todos('SELECT DISTINCT modulo FROM permisos_rol ORDER BY modulo');

        // Usuarios asignados a cada rol
        $usuariosPorRol = [];
        foreach ($roles as $rol) {
            $usuariosPorRol[$rol['id_rol']] = (int) Database::valor(
                'SELECT COUNT(*) FROM usuarios WHERE id_rol = ?', [$rol['id_rol']]
            );
        }

        $tituloPagina = 'Roles y permisos';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/usuarios/roles.php';
    }

    /**
     * Guarda la matriz de permisos de un rol.
     */
    public function guardarPermisos(): void
    {
        Sesion::exigirNivel(3);

        if (!Helper::esPost() || !Sesion::validarCsrf(Helper::post('csrf_token'))) {
            Sesion::mensajeFlash('error', 'Solicitud no valida.');
            Helper::redirigir(Helper::url('roles'));
        }

        $idRol = Helper::entero('id_rol', 0, 'post');
        $rol   = Database::uno('SELECT * FROM roles WHERE id_rol = ?', [$idRol]);

        if (!$rol) {
            Sesion::mensajeFlash('error', 'El rol indicado no existe.');
            Helper::redirigir(Helper::url('roles'));
        }

        $antes    = Usuario::permisosDeRol($idRol);
        $permisos = $_POST['permisos'] ?? [];

        if (!is_array($permisos) || empty($permisos)) {
            Sesion::mensajeFlash('advertencia', 'No se recibio ningun permiso para guardar.');
            Helper::redirigir(Helper::url('roles'));
        }

        Usuario::guardarPermisos($idRol, $permisos);

        Auditoria::registrar('roles', 'ACTUALIZAR',
            'Actualizacion de la matriz de permisos del rol "' . $rol['nombre'] . '".',
            ['entidad' => 'roles', 'id_entidad' => $idRol,
             'datos_antes' => $antes, 'datos_despues' => $permisos]);

        Sesion::mensajeFlash('exito',
            'Los permisos del rol "' . $rol['nombre'] . '" se guardaron correctamente.');
        Helper::redirigir(Helper::url('roles'));
    }
}
