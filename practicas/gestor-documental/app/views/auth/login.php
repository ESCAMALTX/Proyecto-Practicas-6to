<?php
/**
 * Vista: app/views/auth/login.php
 * Formulario de inicio de sesion (RF-001).
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Iniciar sesion · <?= Helper::e(NOMBRE_SISTEMA) ?></title>

    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/formularios.css">
    <link rel="stylesheet" href="assets/css/componentes.css">
    <link rel="stylesheet" href="assets/css/avisos.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/impresion.css">
</head>
<body class="auth">

<div class="auth__contenedor">

    <!-- =============================================================
         PANEL INFORMATIVO
         ============================================================= -->
    <section class="auth__lateral">
        <div class="auth__marca">
            <div class="marca__icono marca__icono--grande" aria-hidden="true">GD</div>
            <h1 class="auth__titulo"><?= Helper::e(NOMBRE_SISTEMA) ?></h1>
            <p class="auth__descripcion">
                Plataforma interna para la gestion, clasificacion y consulta
                de documentos institucionales con control de acceso por roles.
            </p>
        </div>

        <ul class="auth__lista">
            <li>Acceso restringido a usuarios autorizados.</li>
            <li>Control de permisos por rol y area.</li>
            <li>Busqueda por palabras clave y filtros avanzados.</li>
            <li>Bitacora de auditoria de todas las acciones criticas.</li>
            <li>Versionado e integridad verificada de los archivos.</li>
        </ul>

        <p class="auth__nota">
            El acceso y todas las operaciones realizadas quedan registradas
            en la bitacora del sistema.
        </p>
    </section>

    <!-- =============================================================
         FORMULARIO
         ============================================================= -->
    <section class="auth__formulario">

        <div class="auth__encabezado">
            <h2 class="auth__subtitulo">Iniciar sesion</h2>
            <p class="auth__ayuda">Ingrese sus credenciales para continuar.</p>
        </div>

        <?php foreach (Sesion::obtenerFlash() as $mensaje): ?>
            <div class="alerta alerta--<?= $mensaje['tipo'] === 'exito' ? 'exito' : 'info' ?>">
                <span class="alerta__texto"><?= Helper::e($mensaje['texto']) ?></span>
            </div>
        <?php endforeach; ?>

        <?php if (!empty($errores['general'])): ?>
            <div class="alerta alerta--error" role="alert">
                <span class="alerta__texto"><?= Helper::e($errores['general']) ?></span>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= Helper::url('auth', 'login') ?>" class="formulario" novalidate>
            <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">

            <div class="campo">
                <label class="campo__etiqueta" for="identificador">
                    Usuario o correo electronico
                </label>
                <input
                    type="text"
                    class="campo__control <?= isset($errores['identificador']) ? 'campo__control--error' : '' ?>"
                    id="identificador"
                    name="identificador"
                    value="<?= Helper::e($identificador ?? '') ?>"
                    placeholder="ejemplo: admin"
                    autocomplete="username"
                    autofocus
                    required>
                <?php if (isset($errores['identificador'])): ?>
                    <span class="campo__error"><?= Helper::e($errores['identificador']) ?></span>
                <?php endif; ?>
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="clave">Contrasena</label>
                <div class="campo__grupo">
                    <input
                        type="password"
                        class="campo__control <?= isset($errores['clave']) ? 'campo__control--error' : '' ?>"
                        id="clave"
                        name="clave"
                        placeholder="Ingrese su contrasena"
                        autocomplete="current-password"
                        required>
                    <button type="button" class="campo__ver" data-ver-clave="clave"
                            aria-label="Mostrar u ocultar la contrasena">Ver</button>
                </div>
                <?php if (isset($errores['clave'])): ?>
                    <span class="campo__error"><?= Helper::e($errores['clave']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="boton boton--primario boton--bloque">
                Ingresar al sistema
            </button>
        </form>

        <div class="auth__separador"><span>o</span></div>

        <a class="boton boton--secundario boton--bloque"
           href="<?= Helper::url('auth', 'registro') ?>">
            Crear una cuenta nueva
        </a>

        <!-- Credenciales de demostracion -->
        <details class="auth__demo">
            <summary>Credenciales de demostracion</summary>
            <table class="tabla tabla--compacta">
                <thead>
                    <tr><th>Rol</th><th>Usuario</th><th>Contrasena</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Administrador</td>
                        <td><code>admin</code></td>
                        <td><code>Admin123*</code></td>
                    </tr>
                    <tr>
                        <td>Responsable documental</td>
                        <td><code>abogado</code></td>
                        <td><code>Abogado123*</code></td>
                    </tr>
                    <tr>
                        <td>Asesor</td>
                        <td><code>asesor</code></td>
                        <td><code>Asesor123*</code></td>
                    </tr>
                </tbody>
            </table>
            <p class="texto-mini">
                Cambie estas contrasenas antes de poner el sistema en produccion.
            </p>
        </details>

        <p class="auth__pie">
            <?= Helper::e(NOMBRE_SISTEMA) ?> · v<?= Helper::e(VERSION_SISTEMA) ?>
        </p>
    </section>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
