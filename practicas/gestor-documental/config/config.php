<?php
/**
 * =====================================================================
 * SISTEMA PRIVADO DE GESTION Y CONSULTA DOCUMENTAL
 * Archivo: config/config.php
 * Descripcion: Parametros globales de configuracion de la aplicacion.
 * =====================================================================
 */

// ---------------------------------------------------------------------
// Zona horaria y codificacion
// ---------------------------------------------------------------------
date_default_timezone_set('America/Bogota');
mb_internal_encoding('UTF-8');

// ---------------------------------------------------------------------
// Entorno: 'desarrollo' muestra errores, 'produccion' los oculta.
// ---------------------------------------------------------------------
define('ENTORNO', 'desarrollo');

if (ENTORNO === 'desarrollo') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ---------------------------------------------------------------------
// Datos de conexion a MySQL (XAMPP por defecto)
// ---------------------------------------------------------------------
define('DB_HOST', '127.0.0.1');
define('DB_PUERTO', 3306);
define('DB_NOMBRE', 'gestor_documental');
define('DB_USUARIO', 'root');
define('DB_CLAVE', '12345');      // Contrasena del usuario root de MySQL
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------------
// Rutas del proyecto
// Se normalizan a "/" para evitar mezclas de separadores en Windows.
// ---------------------------------------------------------------------
define('RUTA_BASE',      str_replace('\\', '/', dirname(__DIR__)));
define('RUTA_APP',       RUTA_BASE . '/app');
define('RUTA_VISTAS',    RUTA_APP  . '/views');
define('RUTA_UPLOADS',   RUTA_BASE . '/public/uploads');
define('RUTA_RESPALDOS', RUTA_BASE . '/respaldos');

// Ruta publica usada por el navegador (se calcula sola desde el script)
define('URL_BASE', 'http://localhost/gestor-documental/public');
define('URL_UPLOADS', URL_BASE . '/uploads');

// ---------------------------------------------------------------------
// Archivos permitidos (RF-005, RF-029)
// ---------------------------------------------------------------------
define('PESO_MAXIMO_MB', 20);
define('PESO_MAXIMO_BYTES', PESO_MAXIMO_MB * 1024 * 1024);

define('EXTENSIONES_PERMITIDAS', [
    'pdf'  => ['application/pdf'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document',
               'application/zip'],
    'doc'  => ['application/msword'],
    'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
               'application/zip'],
    'xls'  => ['application/vnd.ms-excel'],
    'txt'  => ['text/plain'],
]);

// ---------------------------------------------------------------------
// Seguridad
// ---------------------------------------------------------------------
define('MAX_INTENTOS_LOGIN',   5);
define('MINUTOS_INACTIVIDAD',  30);
define('NOMBRE_SISTEMA',       'Sistema Privado de Gestion y Consulta Documental');
define('VERSION_SISTEMA',      '1.0.0');

// ---------------------------------------------------------------------
// Paginacion
// ---------------------------------------------------------------------
define('ITEMS_POR_PAGINA', 10);

// ---------------------------------------------------------------------
// Registro de errores en archivo
// ---------------------------------------------------------------------
define('RUTA_LOG_ERRORES', RUTA_BASE . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'errores.log');
