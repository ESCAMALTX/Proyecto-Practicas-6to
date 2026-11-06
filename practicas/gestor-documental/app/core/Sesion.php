<?php
/**
 * =====================================================================
 * Archivo: app/core/Sesion.php
 * Descripcion: Gestion de sesiones, cierre por inactividad (RNF-003)
 *              y control de acceso basado en roles (RF-003).
 * =====================================================================
 */

class Sesion
{
    /**
     * Inicia la sesion con parametros seguros.
     */
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,   // Bloquea el acceso desde JavaScript
            'samesite' => 'Lax',  // Mitiga CSRF
        ]);
        session_name('GESTORDOCSID');
        session_start();
    }

    /**
     * Registra los datos del usuario en la sesion tras un login correcto.
     */
    public static function autenticar(array $usuario): void
    {
        session_regenerate_id(true); // Evita fijacion de sesion

        $_SESSION['usuario'] = [
            'id_usuario'      => (int) $usuario['id_usuario'],
            'nombre_completo' => $usuario['nombre_completo'],
            'usuario'         => $usuario['usuario'],
            'correo'          => $usuario['correo'],
            'id_rol'          => (int) $usuario['id_rol'],
            'rol'             => $usuario['rol'],
            'nivel_acceso'    => (int) $usuario['nivel_acceso'],
            'id_area'         => $usuario['id_area'] !== null ? (int) $usuario['id_area'] : null,
            'area'            => $usuario['area'],
        ];

        $_SESSION['ultima_actividad'] = time();
        $_SESSION['inicio_sesion']    = time();
        $_SESSION['token_csrf']       = bin2hex(random_bytes(32));
    }

    /**
     * Indica si hay una sesion valida y activa.
     */
    public static function activa(): bool
    {
        if (empty($_SESSION['usuario'])) {
            return false;
        }

        // Cierre automatico por inactividad
        $limite = MINUTOS_INACTIVIDAD * 60;
        if (isset($_SESSION['ultima_actividad']) &&
            (time() - $_SESSION['ultima_actividad']) > $limite) {
            self::cerrar();
            return false;
        }

        $_SESSION['ultima_actividad'] = time();
        return true;
    }

    /**
     * Devuelve los datos del usuario en sesion o null.
     */
    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    /**
     * Devuelve un campo concreto del usuario en sesion.
     */
    public static function dato(string $campo, $porDefecto = null)
    {
        return $_SESSION['usuario'][$campo] ?? $porDefecto;
    }

    /**
     * Nivel de acceso del usuario: 1=Consulta 2=Gestion 3=Administracion.
     */
    public static function nivel(): int
    {
        return (int) ($_SESSION['usuario']['nivel_acceso'] ?? 0);
    }

    /**
     * Verifica si el usuario tiene al menos un nivel de acceso.
     */
    public static function tieneNivel(int $nivelMinimo): bool
    {
        return self::nivel() >= $nivelMinimo;
    }

    /**
     * Exige sesion activa; si no la hay redirige al login (Regla: solo
     * usuarios autenticados acceden al sistema).
     */
    public static function exigirAutenticacion(): void
    {
        if (!self::activa()) {
            self::mensajeFlash('advertencia', 'Su sesion expiro. Inicie sesion nuevamente.');
            header('Location: index.php?modulo=auth&accion=login');
            exit;
        }
    }

    /**
     * Exige un nivel de acceso minimo (RF-003, RF-014).
     */
    public static function exigirNivel(int $nivelMinimo): void
    {
        self::exigirAutenticacion();

        if (!self::tieneNivel($nivelMinimo)) {
            self::mensajeFlash('error', 'No tiene permisos para acceder a ese modulo.');
            header('Location: index.php?modulo=panel');
            exit;
        }
    }

    /**
     * Comprueba un permiso especifico de la tabla permisos_rol.
     */
    public static function puede(string $modulo, string $accion = 'ver'): bool
    {
        if (!self::activa()) {
            return false;
        }

        $columna = match ($accion) {
            'ver'      => 'puede_ver',
            'crear'    => 'puede_crear',
            'editar'   => 'puede_editar',
            'eliminar' => 'puede_eliminar',
            default    => 'puede_ver',
        };

        $valor = Database::valor(
            "SELECT {$columna} FROM permisos_rol WHERE id_rol = ? AND modulo = ?",
            [self::dato('id_rol'), $modulo]
        );

        return (int) $valor === 1;
    }

    /**
     * Exige un permiso concreto; redirige si no lo tiene.
     */
    public static function exigirPermiso(string $modulo, string $accion = 'ver'): void
    {
        self::exigirAutenticacion();

        if (!self::puede($modulo, $accion)) {
            self::mensajeFlash('error', 'Accion no autorizada para su rol.');
            header('Location: index.php?modulo=panel');
            exit;
        }
    }

    // -----------------------------------------------------------------
    // Mensajes flash (notificaciones de una sola lectura)
    // -----------------------------------------------------------------
    public static function mensajeFlash(string $tipo, string $texto): void
    {
        $_SESSION['flash'][] = ['tipo' => $tipo, 'texto' => $texto];
    }

    public static function obtenerFlash(): array
    {
        $mensajes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $mensajes;
    }

    // -----------------------------------------------------------------
    // Proteccion CSRF
    // -----------------------------------------------------------------
    public static function tokenCsrf(): string
    {
        if (empty($_SESSION['token_csrf'])) {
            $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['token_csrf'];
    }

    public static function campoCsrf(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::tokenCsrf() . '">';
    }

    public static function validarCsrf(?string $token): bool
    {
        return !empty($token)
            && !empty($_SESSION['token_csrf'])
            && hash_equals($_SESSION['token_csrf'], $token);
    }

    // -----------------------------------------------------------------
    // Cierre de sesion
    // -----------------------------------------------------------------
    public static function cerrar(): void
    {
        // Marca la sesion como cerrada en la base de datos
        if (!empty($_SESSION['id_sesion_bd'])) {
            try {
                Database::actualizar(
                    'sesiones_usuario',
                    ['activa' => 0, 'fecha_cierre' => date('Y-m-d H:i:s')],
                    'id_sesion = ?',
                    [$_SESSION['id_sesion_bd']]
                );
            } catch (Throwable $e) {
                // El cierre de sesion no debe fallar por esto
            }
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']);
        }

        session_destroy();
    }
}
