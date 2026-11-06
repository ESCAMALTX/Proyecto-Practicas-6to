<?php
/**
 * Vista: app/views/usuarios/crear.php
 * Creacion de usuarios (RF-002).
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Nuevo usuario</h2>
        <p class="encabezado-seccion__meta">
            El rol determina los permisos. El area restringe el acceso a los
            documentos de esa dependencia.
        </p>
    </div>
    <a class="boton boton--fantasma" href="<?= Helper::url('usuarios') ?>">Cancelar</a>
</section>

<?php if (!empty($errores)): ?>
    <div class="alerta alerta--error" role="alert">
        <strong class="alerta__titulo">Revise los siguientes campos:</strong>
        <ul class="alerta__lista">
            <?php foreach ($errores as $error): ?>
                <li><?= Helper::e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= Helper::url('usuarios', 'crear') ?>"
      class="formulario formulario--tarjeta" novalidate>
    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">

    <div class="rejilla rejilla--2">

        <section class="tarjeta">
            <div class="tarjeta__cabecera">
                <h3 class="tarjeta__titulo">Datos personales</h3>
            </div>

            <div class="tarjeta__cuerpo">
                <div class="campo">
                    <label class="campo__etiqueta" for="nombre_completo">
                        Nombre completo <span class="campo__obligatorio">*</span>
                    </label>
                    <input type="text" class="campo__control" id="nombre_completo"
                           name="nombre_completo" maxlength="150"
                           value="<?= Helper::e($datos['nombre_completo'] ?? '') ?>"
                           required>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="documento">
                        Documento <span class="campo__obligatorio">*</span>
                    </label>
                    <input type="text" class="campo__control" id="documento"
                           name="documento" maxlength="30"
                           value="<?= Helper::e($datos['documento'] ?? '') ?>" required>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="correo">
                        Correo electronico <span class="campo__obligatorio">*</span>
                    </label>
                    <input type="email" class="campo__control" id="correo"
                           name="correo" maxlength="150"
                           value="<?= Helper::e($datos['correo'] ?? '') ?>" required>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="telefono">Telefono</label>
                    <input type="text" class="campo__control" id="telefono"
                           name="telefono" maxlength="30"
                           value="<?= Helper::e($datos['telefono'] ?? '') ?>">
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="cargo">Cargo</label>
                    <input type="text" class="campo__control" id="cargo"
                           name="cargo" maxlength="100"
                           value="<?= Helper::e($datos['cargo'] ?? '') ?>">
                </div>
            </div>
        </section>

        <div class="columna">
            <section class="tarjeta">
                <div class="tarjeta__cabecera">
                    <h3 class="tarjeta__titulo">Acceso y permisos</h3>
                </div>

                <div class="tarjeta__cuerpo">
                    <div class="campo">
                        <label class="campo__etiqueta" for="usuario">
                            Nombre de usuario <span class="campo__obligatorio">*</span>
                        </label>
                        <input type="text" class="campo__control" id="usuario"
                               name="usuario" maxlength="50"
                               value="<?= Helper::e($datos['usuario'] ?? '') ?>"
                               placeholder="Sin espacios ni caracteres especiales" required>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="id_rol">
                            Rol <span class="campo__obligatorio">*</span>
                        </label>
                        <select class="campo__control" id="id_rol" name="id_rol" required>
                            <option value="">Seleccione un rol...</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= (int) $rol['id_rol'] ?>"
                                    <?= (int) ($datos['id_rol'] ?? 0) === (int) $rol['id_rol'] ? 'selected' : '' ?>>
                                    <?= Helper::e($rol['nombre']) ?> — nivel <?= (int) $rol['nivel_acceso'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="campo__ayuda">
                            Nivel 1 consulta · Nivel 2 gestiona documentos · Nivel 3 administra el sistema.
                        </span>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="id_area">Area</label>
                        <select class="campo__control" id="id_area" name="id_area">
                            <option value="">Sin area asignada</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= (int) $area['id_area'] ?>"
                                    <?= (int) ($datos['id_area'] ?? 0) === (int) $area['id_area'] ? 'selected' : '' ?>>
                                    <?= Helper::e($area['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="estado">Estado inicial</label>
                        <select class="campo__control" id="estado" name="estado">
                            <option value="ACTIVO">Activo — puede ingresar de inmediato</option>
                            <option value="INACTIVO"
                                <?= ($datos['estado'] ?? '') === 'INACTIVO' ? 'selected' : '' ?>>
                                Inactivo — cuenta creada sin acceso
                            </option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="tarjeta">
                <div class="tarjeta__cabecera">
                    <h3 class="tarjeta__titulo">Contrasena</h3>
                    <p class="tarjeta__subtitulo">
                        El usuario debera cambiarla en su primer ingreso.
                    </p>
                </div>

                <div class="tarjeta__cuerpo">
                    <div class="campo">
                        <label class="campo__etiqueta" for="clave">
                            Contrasena <span class="campo__obligatorio">*</span>
                        </label>
                        <div class="campo__grupo">
                            <input type="password" class="campo__control" id="clave"
                                   name="clave" autocomplete="new-password" required>
                            <button type="button" class="campo__ver" data-ver-clave="clave">Ver</button>
                        </div>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="clave2">
                            Confirmar contrasena <span class="campo__obligatorio">*</span>
                        </label>
                        <div class="campo__grupo">
                            <input type="password" class="campo__control" id="clave2"
                                   name="clave2" autocomplete="new-password" required>
                            <button type="button" class="campo__ver" data-ver-clave="clave2">Ver</button>
                        </div>
                    </div>

                    <div class="campo__medidor" id="medidorClave" data-medidor="clave">
                        <div class="campo__medidor-barra"><span></span></div>
                        <span class="campo__medidor-texto">Fortaleza de la contrasena</span>
                    </div>

                    <button type="button" class="boton boton--fantasma boton--bloque"
                            data-generar-clave="clave">
                        Generar contrasena segura
                    </button>

                    <p class="campo__ayuda">
                        Minimo 8 caracteres con mayuscula, minuscula, numero y simbolo.
                    </p>
                </div>
            </section>

            <div class="formulario__acciones formulario__acciones--destacadas">
                <button type="submit" class="boton boton--primario boton--grande">
                    Crear usuario
                </button>
                <a class="boton boton--fantasma boton--grande"
                   href="<?= Helper::url('usuarios') ?>">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
