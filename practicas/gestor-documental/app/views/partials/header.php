<?php
/**
 * =====================================================================
 * Archivo: app/views/partials/header.php
 * Descripcion: Cabecera global con estilo ISOTools Gestor Documental.
 * =====================================================================
 */

$usuarioActual = Sesion::usuario();
$nivelActual   = Sesion::nivel();
$moduloActual  = Helper::get('modulo', 'documentos');
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
    <title><?= Helper::e($tituloPagina ?? 'Gestor Documental') ?> · ISOTools</title>

    <!-- Hojas de estilo en orden de cascada -->
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/formularios.css">
    <link rel="stylesheet" href="assets/css/componentes.css">
    <link rel="stylesheet" href="assets/css/avisos.css">
    <link rel="stylesheet" href="assets/css/impresion.css">
    <link rel="stylesheet" href="assets/css/isotools.css">
</head>
<body class="app">

<!-- =============================================================
     BARRA SUPERIOR GLOBAL ISOTOOLS
     ============================================================= -->
<header class="isotools-topbar">
    <div class="isotools-topbar__izq">
        <button type="button" class="isotools-btn-menu" id="botonMenu" aria-label="Abrir aplicaciones">&#9776;</button>
        <a href="<?= Helper::url('documentos') ?>" class="isotools-logo">
            <span>ISO</span>tools
        </a>
        <form action="index.php" method="get" class="isotools-search-form">
            <input type="hidden" name="modulo" value="documentos">
            <input type="search" name="q" class="isotools-search-input"
                   placeholder="Busca una aplicación"
                   value="<?= Helper::e(Helper::get('q', '')) ?>">
            <button type="submit" class="isotools-search-btn" aria-label="Buscar">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </form>
    </div>

    <div class="isotools-topbar__der">
        <button type="button" class="isotools-icon-action" title="Ayuda" onclick="alert('ISOTools Gestor Documental v1.0')">
            <span class="isotools-help-circle">?</span>
        </button>

        <a class="isotools-icon-action" href="<?= Helper::url('panel', 'notificaciones') ?>"
           title="<?= $notifPendientes ?> notificaciones">
            <span style="font-size: 16px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg></span>
            <?php if ($notifPendientes > 0): ?>
                <span class="isotools-bell-badge"><?= $notifPendientes ?></span>
            <?php endif; ?>
        </a>

        <div class="isotools-user-menu">
            <div class="isotools-avatar" title="<?= Helper::e((string) Sesion::dato('nombre_completo')) ?>">
                <span><?= Helper::e(mb_strtoupper(mb_substr((string) Sesion::dato('nombre_completo', 'U'), 0, 1))) ?></span>
            </div>
            <a class="isotools-link-salir" href="<?= Helper::url('auth', 'logout') ?>" title="Cerrar sesión">
                Salir
            </a>
        </div>
    </div>
</header>

<!-- =============================================================
     SUBHEADER: GESTOR DOCUMENTAL
     ============================================================= -->
<div class="isotools-subheader">
    <span class="isotools-folder-badge" aria-hidden="true"></span>
    <h1 class="isotools-subheader__title">Gestor Documental</h1>
</div>

<div class="app__contenedor">

    <!-- =============================================================
         MENU LATERAL (DRAWER DE MODULOS DEL SISTEMA)
         ============================================================= -->
    <aside class="app__lateral" id="menuLateral">

        <div class="marca">
            <div class="marca__icono" aria-hidden="true">ISO</div>
            <div class="marca__texto">
                <strong class="marca__nombre">ISOTools</strong>
                <span class="marca__version">Módulos del Sistema</span>
            </div>
        </div>

        <nav class="menu" aria-label="Menu principal">
            <p class="menu__titulo">Navegación</p>
            <a class="<?= $activo('documentos') ?>" href="<?= Helper::url('documentos') ?>">
                <span class="menu__icono" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg></span> Gestor Documental
            </a>
            <a class="<?= $activo('panel') ?>" href="<?= Helper::url('panel') ?>">
                <span class="menu__icono" aria-hidden="true"><span style="display:inline-block;width:8px;height:8px;background:currentColor;border-radius:1px;"></span></span> Indicadores Panel
            </a>
            <a class="<?= $activo('panel') === 'menu__enlace menu__enlace--activo' && $accionActual === 'buscar' ? 'menu__enlace menu__enlace--activo' : 'menu__enlace' ?>"
               href="<?= Helper::url('panel', 'buscar') ?>">
                <span class="menu__icono" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span> Búsqueda Avanzada
            </a>

            <?php if (Sesion::puede('categorias', 'ver')): ?>
                <p class="menu__titulo">Estructura</p>
                <a class="<?= $activo('categorias') ?>" href="<?= Helper::url('categorias') ?>">
                    <span class="menu__icono" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg></span> Categorías
                </a>
                <a class="<?= $activo('casos') ?>" href="<?= Helper::url('casos') ?>">
                    <span class="menu__icono" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg></span> Casos
                </a>
            <?php endif; ?>

            <?php if (Sesion::puede('usuarios', 'ver')): ?>
                <p class="menu__titulo">Administración</p>
                <a class="<?= $activo('usuarios') ?>" href="<?= Helper::url('usuarios') ?>">
                    <span class="menu__icono" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span> Usuarios
                </a>
            <?php endif; ?>

            <?php if ($nivelActual >= 3): ?>
                <a class="<?= $activo('roles') ?>" href="<?= Helper::url('roles') ?>">
                    <span class="menu__icono" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-1.5 1.5L16 7l-2 2-2-2-2 2 2 2-4 4a5 5 0 1 1-3-3l4-4 2 2 2-2-2-2 1.5-1.5L19 2z"></path></svg></span> Roles y Permisos
                </a>
                <a class="<?= $activo('areas') ?>" href="<?= Helper::url('areas') ?>">
                    <span class="menu__icono" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="22" x2="9" y2="2"></line></svg></span> Áreas
                </a>
            <?php endif; ?>

            <?php if (Sesion::puede('auditoria', 'ver')): ?>
                <p class="menu__titulo">Trazabilidad</p>
                <a class="<?= $activo('auditoria') ?>" href="<?= Helper::url('auditoria') ?>">
                    <span class="menu__icono" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span> Auditoría
                </a>
            <?php endif; ?>

            <?php if (Sesion::puede('reportes', 'ver')): ?>
                <a class="<?= $activo('reportes') ?>" href="<?= Helper::url('reportes') ?>">
                    <span class="menu__icono" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg></span> Reportes
                </a>
            <?php endif; ?>
        </nav>

        <div class="app__lateral-pie">
            <span class="texto-mini"><?= Helper::e(mb_strimwidth(NOMBRE_SISTEMA, 0, 38, '...')) ?></span>
        </div>
    </aside>

    <!-- =============================================================
         AREA PRINCIPAL
         ============================================================= -->
    <div class="app__principal">
        <main class="contenido-iso" id="contenido">
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
                <div class="alerta <?= $claseTipo ?>" role="alert" style="margin: 12px 24px 0 24px;">
                    <span class="alerta__texto"><?= Helper::e($mensaje['texto']) ?></span>
                    <button type="button" class="alerta__cerrar" aria-label="Cerrar">&times;</button>
                </div>
            <?php endforeach; ?>
