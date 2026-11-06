<?php
/**
 * =====================================================================
 * Archivo: app/core/Auditoria.php
 * Descripcion: Registro de acciones criticas en la bitacora
 *              (RF-017, RNF-007, RNF-020).
 * =====================================================================
 */

class Auditoria
{
    /**
     * Registra un evento en la tabla auditoria.
     *
     * @param string $modulo      Modulo donde ocurre la accion.
     * @param string $accion      CREAR, ACTUALIZAR, ELIMINAR, LOGIN, LOGOUT, DESCARGAR, CONSULTAR, RESPALDO.
     * @param string $descripcion Texto legible del evento.
     * @param array  $opciones    entidad, id_entidad, datos_antes, datos_despues, resultado.
     */
    public static function registrar(
        string $modulo,
        string $accion,
        string $descripcion,
        array $opciones = []
    ): void {
        try {
            $usuario = Sesion::usuario();

            Database::insertar('auditoria', [
                'id_usuario'    => $usuario['id_usuario'] ?? null,
                'usuario_texto' => $usuario['usuario'] ?? ($opciones['usuario_texto'] ?? 'anonimo'),
                'modulo'        => $modulo,
                'accion'        => strtoupper($accion),
                'entidad'       => $opciones['entidad']       ?? null,
                'id_entidad'    => $opciones['id_entidad']    ?? null,
                'descripcion'   => mb_substr($descripcion, 0, 500),
                'datos_antes'   => isset($opciones['datos_antes'])
                                     ? Helper::aJson($opciones['datos_antes']) : null,
                'datos_despues' => isset($opciones['datos_despues'])
                                     ? Helper::aJson($opciones['datos_despues']) : null,
                'direccion_ip'  => Helper::ip(),
                'agente_usuario'=> Helper::agente(),
                'resultado'     => $opciones['resultado'] ?? 'EXITO',
            ]);
        } catch (Throwable $e) {
            // La auditoria nunca debe interrumpir la operacion principal
            self::guardarError($e->getMessage());
        }
    }

    /**
     * Atajos para las acciones mas frecuentes.
     */
    public static function login(string $usuario, bool $exito): void
    {
        self::registrar(
            'autenticacion',
            'LOGIN',
            $exito
                ? "Inicio de sesion exitoso del usuario {$usuario}."
                : "Intento de inicio de sesion fallido para el usuario {$usuario}.",
            ['entidad' => 'usuarios', 'resultado' => $exito ? 'EXITO' : 'FALLO',
             'usuario_texto' => $usuario]
        );
    }

    public static function logout(string $usuario): void
    {
        self::registrar('autenticacion', 'LOGOUT',
            "Cierre de sesion del usuario {$usuario}.",
            ['entidad' => 'usuarios', 'usuario_texto' => $usuario]);
    }

    public static function descarga(int $idDocumento, string $titulo): void
    {
        self::registrar('documentos', 'DESCARGAR',
            "Descarga del documento \"{$titulo}\".",
            ['entidad' => 'documentos', 'id_entidad' => $idDocumento]);
    }

    public static function permisoDenegado(string $detalle): void
    {
        self::registrar('seguridad', 'ACCESO_DENEGADO', $detalle,
            ['resultado' => 'ADVERTENCIA']);
    }

    /**
     * Guarda el fallo del propio registro de auditoria.
     */
    private static function guardarError(string $mensaje): void
    {
        $dir = dirname(RUTA_LOG_ERRORES);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents(
            RUTA_LOG_ERRORES,
            sprintf("[%s] AUDITORIA: %s%s", date('Y-m-d H:i:s'), $mensaje, PHP_EOL),
            FILE_APPEND
        );
    }
}
