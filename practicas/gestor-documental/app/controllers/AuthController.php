<?php
/**
 * =====================================================================
 * Archivo: app/controllers/AuthController.php
 * Descripcion: Inicio y cierre de sesion, registro de asesores y
 *              cambio de contrasena (RF-001).
 * =====================================================================
 */

class AuthController
{
    /**
     * Muestra el formulario de inicio de sesion.
     */
    public function login(): void
    {
        // Si ya hay una sesion activa se redirige al panel
        if (Sesion::activa()) {
            Helper::redirigir(Helper::url('panel'));
        }

        $errores    = [];
        $identificador = '';

        if (Helper::esPost()) {
            $identificador = Helper::post('identificador');
            $clave         = $_POST['clave'] ?? '';
            $csrf          = Helper::post('csrf_token');

            // ---------------------------------------------------------
            // 1. Verificacion del token CSRF
            // ---------------------------------------------------------
            if (!Sesion::validarCsrf($csrf)) {
                $errores['general'] = 'La sesion del formulario expiro. Intente nuevamente.';
                Auditoria::permisoDenegado('Token CSRF invalido en el formulario de inicio de sesion.');
            } else {
                // -----------------------------------------------------
                // 2. Validacion de campos obligatorios
                // -----------------------------------------------------
                $validador = (new Validador($_POST, [
                    'identificador' => 'usuario o correo',
                    'clave'         => 'contrasena',
                ]))
                    ->requerido('identificador')
                    ->requerido('clave');

                if (!$validador->pasa()) {
                    $errores = $validador->errores();
                    Auditoria::login($identificador ?: 'desconocido', false);
                } else {
                    // -------------------------------------------------
                    // 3. Verificacion de credenciales
                    // -------------------------------------------------
                    $resultado = Usuario::verificarCredenciales($identificador, $clave);

                    if ($resultado['ok']) {
                        $usuario = $resultado['usuario'];

                        Sesion::autenticar($usuario);

                        // Registro de la sesion activa para el control de inactividad
                        $idSesionBd = Usuario::registrarSesion((int) $usuario['id_usuario']);
                        $_SESSION['id_sesion_bd'] = $idSesionBd;

                        Auditoria::registrar('autenticacion', 'LOGIN',
                            'Inicio de sesion exitoso.', ['entidad' => 'usuarios',
                            'id_entidad' => $usuario['id_usuario']]);

                        // Aviso si la contrasena es la asignada por defecto
                        if ((int) $usuario['debe_cambiar_clave'] === 1) {
                            Sesion::mensajeFlash('advertencia',
                                'Su contrasena es temporal. Por seguridad, cambiela en "Mi perfil".');
                        }

                        Helper::redirigir(Helper::url('panel'));
                    }

                    $errores['general']   = $resultado['error'];
                    Auditoria::login($identificador, false);
                }
            }
        }

        // Datos para la vista
        $tituloPagina = 'Iniciar sesion';
        $vista        = 'auth/login';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/auth/login.php';
    }

    /**
     * Cierra la sesion del usuario.
     */
    public function logout(): void
    {
        $usuario = Sesion::dato('usuario');

        if ($usuario) {
            Auditoria::logout($usuario);
        }

        Sesion::cerrar();

        // Se inicia una sesion nueva solo para transportar el mensaje
        Sesion::iniciar();
        Sesion::mensajeFlash('exito', 'Su sesion se cerro correctamente.');

        Helper::redirigir(Helper::url('auth', 'login'));
    }

    /**
     * Formulario de registro publico. Crea cuentas con rol Asesor.
     * Regla de negocio: los roles con privilegios solo los asigna
     * el administrador desde el modulo de usuarios.
     */
    public function registro(): void
    {
        if (Sesion::activa()) {
            Helper::redirigir(Helper::url('panel'));
        }

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
                    'clave'           => 'contrasena',
                    'clave2'          => 'confirmacion de contrasena',
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
                    ->requerido('id_area')
                    ->enteroPositivo('id_area', 'Debe seleccionar un area.')
                    ->requerido('clave')
                    ->claveFuerte('clave')
                    ->requerido('clave2')
                    ->coinciden('clave', 'clave2');

                // Verificacion de duplicados
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
                    // El rol Asesor se asigna siempre, sin importar lo enviado
                    $idRolAsesor = (int) Database::valor(
                        "SELECT id_rol FROM roles WHERE nombre = 'Asesor'"
                    );

                    $idUsuario = Usuario::crear([
                        'nombre_completo' => Helper::post('nombre_completo'),
                        'documento'       => Helper::post('documento'),
                        'correo'          => Helper::post('correo'),
                        'usuario'         => Helper::post('usuario'),
                        'clave'           => $_POST['clave'],
                        'id_rol'          => $idRolAsesor,
                        'id_area'         => (int) Helper::post('id_area'),
                        'telefono'        => Helper::post('telefono'),
                        'cargo'           => Helper::post('cargo'),
                        'estado'          => 'ACTIVO',
                    ]);

                    Auditoria::registrar('usuarios', 'CREAR',
                        'Registro publico de un nuevo usuario con rol Asesor.',
                        ['entidad' => 'usuarios', 'id_entidad' => $idUsuario,
                         'datos_despues' => ['usuario' => Helper::post('usuario'),
                                             'correo'  => Helper::post('correo')]]);

                    Sesion::mensajeFlash('exito',
                        'Su cuenta fue creada correctamente. Ya puede iniciar sesion.');
                    Helper::redirigir(Helper::url('auth', 'login'));
                } else {
                    $errores = array_merge($validador->errores(), $errores);
                }
            }
        }

        $areas        = Usuario::listarAreas();
        $tituloPagina = 'Crear cuenta';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/auth/registro.php';
    }

    /**
     * Formulario para cambiar la contrasena del propio usuario.
     */
    public function cambiarClave(): void
    {
        Sesion::exigirAutenticacion();

        $errores = [];
        $exito   = false;

        if (Helper::esPost()) {
            if (!Sesion::validarCsrf(Helper::post('csrf_token'))) {
                $errores['general'] = 'La sesion del formulario expiro. Intente nuevamente.';
            } else {
                $validador = (new Validador($_POST, [
                    'clave_actual' => 'contrasena actual',
                    'clave_nueva'  => 'contrasena nueva',
                    'clave_nueva2' => 'confirmacion',
                ]))
                    ->requerido('clave_actual')
                    ->requerido('clave_nueva')
                    ->claveFuerte('clave_nueva')
                    ->requerido('clave_nueva2')
                    ->coinciden('clave_nueva', 'clave_nueva2')
                    ->personalizado('clave_nueva',
                        Helper::post('clave_actual') !== Helper::post('clave_nueva'),
                        'La contrasena nueva debe ser diferente a la actual.');

                if ($validador->pasa()) {
                    $resultado = Usuario::cambiarClavePropia(
                        (int) Sesion::dato('id_usuario'),
                        $_POST['clave_actual'],
                        $_POST['clave_nueva']
                    );

                    if ($resultado['ok']) {
                        Auditoria::registrar('usuarios', 'ACTUALIZAR',
                            'El usuario cambio su propia contrasena.',
                            ['entidad' => 'usuarios',
                             'id_entidad' => Sesion::dato('id_usuario')]);

                        Sesion::mensajeFlash('exito', 'Su contrasena fue actualizada correctamente.');
                        Helper::redirigir(Helper::url('perfil'));
                    }

                    $errores['clave_actual'] = $resultado['error'];
                } else {
                    $errores = $validador->errores();
                }
            }
        }

        $tituloPagina = 'Cambiar contrasena';
        $csrfToken    = Sesion::tokenCsrf();

        require RUTA_VISTAS . '/auth/cambiar_clave.php';
    }
}
