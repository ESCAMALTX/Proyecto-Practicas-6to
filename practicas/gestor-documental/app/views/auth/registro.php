<?php
/**
 * Vista: app/views/auth/registro.php
 * Registro publico de usuarios. Las cuentas creadas por esta via
 * reciben siempre el rol Asesor.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Crear cuenta · <?= Helper::e(NOMBRE_SISTEMA) ?></title>

    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/formularios.css">
    <link rel="stylesheet" href="assets/css/componentes.css">
    <link rel="stylesheet" href="assets/css/avisos.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/impresion.css">
</head>
<body class="auth">

<div class="auth__contenedor auth__contenedor--invertido">

    <section class="auth__lateral">
        <div class="auth__marca">
            <div class="marca__icono marca__icono--grande" aria-hidden="true">GD</div>
            <h1 class="auth__titulo">Crear una cuenta</h1>
            <p class="auth__descripcion">
                Las cuentas creadas desde este formulario reciben el rol
                <strong>Asesor</strong>: permiten consultar los documentos
                publicados del area asignada.
            </p>
        </div>

        <ul class="auth__lista">
            <li>Consulta de documentos publicados de su area.</li>
            <li>Busqueda por palabras clave y filtros.</li>
            <li>Historial de sus propias consultas.</li>
            <li>Sin permisos de carga, edicion ni eliminacion.</li>
        </ul>

        <p class="auth__nota">
            Para solicitar un rol con privilegios de gestion, contacte al
            administrador del sistema.
        </p>
    </section>

    <section class="auth__formulario">

        <div class="auth__encabezado">
            <h2 class="auth__subtitulo">Datos de la cuenta</h2>
            <p class="auth__ayuda">Todos los campos marcados son obligatorios.</p>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="alerta alerta--error" role="alert">
                <ul class="alerta__lista">
                    <?php foreach ($errores as $error): ?>
                        <li><?= Helper::e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= Helper::url('auth', 'registro') ?>" class="formulario" novalidate>
            <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">

            <div class="campo">
                <label class="campo__etiqueta" for="nombre_completo">Nombre completo</label>
                <input type="text" class="campo__control" id="nombre_completo"
                       name="nombre_completo" maxlength="150"
                       value="<?= Helper::e($datos['nombre_completo'] ?? '') ?>"
                       placeholder="Nombres y apellidos" required>
                <?php if (isset($errores['nombre_completo'])): ?>
                    <span class="campo__error"><?= Helper::e($errores['nombre_completo']) ?></span>
                <?php endif; ?>
            </div>

            <div class="formulario__fila">
                <div class="campo">
                    <label class="campo__etiqueta" for="documento">Documento</label>
                    <input type="text" class="campo__control" id="documento"
                           name="documento" maxlength="30"
                           value="<?= Helper::e($datos['documento'] ?? '') ?>"
                           placeholder="Numero de documento" required>
                    <?php if (isset($errores['documento'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['documento']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="telefono">Telefono</label>
                    <input type="text" class="campo__control" id="telefono"
                           name="telefono" maxlength="30"
                           value="<?= Helper::e($datos['telefono'] ?? '') ?>"
                           placeholder="Opcional">
                </div>
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="correo">Correo electronico</label>
                <input type="email" class="campo__control" id="correo"
                       name="correo" maxlength="150"
                       value="<?= Helper::e($datos['correo'] ?? '') ?>"
                       placeholder="nombre@institucion.local" required>
                <?php if (isset($errores['correo'])): ?>
                    <span class="campo__error"><?= Helper::e($errores['correo']) ?></span>
                <?php endif; ?>
            </div>

            <div class="formulario__fila">
                <div class="campo">
                    <label class="campo__etiqueta" for="usuario">Nombre de usuario</label>
                    <input type="text" class="campo__control" id="usuario"
                           name="usuario" maxlength="50"
                           value="<?= Helper::e($datos['usuario'] ?? '') ?>"
                           placeholder="Sin espacios ni caracteres especiales" required>
                    <?php if (isset($errores['usuario'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['usuario']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="id_area">Area</label>
                    <select class="campo__control" id="id_area" name="id_area" required>
                        <option value="">Seleccione un area...</option>
                        <?php foreach ($areas as $area): ?>
                            <option value="<?= (int) $area['id_area'] ?>"
                                <?= (int) ($datos['id_area'] ?? 0) === (int) $area['id_area'] ? 'selected' : '' ?>>
                                <?= Helper::e($area['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['id_area'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['id_area']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="cargo">Cargo</label>
                <input type="text" class="campo__control" id="cargo"
                       name="cargo" maxlength="100"
                       value="<?= Helper::e($datos['cargo'] ?? '') ?>"
                       placeholder="Opcional">
            </div>

            <div class="formulario__fila">
                <div class="campo">
                    <label class="campo__etiqueta" for="clave">Contrasena</label>
                    <div class="campo__grupo">
                        <input type="password" class="campo__control" id="clave"
                               name="clave" autocomplete="new-password" required>
                        <button type="button" class="campo__ver" data-ver-clave="clave"
                                aria-label="Mostrar u ocultar">Ver</button>
                    </div>
                    <?php if (isset($errores['clave'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['clave']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="clave2">Confirmar contrasena</label>
                    <div class="campo__grupo">
                        <input type="password" class="campo__control" id="clave2"
                               name="clave2" autocomplete="new-password" required>
                        <button type="button" class="campo__ver" data-ver-clave="clave2"
                                aria-label="Mostrar u ocultar">Ver</button>
                    </div>
                    <?php if (isset($errores['clave2'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['clave2']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="campo__medidor" id="medidorClave" data-medidor="clave">
                <div class="campo__medidor-barra"><span></span></div>
                <span class="campo__medidor-texto">Fortaleza de la contrasena</span>
            </div>

            <p class="campo__ayuda">
                Minimo 8 caracteres, una mayuscula, una minuscula,
                un numero y un simbolo.
            </p>

            <button type="submit" class="boton boton--primario boton--bloque">
                Crear cuenta
            </button>
        </form>

        <div class="auth__separador"><span>o</span></div>

        <a class="boton boton--secundario boton--bloque"
           href="<?= Helper::url('auth', 'login') ?>">
            Ya tengo una cuenta
        </a>
    </section>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
