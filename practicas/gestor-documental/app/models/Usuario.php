<?php
/**
 * =====================================================================
 * Archivo: app/models/Usuario.php
 * Descripcion: Acceso a datos de usuarios, roles y autenticacion.
 * =====================================================================
 */

class Usuario
{
    /**
     * Busca un usuario por su nombre de acceso o correo, incluyendo
     * el rol y el area mediante la vista v_usuarios_completo.
     */
    public static function porUsuarioOCorreo(string $identificador): ?array
    {
        return Database::uno(
            "SELECT u.*, r.nombre AS rol, r.nivel_acceso, a.nombre AS area
               FROM usuarios u
               INNER JOIN roles r ON r.id_rol = u.id_rol
               LEFT  JOIN areas a ON a.id_area = u.id_area
              WHERE u.usuario = ? OR u.correo = ?
              LIMIT 1",
            [$identificador, $identificador]
        );
    }

    public static function porId(int $id): ?array
    {
        return Database::uno(
            "SELECT * FROM v_usuarios_completo WHERE id_usuario = ?",
            [$id]
        );
    }

    /**
     * Listado paginado con filtros de busqueda.
     */
    public static function listar(string $busqueda = '', int $idRol = 0, string $estado = '', int $limite = ITEMS_POR_PAGINA, int $offset = 0): array
    {
        $sql    = "SELECT * FROM v_usuarios_completo WHERE 1=1";
        $params = [];

        if ($busqueda !== '') {
            $sql .= " AND (nombre_completo LIKE ? OR usuario LIKE ? OR correo LIKE ? OR documento LIKE ?)";
            $comodin = "%{$busqueda}%";
            array_push($params, $comodin, $comodin, $comodin, $comodin);
        }

        if ($idRol > 0) {
            $sql .= " AND id_rol = ?";
            $params[] = $idRol;
        }

        if ($estado !== '') {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }

        $sql .= " ORDER BY nombre_completo ASC LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;

        return Database::todos($sql, $params);
    }

    /**
     * Cuenta los usuarios que cumplen los filtros.
     */
    public static function contar(string $busqueda = '', int $idRol = 0, string $estado = ''): int
    {
        $sql    = "SELECT COUNT(*) FROM v_usuarios_completo WHERE 1=1";
        $params = [];

        if ($busqueda !== '') {
            $sql .= " AND (nombre_completo LIKE ? OR usuario LIKE ? OR correo LIKE ? OR documento LIKE ?)";
            $comodin = "%{$busqueda}%";
            array_push($params, $comodin, $comodin, $comodin, $comodin);
        }

        if ($idRol > 0) {
            $sql .= " AND id_rol = ?";
            $params[] = $idRol;
        }

        if ($estado !== '') {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }

        return (int) Database::valor($sql, $params);
    }

    /**
     * Crea un usuario. La clave se hashea con bcrypt.
     */
    public static function crear(array $datos): int
    {
        return Database::insertar('usuarios', [
            'nombre_completo' => $datos['nombre_completo'],
            'documento'       => $datos['documento'],
            'correo'          => $datos['correo'],
            'usuario'         => $datos['usuario'],
            'clave_hash'      => password_hash($datos['clave'], PASSWORD_BCRYPT, ['cost' => 10]),
            'id_rol'          => (int) $datos['id_rol'],
            'id_area'         => !empty($datos['id_area']) ? (int) $datos['id_area'] : null,
            'telefono'        => $datos['telefono'] ?? null,
            'cargo'           => $datos['cargo'] ?? null,
            'estado'          => $datos['estado'] ?? 'ACTIVO',
            'debe_cambiar_clave' => 1,
        ]);
    }

    /**
     * Actualiza los datos de un usuario (sin tocar la clave).
     */
    public static function actualizar(int $id, array $datos): int
    {
        return Database::actualizar('usuarios', [
            'nombre_completo' => $datos['nombre_completo'],
            'documento'       => $datos['documento'],
            'correo'          => $datos['correo'],
            'usuario'         => $datos['usuario'],
            'id_rol'          => (int) $datos['id_rol'],
            'id_area'         => !empty($datos['id_area']) ? (int) $datos['id_area'] : null,
            'telefono'        => $datos['telefono'] ?? null,
            'cargo'           => $datos['cargo'] ?? null,
            'estado'          => $datos['estado'] ?? 'ACTIVO',
        ], 'id_usuario = ?', [$id]);
    }

    /**
     * Cambia el estado del usuario (activar, desactivar, bloquear).
     */
    public static function cambiarEstado(int $id, string $estado): int
    {
        $datos = ['estado' => $estado];
        if ($estado === 'ACTIVO') {
            $datos['intentos_fallidos'] = 0;
        }

        return Database::actualizar('usuarios', $datos, 'id_usuario = ?', [$id]);
    }

    /**
     * Restablece la contrasena de un usuario.
     */
    public static function restablecerClave(int $id, string $nuevaClave): int
    {
        return Database::actualizar('usuarios', [
            'clave_hash'         => password_hash($nuevaClave, PASSWORD_BCRYPT, ['cost' => 10]),
            'debe_cambiar_clave' => 1,
            'intentos_fallidos'  => 0,
        ], 'id_usuario = ?', [$id]);
    }

    /**
     * Cambia la contrasena del propio usuario, verificando la anterior.
     */
    public static function cambiarClavePropia(int $id, string $claveActual, string $claveNueva): array
    {
        $usuario = self::porId($id);

        if (!$usuario) {
            return ['ok' => false, 'error' => 'Usuario no encontrado.'];
        }

        $hash = Database::valor('SELECT clave_hash FROM usuarios WHERE id_usuario = ?', [$id]);

        if (!password_verify($claveActual, (string) $hash)) {
            return ['ok' => false, 'error' => 'La contrasena actual no es correcta.'];
        }

        Database::actualizar('usuarios', [
            'clave_hash'         => password_hash($claveNueva, PASSWORD_BCRYPT, ['cost' => 10]),
            'debe_cambiar_clave' => 0,
        ], 'id_usuario = ?', [$id]);

        return ['ok' => true];
    }

    /**
     * Verifica unas credenciales y devuelve el usuario si son validas.
     *
     * @return array{ok:bool, error?:string, usuario?:array}
     */
    public static function verificarCredenciales(string $identificador, string $clave): array
    {
        $usuario = self::porUsuarioOCorreo($identificador);

        // Mensaje generico: no revela si el usuario existe
        if (!$usuario) {
            return ['ok' => false, 'error' => 'Usuario o contrasena incorrectos.'];
        }

        if ($usuario['estado'] === 'BLOQUEADO') {
            return ['ok' => false,
                    'error' => 'Su cuenta esta bloqueada por demasiados intentos fallidos. '
                             . 'Contacte al administrador.'];
        }

        if ($usuario['estado'] === 'INACTIVO') {
            return ['ok' => false, 'error' => 'Su cuenta esta inactiva. Contacte al administrador.'];
        }

        if (!password_verify($clave, $usuario['clave_hash'])) {
            self::registrarIntentoFallido((int) $usuario['id_usuario']);
            $restantes = MAX_INTENTOS_LOGIN - ((int) $usuario['intentos_fallidos'] + 1);

            $mensaje = 'Usuario o contrasena incorrectos.';
            if ($restantes > 0 && $restantes <= 2) {
                $mensaje .= " Le quedan {$restantes} intento(s) antes del bloqueo.";
            }

            return ['ok' => false, 'error' => $mensaje];
        }

        // Credenciales correctas: se limpian los intentos y se marca el acceso
        Database::actualizar('usuarios', [
            'intentos_fallidos' => 0,
            'ultimo_acceso'     => date('Y-m-d H:i:s'),
        ], 'id_usuario = ?', [$usuario['id_usuario']]);

        return ['ok' => true, 'usuario' => $usuario];
    }

    /**
     * Incrementa el contador de intentos fallidos. Al llegar al limite,
     * el trigger tr_usuario_intento_fallido marca la cuenta como BLOQUEADA.
     */
    private static function registrarIntentoFallido(int $id): void
    {
        Database::consultar(
            'UPDATE usuarios SET intentos_fallidos = intentos_fallidos + 1 WHERE id_usuario = ?',
            [$id]
        );
    }

    /**
     * Comprueba si un valor ya existe en una columna (para validar unicidad).
     */
    public static function existe(string $columna, string $valor, int $exceptoId = 0): bool
    {
        $permitidas = ['usuario', 'correo', 'documento'];
        if (!in_array($columna, $permitidas, true)) {
            return false;
        }

        $sql    = "SELECT COUNT(*) FROM usuarios WHERE {$columna} = ?";
        $params = [$valor];

        if ($exceptoId > 0) {
            $sql .= " AND id_usuario <> ?";
            $params[] = $exceptoId;
        }

        return (int) Database::valor($sql, $params) > 0;
    }

    /**
     * Lista los roles disponibles.
     */
    public static function listarRoles(): array
    {
        return Database::todos('SELECT * FROM roles WHERE activo = 1 ORDER BY nivel_acceso DESC');
    }

    /**
     * Obtiene los permisos de un rol agrupados por modulo.
     */
    public static function permisosDeRol(int $idRol): array
    {
        return Database::todos(
            'SELECT modulo, puede_ver, puede_crear, puede_editar, puede_eliminar
               FROM permisos_rol WHERE id_rol = ? ORDER BY modulo',
            [$idRol]
        );
    }

    /**
     * Actualiza la matriz de permisos de un rol.
     */
    public static function guardarPermisos(int $idRol, array $permisos): void
    {
        Database::iniciarTransaccion();

        try {
            foreach ($permisos as $modulo => $acciones) {
                Database::consultar(
                    'INSERT INTO permisos_rol (id_rol, modulo, puede_ver, puede_crear, puede_editar, puede_eliminar)
                     VALUES (?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        puede_ver      = VALUES(puede_ver),
                        puede_crear    = VALUES(puede_crear),
                        puede_editar   = VALUES(puede_editar),
                        puede_eliminar = VALUES(puede_eliminar)',
                    [
                        $idRol,
                        $modulo,
                        !empty($acciones['ver'])      ? 1 : 0,
                        !empty($acciones['crear'])    ? 1 : 0,
                        !empty($acciones['editar'])   ? 1 : 0,
                        !empty($acciones['eliminar']) ? 1 : 0,
                    ]
                );
            }
            Database::confirmar();
        } catch (Throwable $e) {
            Database::revertir();
            throw $e;
        }
    }

    /**
     * Registra el inicio de una sesion activa.
     */
    public static function registrarSesion(int $idUsuario): int
    {
        return Database::insertar('sesiones_usuario', [
            'id_usuario'     => $idUsuario,
            'token_sesion'   => bin2hex(random_bytes(32)),
            'direccion_ip'   => Helper::ip(),
            'agente_usuario' => Helper::agente(),
        ]);
    }

    /**
     * Actualiza la actividad de la sesion actual.
     */
    public static function tocarSesion(int $idSesion): void
    {
        Database::actualizar('sesiones_usuario',
            ['ultima_actividad' => date('Y-m-d H:i:s')],
            'id_sesion = ?', [$idSesion]);
    }

    /**
     * Lista las areas disponibles.
     */
    public static function listarAreas(): array
    {
        return Database::todos('SELECT * FROM areas WHERE activo = 1 ORDER BY nombre');
    }

    /**
     * Obtiene el nombre de un area por su id.
     */
    public static function nombreArea(?int $idArea): string
    {
        if (!$idArea) {
            return 'Sin area';
        }

        return (string) (Database::valor('SELECT nombre FROM areas WHERE id_area = ?', [$idArea]) ?? 'Sin area');
    }
}
