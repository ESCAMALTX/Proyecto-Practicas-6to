<?php
/**
 * =====================================================================
 * Archivo: app/views/partials/header.php
 * Descripcion: Estructura comun de la interfaz interna y menu lateral.
 * =====================================================================
 */

$usuarioActual = Sesion::usuario();
$nivelActual   = Sesion::nivel();
$moduloActual  = Helper::get('modulo', 'panel');
$accionActual  = Helper::get('accion');

/**
 * Devuelve la clase de enlace activo comparando el modulo recibido.
 */
$activo = function (string $modulo) use ($moduloActual): string {
    return $moduloActual === $modulo ? 'menu__enlace menu__enlace--activo' : 'menu__enlace';
};

// Notificaciones sin leer del usuario
$notifPendientes = (int) Database::valor(
    'SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leida = 0',
    [Sesion::dato('id_usuario')]
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= Helper::e(NOMBRE_SISTEMA) ?>">
    <meta name="robots" content="noindex, nofollow">
    <title><?= Helper::e($tituloPagina ?? 'Panel') ?> · <?= Helper::e(NOMBRE_SISTEMA) ?></title>

    <!-- Hojas de estilo en orden de cascada -->
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/formularios.css">
    <link rel="stylesheet" href="assets/css/componentes.css">
    <link rel="stylesheet" href="assets/css/avisos.css">
    <link rel="stylesheet" href="assets/css/impresion.css">
</head>
<body class="app">

<div class="app__contenedor">

    <!-- =============================================================
         MENU LATERAL
         ============================================================= -->
    <aside class="app__lateral" id="menuLateral">

        <div class="marca">
            <div class="marca__icono" aria-hidden="true">GD</div>
            <div class="marca__texto">
                <strong class="marca__nombre">Gestor Documental</strong>
                <span class="marca__version">v<?= Helper::e(VERSION_SISTEMA) ?></span>
            </div>
        </div>

        <nav class="menu" aria-label="Menu principal">

            <p class="menu__titulo">General</p>
            <a class="<?= $activo('panel') ?>" href="<?= Helper::url('panel') ?>">
                <span class="menu__icono" aria-hidden="true">&#9632;</span> Panel de control
            </a>
            <a class="<?= $activo('documentos') ?>" href="<?= Helper::url('documentos') ?>">
                <span class="menu__icono" aria-hidden="true">&#128196;</span> Documentos
            </a>
            <a class="<?= $activo('panel') === 'menu__enlace menu__enlace--activo' && $accionActual === 'buscar' ? 'menu__enlace menu__enlace--activo' : 'menu__enlace' ?>"
               href="<?= Helper::url('panel', 'buscar') ?>">
                <span class="menu__icono" aria-hidden="true">&#128269;</span> Consulta documental
            </a>

            <?php if (Sesion::puede('documentos', 'crear')): ?>
                <a class="<?= $activo('documentos') ?>"
                   href="<?= Helper::url('documentos', 'crear') ?>"
                   style="display:none"></a>
            <?php endif; ?>

            <?php if (Sesion::puede('categorias', 'ver')): ?>
                <p class="menu__titulo">Clasificacion</p>
                <a class="<?= $activo('categorias') ?>" href="<?= Helper::url('categorias') ?>">
                    <span class="menu__icono" aria-hidden="true">&#128193;</span> Categorias
                </a>
                <a class="<?= $activo('casos') ?>" href="<?= Helper::url('casos') ?>">
                    <span class="menu__icono" aria-hidden="true">&#128210;</span> Casos
                </a>
            <?php endif; ?>

            <?php if (Sesion::puede('usuarios', 'ver') || $nivelActual >= 3): ?>
                <p class="menu__titulo">Seguridad</p>
            <?php endif; ?>

            <?php if (Sesion::puede('usuarios', 'ver')): ?>
                <a class="<?= $activo('usuarios') ?>" href="<?= Helper::url('usuarios') ?>">
                    <span class="menu__icono" aria-hidden="true">&#128100;</span> Usuarios
                </a>
            <?php endif; ?>

            <?php if ($nivelActual >= 3): ?>
                <a class="<?= $activo('roles') ?>" href="<?= Helper::url('roles') ?>">
                    <span class="menu__icono" aria-hidden="true">&#128273;</span> Roles y permisos
                </a>
                <a class="<?= $activo('areas') ?>" href="<?= Helper::url('areas') ?>">
                    <span class="menu__icono" aria-hidden="true">&#127970;</span> Areas
                </a>
            <?php endif; ?>

            <?php if (Sesion::puede('auditoria', 'ver')): ?>
                <p class="menu__titulo">Control</p>
                <a class="<?= $activo('auditoria') ?>" href="<?= Helper::url('auditoria') ?>">
                    <span class="menu__icono" aria-hidden="true">&#128220;</span> Auditoria
                </a>
            <?php endif; ?>

            <?php if (Sesion::puede('reportes', 'ver')): ?>
                <a class="<?= $activo('reportes') ?>" href="<?= Helper::url('reportes') ?>">
                    <span class="menu__icono" aria-hidden="true">&#128202;</span> Reportes
                </a>
            <?php endif; ?>

            <?php if (Sesion::puede('respaldo', 'ver')): ?>
                <a class="<?= $activo('respaldos') ?>" href="<?= Helper::url('respaldos') ?>">
                    <span class="menu__icono" aria-hidden="true">&#128190;</span> Respaldos
                </a>
            <?php endif; ?>

            <?php if ($nivelActual >= 3): ?>
                <a class="<?= $activo('configuracion') ?>" href="<?= Helper::url('configuracion') ?>">
                    <span class="menu__icono" aria-hidden="true">&#9881;</span> Configuracion
                </a>
            <?php endif; ?>
        </nav>

        <div class="app__lateral-pie">
            <span class="texto-mini"><?= Helper::e(mb_strimwidth(NOMBRE_SISTEMA, 0, 42, '...')) ?></span>
        </div>
    </aside>

    <!-- =============================================================
         AREA PRINCIPAL
         ============================================================= -->
    <div class="app__principal">

        <header class="cabecera">
            <button type="button" class="cabecera__boton-menu" id="botonMenu"
                    aria-label="Mostrar u ocultar el menu">&#9776;</button>

            <div class="cabecera__titulo">
                <h1 class="cabecera__titulo-texto"><?= Helper::e(Helper::tituloModulo($moduloActual)) ?></h1>
                <?php if (!empty($tituloPagina)): ?>
                    <span class="cabecera__subtitulo"><?= Helper::e($tituloPagina) ?></span>
                <?php endif; ?>
            </div>

            <div class="cabecera__acciones">

                <a class="cabecera__notif" href="<?= Helper::url('panel', 'notificaciones') ?>"
                   title="<?= $notifPendientes ?> notificacion(es) sin leer">
                    <span aria-hidden="true">&#128276;</span>
                    <?php if ($notifPendientes > 0): ?>
                        <span class="cabecera__notif-badge"><?= (int) $notifPendientes ?></span>
                    <?php endif; ?>
                </a>

                <div class="perfil-mini">
                    <div class="perfil-mini__avatar" aria-hidden="true">
                        <?= Helper::e(mb_strtoupper(mb_substr((string) Sesion::dato('nombre_completo', 'U'), 0, 1))) ?>
                    </div>
                    <div class="perfil-mini__datos">
                        <strong class="perfil-mini__nombre">
                            <?= Helper::e(mb_strimwidth((string) Sesion::dato('nombre_completo'), 0, 26, '...')) ?>
                        </strong>
                        <span class="perfil-mini__rol"><?= Helper::e((string) Sesion::dato('rol')) ?></span>
                    </div>
                </div>

                <a class="boton boton--fantasma boton--mini"
                   href="<?= Helper::url('panel', 'perfil') ?>">Mi perfil</a>

                <a class="boton boton--peligro boton--mini"
                   href="<?= Helper::url('auth', 'logout') ?>">Salir</a>
            </div>
        </header>

        <main class="contenido" id="contenido">

            <?php
            // Mensajes flash
            foreach (Sesion::obtenerFlash() as $mensaje):
                $claseTipo = match ($mensaje['tipo']) {
                    'exito'       => 'alerta--exito',
                    'error'       => 'alerta--error',
                    'advertencia' => 'alerta--aviso',
                    default       => 'alerta--info',
                };
            ?>
                <div class="alerta <?= $claseTipo ?>" role="alert">
                    <span class="alerta__texto"><?= Helper::e($mensaje['texto']) ?></span>
                    <button type="button" class="alerta__cerrar" aria-label="Cerrar">&times;</button>
                </div>
            <?php endforeach; ?>
