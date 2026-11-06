<?php
/**
 * Vista: app/views/usuarios/editar.php
 * Edicion de usuarios (RF-002).
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Editar usuario</h2>
        <p class="encabezado-seccion__meta">
            <span class="texto-mono"><?= Helper::e($usuario['usuario']) ?></span>
            · <?= Helper::e($usuario['rol']) ?>
            · Creado el <?= Helper::e(Helper::fechaHora($usuario['fecha_creacion'])) ?>
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

<?php if ((int) $usuario['id_usuario'] === (int) Sesion::dato('id_usuario')): ?>
    <div class="aviso aviso--info">
        <strong class="aviso__titulo">Esta editando su propia cuenta</strong>
        <p class="aviso__texto">
            Por seguridad no puede cambiar su propio rol ni desactivar su cuenta.
            Use la opcion "Mi perfil" para cambiar su contrasena.
        </p>
    </div>
<?php endif; ?>

<form method="post" action="<?= Helper::url('usuarios', 'editar', ['id' => $usuario['id_usuario']]) ?>"
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
                           value="<?= Helper::e($datos['nombre_completo'] ?? $usuario['nombre_completo']) ?>"
                           required>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="documento">
                        Documento <span class="campo__obligatorio">*</span>
                    </label>
                    <input type="text" class="campo__control" id="documento"
                           name="documento" maxlength="30"
                           value="<?= Helper::e($datos['documento'] ?? $usuario['documento']) ?>" required>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="correo">
                        Correo electronico <span class="campo__obligatorio">*</span>
                    </label>
                    <input type="email" class="campo__control" id="correo"
                           name="correo" maxlength="150"
                           value="<?= Helper::e($datos['correo'] ?? $usuario['correo']) ?>" required>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="telefono">Telefono</label>
                    <input type="text" class="campo__control" id="telefono"
                           name="telefono" maxlength="30"
                           value="<?= Helper::e($datos['telefono'] ?? $usuario['telefono']) ?>">
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="cargo">Cargo</label>
                    <input type="text" class="campo__control" id="cargo"
                           name="cargo" maxlength="100"
                           value="<?= Helper::e($datos['cargo'] ?? $usuario['cargo']) ?>">
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
                               value="<?= Helper::e($datos['usuario'] ?? $usuario['usuario']) ?>" required>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="id_rol">
                            Rol <span class="campo__obligatorio">*</span>
                        </label>
                        <select class="campo__control" id="id_rol" name="id_rol"
                                <?= (int) $usuario['id_usuario'] === (int) Sesion::dato('id_usuario') ? 'disabled' : '' ?>
                                required>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= (int) $rol['id_rol'] ?>"
                                    <?= (int) ($datos['id_rol'] ?? $usuario['id_rol']) === (int) $rol['id_rol'] ? 'selected' : '' ?>>
                                    <?= Helper::e($rol['nombre']) ?> — nivel <?= (int) $rol['nivel_acceso'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if ((int) $usuario['id_usuario'] === (int) Sesion::dato('id_usuario')): ?>
                            <!-- Se envia el rol actual para que el POST no llegue vacio -->
                            <input type="hidden" name="id_rol" value="<?= (int) $usuario['id_rol'] ?>">
                            <span class="campo__ayuda">No puede modificar su propio rol.</span>
                        <?php endif; ?>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="id_area">Area</label>
                        <select class="campo__control" id="id_area" name="id_area">
                            <option value="">Sin area asignada</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= (int) $area['id_area'] ?>"
                                    <?= (int) ($datos['id_area'] ?? $usuario['id_area']) === (int) $area['id_area'] ? 'selected' : '' ?>>
                                    <?= Helper::e($area['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="estado">Estado de la cuenta</label>
                        <select class="campo__control" id="estado" name="estado"
                                <?= (int) $usuario['id_usuario'] === (int) Sesion::dato('id_usuario') ? 'disabled' : '' ?>>
                            <option value="ACTIVO"
                                <?= ($datos['estado'] ?? $usuario['estado']) === 'ACTIVO' ? 'selected' : '' ?>>
                                Activo — puede ingresar
                            </option>
                            <option value="INACTIVO"
                                <?= ($datos['estado'] ?? $usuario['estado']) === 'INACTIVO' ? 'selected' : '' ?>>
                                Inactivo — sin acceso
                            </option>
                            <option value="BLOQUEADO"
                                <?= ($datos['estado'] ?? $usuario['estado']) === 'BLOQUEADO' ? 'selected' : '' ?>>
                                Bloqueado — requiere desbloqueo
                            </option>
                        </select>

                        <?php if ((int) $usuario['id_usuario'] === (int) Sesion::dato('id_usuario')): ?>
                            <input type="hidden" name="estado" value="<?= Helper::e($usuario['estado']) ?>">
                            <span class="campo__ayuda">No puede desactivar su propia cuenta.</span>
                        <?php endif; ?>
                    </div>

                    <dl class="lista-datos lista-datos--compacta">
                        <div class="lista-datos__fila">
                            <dt>Intentos fallidos</dt>
                            <dd><?= (int) $usuario['intentos_fallidos'] ?> de <?= (int) MAX_INTENTOS_LOGIN ?></dd>
                        </div>
                        <div class="lista-datos__fila">
                            <dt>Ultimo acceso</dt>
                            <dd><?= Helper::e(Helper::fechaHora($usuario['ultimo_acceso'])) ?></dd>
                        </div>
                        <div class="lista-datos__fila">
                            <dt>Contrasena temporal pendiente</dt>
                            <dd><?= (int) $usuario['debe_cambiar_clave'] === 1 ? 'Si' : 'No' ?></dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="tarjeta">
                <div class="tarjeta__cabecera">
                    <h3 class="tarjeta__titulo">Restablecer contrasena</h3>
                    <p class="tarjeta__subtitulo">
                        Se asigna una contrasena temporal que el usuario debera
                        cambiar en su proximo ingreso.
                    </p>
                </div>

                <div class="tarjeta__cuerpo">
                    <form method="post"
                          action="<?= Helper::url('usuarios', 'restablecer-clave') ?>"
                          class="formulario"
                          data-confirmar="¿Confirma que desea restablecer la contrasena de <?= Helper::e($usuario['nombre_completo']) ?>?">
                        <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= (int) $usuario['id_usuario'] ?>">

                        <div class="campo">
                            <label class="campo__etiqueta" for="nueva_clave">
                                Contrasena temporal
                            </label>
                            <div class="campo__grupo">
                                <input type="text" class="campo__control" id="nueva_clave"
                                       name="nueva_clave" value="Temporal123*">
                                <button type="button" class="campo__ver"
                                        data-generar-clave="nueva_clave">Generar</button>
                            </div>
                            <span class="campo__ayuda">
                                Debe cumplir la politica de contrasenas del sistema.
                            </span>
                        </div>

                        <button type="submit" class="boton boton--aviso boton--bloque">
                            Restablecer contrasena
                        </button>
                    </form>
                </div>
            </section>

            <div class="formulario__acciones formulario__acciones--destacadas">
                <button type="submit" class="boton boton--primario boton--grande">
                    Guardar cambios
                </button>
                <a class="boton boton--fantasma boton--grande"
                   href="<?= Helper::url('usuarios') ?>">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
