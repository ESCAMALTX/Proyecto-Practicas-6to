<?php
/**
 * =====================================================================
 * SISTEMA PRIVADO DE GESTION Y CONSULTA DOCUMENTAL
 * Archivo: public/index.php
 * Descripcion: Punto de entrada unico. Carga la configuracion, las
 *              clases del nucleo y enruta la peticion al controlador.
 * =====================================================================
 */

// ---------------------------------------------------------------------
// 1. Configuracion
// ---------------------------------------------------------------------
require_once dirname(__DIR__) . '/config/config.php';

// ---------------------------------------------------------------------
// 2. Nucleo del sistema
// ---------------------------------------------------------------------
require_once RUTA_APP . '/core/Database.php';
require_once RUTA_APP . '/core/Helper.php';
require_once RUTA_APP . '/core/Sesion.php';
require_once RUTA_APP . '/core/Auditoria.php';
require_once RUTA_APP . '/core/Validador.php';
require_once RUTA_APP . '/core/GestorArchivos.php';

// ---------------------------------------------------------------------
// 3. Modelos
// ---------------------------------------------------------------------
require_once RUTA_APP . '/models/Usuario.php';
require_once RUTA_APP . '/models/Documento.php';
require_once RUTA_APP . '/models/Catalogo.php';
require_once RUTA_APP . '/models/Consulta.php';
require_once RUTA_APP . '/models/Respaldo.php';
require_once RUTA_APP . '/models/AuditoriaModelo.php';

// ---------------------------------------------------------------------
// 4. Controladores
// ---------------------------------------------------------------------
require_once RUTA_APP . '/controllers/AuthController.php';
require_once RUTA_APP . '/controllers/PanelController.php';
require_once RUTA_APP . '/controllers/DocumentoController.php';
require_once RUTA_APP . '/controllers/CatalogoController.php';
require_once RUTA_APP . '/controllers/UsuarioController.php';
require_once RUTA_APP . '/controllers/AuditoriaController.php';
require_once RUTA_APP . '/controllers/ReporteController.php';

// ---------------------------------------------------------------------
// 5. Manejador global de errores
// ---------------------------------------------------------------------
set_exception_handler(function (Throwable $e) {
    $dir = dirname(RUTA_LOG_ERRORES);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    @file_put_contents(
        RUTA_LOG_ERRORES,
        sprintf("[%s] %s en %s:%d%s%s%s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            PHP_EOL,
            $e->getTraceAsString(),
            PHP_EOL . str_repeat('-', 80) . PHP_EOL
        ),
        FILE_APPEND
    );

    // Registro en base de datos cuando la conexion esta disponible
    try {
        Database::insertar('log_errores', [
            'id_usuario'     => Sesion::dato('id_usuario'),
            'archivo'        => $e->getFile(),
            'linea'          => $e->getLine(),
            'mensaje'        => mb_substr($e->getMessage(), 0, 500),
            'traza'          => mb_substr($e->getTraceAsString(), 0, 5000),
            'url_solicitada' => mb_substr($_SERVER['REQUEST_URI'] ?? '', 0, 400),
        ]);
    } catch (Throwable $ignorado) {
        // Si la base de datos no responde, ya quedo registrado en el archivo
    }

    http_response_code(500);

    if (ENTORNO === 'desarrollo') {
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
           . '<title>Error del sistema</title>'
           . '<style>body{font-family:system-ui,sans-serif;background:#f4f6f8;padding:40px;}'
           . '.caja{background:#fff;border-left:5px solid #c0392b;padding:24px;'
           . 'border-radius:8px;max-width:800px;margin:auto;box-shadow:0 2px 10px rgba(0,0,0,.1);}'
           . 'h1{color:#c0392b;font-size:20px;margin-top:0;}'
           . 'pre{background:#f8f9fa;padding:14px;border-radius:6px;overflow:auto;font-size:13px;}'
           . 'a{color:#2c5f8a;}</style></head><body><div class="caja">'
           . '<h1>Ocurrio un error en el sistema</h1>'
           . '<p><strong>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</strong></p>'
           . '<p>Archivo: <code>' . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . '</code> '
           . 'en la linea ' . $e->getLine() . '</p>'
           . '<details><summary>Ver traza completa</summary><pre>'
           . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8')
           . '</pre></details>'
           . '<p><a href="index.php?modulo=panel">Volver al panel</a></p>'
           . '</div></body></html>';
    } else {
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
           . '<title>Error</title></head><body>'
           . '<h1>Ocurrio un error inesperado</h1>'
           . '<p>El inconveniente quedo registrado. Contacte al administrador del sistema.</p>'
           . '<p><a href="index.php?modulo=panel">Volver al panel</a></p>'
           . '</body></html>';
    }
});

set_error_handler(function ($numero, $mensaje, $archivo, $linea) {
    throw new ErrorException($mensaje, 0, $numero, $archivo, $linea);
});

// ---------------------------------------------------------------------
// 6. Inicio de la sesion y cabecera de codificacion
// ---------------------------------------------------------------------
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
Sesion::iniciar();

// ---------------------------------------------------------------------
// 7. Tabla de rutas: modulo => [accion => [Controlador, metodo]]
// ---------------------------------------------------------------------
$rutas = [
    'auth' => [
        'login'         => ['AuthController',      'login'],
        'logout'        => ['AuthController',      'logout'],
        'registro'      => ['AuthController',      'registro'],
        'cambiar-clave' => ['AuthController',      'cambiarClave'],
    ],
    'panel' => [
        ''                => ['PanelController',   'index'],
        'index'           => ['PanelController',   'index'],
        'buscar'          => ['PanelController',   'buscar'],
        'detalle-consulta'=> ['PanelController',   'detalleConsulta'],
        'perfil'          => ['PanelController',   'perfil'],
        'notificaciones'  => ['PanelController',   'leerNotificaciones'],
    ],
    'documentos' => [
        ''              => ['DocumentoController', 'index'],
        'index'         => ['DocumentoController', 'index'],
        'crear'         => ['DocumentoController', 'crear'],
        'ver'           => ['DocumentoController', 'ver'],
        'editar'        => ['DocumentoController', 'editar'],
        'nueva-version' => ['DocumentoController', 'nuevaVersion'],
        'descargar'     => ['DocumentoController', 'descargar'],
        'eliminar'      => ['DocumentoController', 'eliminar'],
    ],
    'categorias' => [
        ''         => ['CatalogoController', 'categorias'],
        'guardar'  => ['CatalogoController', 'guardarCategoria'],
        'eliminar' => ['CatalogoController', 'eliminarCategoria'],
    ],
    'casos' => [
        ''         => ['CatalogoController', 'casos'],
        'guardar'  => ['CatalogoController', 'guardarCaso'],
        'eliminar' => ['CatalogoController', 'eliminarCaso'],
    ],
    'areas' => [
        ''        => ['CatalogoController', 'areas'],
        'guardar' => ['CatalogoController', 'guardarArea'],
    ],
    'configuracion' => [
        ''        => ['CatalogoController', 'configuracion'],
        'guardar' => ['CatalogoController', 'guardarConfiguracion'],
    ],
    'usuarios' => [
        ''                 => ['UsuarioController', 'index'],
        'index'            => ['UsuarioController', 'index'],
        'crear'            => ['UsuarioController', 'crear'],
        'editar'           => ['UsuarioController', 'editar'],
        'cambiar-estado'   => ['UsuarioController', 'cambiarEstado'],
        'restablecer-clave'=> ['UsuarioController', 'restablecerClave'],
    ],
    'roles' => [
        ''        => ['UsuarioController', 'roles'],
        'guardar' => ['UsuarioController', 'guardarPermisos'],
    ],
    'auditoria' => [
        ''         => ['AuditoriaController', 'index'],
        'index'    => ['AuditoriaController', 'index'],
        'detalle'  => ['AuditoriaController', 'detalle'],
        'exportar' => ['AuditoriaController', 'exportar'],
        'depurar'  => ['AuditoriaController', 'depurar'],
    ],
    'respaldos' => [
        ''           => ['AuditoriaController', 'respaldos'],
        'generar'    => ['AuditoriaController', 'generarRespaldo'],
        'descargar'  => ['AuditoriaController', 'descargarRespaldo'],
        'eliminar'   => ['AuditoriaController', 'eliminarRespaldo'],
    ],
    'reportes' => [
        ''                    => ['ReporteController', 'index'],
        'index'               => ['ReporteController', 'index'],
        'inventario'          => ['ReporteController', 'exportarInventario'],
        'consultas'           => ['ReporteController', 'exportarConsultas'],
        'usuarios'            => ['ReporteController', 'exportarUsuarios'],
        'imprimir'            => ['ReporteController', 'imprimir'],
    ],
];

// ---------------------------------------------------------------------
// 8. Resolucion de la peticion
// ---------------------------------------------------------------------
$modulo = Helper::get('modulo');
$accion = Helper::get('accion');

// Sin modulo indicado: se envia al panel o al login segun la sesion
if ($modulo === '') {
    $modulo = Sesion::activa() ? 'panel' : 'auth';
    $accion = Sesion::activa() ? 'index' : 'login';
}

// Modulo inexistente
if (!isset($rutas[$modulo])) {
    http_response_code(404);
    $modulo = Sesion::activa() ? 'panel' : 'auth';
    $accion = Sesion::activa() ? 'index' : 'login';
    Sesion::mensajeFlash('advertencia', 'El modulo solicitado no existe.');
}

// Accion por defecto del modulo
if ($accion === '') {
    $accion = array_key_first($rutas[$modulo]);
}

// Accion inexistente
if (!isset($rutas[$modulo][$accion])) {
    http_response_code(404);
    Auditoria::permisoDenegado("Intento de acceso a la accion no valida: {$modulo}/{$accion}");

    Sesion::mensajeFlash('advertencia', 'La accion solicitada no existe.');
    Helper::redirigir(Helper::url(Sesion::activa() ? 'panel' : 'auth',
        Sesion::activa() ? 'index' : 'login'));
}

$controlador = $rutas[$modulo][$accion][0];
$metodo      = $rutas[$modulo][$accion][1];

// Verificar que el metodo existe antes de invocarlo
if (!class_exists($controlador) || !method_exists($controlador, $metodo)) {
    throw new RuntimeException("La ruta {$modulo}/{$accion} apunta a un metodo inexistente.");
}

// ---------------------------------------------------------------------
// 9. Ejecucion
// ---------------------------------------------------------------------
$instancia = new $controlador();
$instancia->$metodo();
