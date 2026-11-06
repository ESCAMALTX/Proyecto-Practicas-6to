<?php
/**
 * Vista: app/views/auth/cambiar_clave.php
 * Cambio de contrasena del propio usuario.
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<div class="rejilla rejilla--2">

    <div class="tarjeta">
        <div class="tarjeta__cabecera">
            <h2 class="tarjeta__titulo">Cambiar contrasena</h2>
            <p class="tarjeta__subtitulo">
                Por seguridad, la contrasena debe cumplir la politica del sistema.
            </p>
        </div>

        <div class="tarjeta__cuerpo">

            <?php if (!empty($errores['general'])): ?>
                <div class="alerta alerta--error" role="alert">
                    <span class="alerta__texto"><?= Helper::e($errores['general']) ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= Helper::url('auth', 'cambiar-clave') ?>"
                  class="formulario" novalidate>
                <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">

                <div class="campo">
                    <label class="campo__etiqueta" for="clave_actual">Contrasena actual</label>
                    <div class="campo__grupo">
                        <input type="password"
                               class="campo__control <?= isset($errores['clave_actual']) ? 'campo__control--error' : '' ?>"
                               id="clave_actual" name="clave_actual"
                               autocomplete="current-password" required autofocus>
                        <button type="button" class="campo__ver" data-ver-clave="clave_actual">Ver</button>
                    </div>
                    <?php if (isset($errores['clave_actual'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['clave_actual']) ?></span>
                    <?php endif; ?>
                </div>

                <hr class="separador">

                <div class="campo">
                    <label class="campo__etiqueta" for="clave_nueva">Contrasena nueva</label>
                    <div class="campo__grupo">
                        <input type="password"
                               class="campo__control <?= isset($errores['clave_nueva']) ? 'campo__control--error' : '' ?>"
                               id="clave_nueva" name="clave_nueva"
                               autocomplete="new-password" required>
                        <button type="button" class="campo__ver" data-ver-clave="clave_nueva">Ver</button>
                    </div>
                    <?php if (isset($errores['clave_nueva'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['clave_nueva']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="clave_nueva2">Confirmar contrasena nueva</label>
                    <div class="campo__grupo">
                        <input type="password"
                               class="campo__control <?= isset($errores['clave_nueva2']) ? 'campo__control--error' : '' ?>"
                               id="clave_nueva2" name="clave_nueva2"
                               autocomplete="new-password" required>
                        <button type="button" class="campo__ver" data-ver-clave="clave_nueva2">Ver</button>
                    </div>
                    <?php if (isset($errores['clave_nueva2'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['clave_nueva2']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="campo__medidor" id="medidorClave" data-medidor="clave_nueva">
                    <div class="campo__medidor-barra"><span></span></div>
                    <span class="campo__medidor-texto">Fortaleza de la contrasena</span>
                </div>

                <div class="formulario__acciones">
                    <button type="submit" class="boton boton--primario">
                        Actualizar contrasena
                    </button>
                    <a class="boton boton--fantasma" href="<?= Helper::url('panel', 'perfil') ?>">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="tarjeta">
        <div class="tarjeta__cabecera">
            <h2 class="tarjeta__titulo">Politica de contrasenas</h2>
        </div>
        <div class="tarjeta__cuerpo">
            <ul class="lista-requisitos">
                <li>Longitud minima de 8 caracteres.</li>
                <li>Al menos una letra mayuscula.</li>
                <li>Al menos una letra minuscula.</li>
                <li>Al menos un digito numerico.</li>
                <li>Al menos un caracter especial (! @ # $ % * etc.).</li>
                <li>Debe ser distinta a la contrasena actual.</li>
            </ul>

            <div class="aviso aviso--info">
                <strong class="aviso__titulo">Recomendaciones</strong>
                <p class="aviso__texto">
                    No comparta su contrasena ni la reutilice en otros servicios.
                    Si sospecha que alguien la conoce, cambiela de inmediato y
                    revise la bitacora de auditoria de su cuenta.
                </p>
            </div>

            <div class="aviso aviso--advertencia">
                <strong class="aviso__titulo">Sesiones y contrasenas temporales</strong>
                <p class="aviso__texto">
                    Cuando un administrador restablece su contrasena, el sistema
                    le asigna una temporal y le solicita cambiarla al ingresar.
                    <?= (int) Sesion::dato('debe_cambiar_clave') === 1
                        ? 'Su cuenta tiene una contrasena temporal pendiente de cambio.'
                        : '' ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
