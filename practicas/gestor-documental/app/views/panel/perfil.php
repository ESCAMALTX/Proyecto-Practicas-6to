<?php
/**
 * Vista: app/views/panel/perfil.php
 * Perfil del usuario en sesion: datos, sesiones activas y actividad.
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<div class="rejilla rejilla--2">

    <section class="tarjeta">
        <div class="tarjeta__cabecera tarjeta__cabecera--fila">
            <h2 class="tarjeta__titulo">Datos de mi cuenta</h2>
            <a class="boton boton--secundario boton--mini"
               href="<?= Helper::url('auth', 'cambiar-clave') ?>">Cambiar contrasena</a>
        </div>
        <div class="tarjeta__cuerpo">
            <div class="perfil">
                <div class="perfil__avatar" aria-hidden="true">
                    <?= Helper::e(mb_strtoupper(mb_substr((string) $usuario['nombre_completo'], 0, 1))) ?>
                </div>
                <div class="perfil__identidad">
                    <h3 class="perfil__nombre"><?= Helper::e($usuario['nombre_completo']) ?></h3>
                    <span class="perfil__rol"><?= Helper::e($usuario['rol']) ?></span>
                </div>
            </div>

            <dl class="lista-datos">
                <div class="lista-datos__fila">
                    <dt>Documento</dt>
                    <dd><?= Helper::e($usuario['documento']) ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Correo electronico</dt>
                    <dd><?= Helper::e($usuario['correo']) ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Nombre de usuario</dt>
                    <dd><code class="codigo"><?= Helper::e($usuario['usuario']) ?></code></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Area</dt>
                    <dd><?= Helper::eo($usuario['area'], 'Sin area asignada') ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Cargo</dt>
                    <dd><?= Helper::eo($usuario['cargo'], 'No registrado') ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Telefono</dt>
                    <dd><?= Helper::eo($usuario['telefono'], 'No registrado') ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Nivel de acceso</dt>
                    <dd>
                        <span class="badge badge--neutro">
                            <?= (int) $usuario['nivel_acceso'] ?> —
                            <?= (int) $usuario['nivel_acceso'] >= 3 ? 'Administracion'
                                : ((int) $usuario['nivel_acceso'] === 2 ? 'Gestion' : 'Consulta') ?>
                        </span>
                    </dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Estado de la cuenta</dt>
                    <dd><span class="<?= Helper::claseEstado($usuario['estado']) ?>">
                        <?= Helper::e($usuario['estado']) ?>
                    </span></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Ultimo acceso</dt>
                    <dd><?= Helper::e(Helper::fechaHora($usuario['ultimo_acceso'])) ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Cuenta creada</dt>
                    <dd><?= Helper::e(Helper::fechaHora($usuario['fecha_creacion'])) ?></dd>
                </div>
            </dl>

            <?php if ((int) $usuario['debe_cambiar_clave'] === 1): ?>
                <div class="aviso aviso--advertencia">
                    <strong class="aviso__titulo">Contrasena temporal</strong>
                    <p class="aviso__texto">
                        Su contrasena actual fue asignada por el administrador.
                        Cambiela ahora para proteger su cuenta.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="columna">
        <section class="tarjeta">
            <div class="tarjeta__cabecera">
                <h2 class="tarjeta__titulo">Resumen de mi actividad</h2>
            </div>
            <div class="tarjeta__cuerpo">
                <div class="metricas metricas--compactas">
                    <article class="metrica">
                        <div class="metrica__datos">
                            <span class="metrica__valor"><?= (int) $totalConsultas ?></span>
                            <span class="metrica__etiqueta">Consultas realizadas</span>
                        </div>
                    </article>
                    <article class="metrica">
                        <div class="metrica__datos">
                            <span class="metrica__valor"><?= count($sesiones) ?></span>
                            <span class="metrica__etiqueta">Sesiones activas</span>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="tarjeta">
            <div class="tarjeta__cabecera">
                <h2 class="tarjeta__titulo">Sesiones activas</h2>
                <p class="tarjeta__subtitulo">
                    Las sesiones inactivas se cierran automaticamente a los
                    <?= (int) MINUTOS_INACTIVIDAD ?> minutos.
                </p>
            </div>
            <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
                <?php if (empty($sesiones)): ?>
                    <p class="vacio">No se registran sesiones activas.</p>
                <?php else: ?>
                    <table class="tabla tabla--compacta">
                        <thead>
                            <tr><th>Direccion IP</th><th>Inicio</th><th>Ultima actividad</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sesiones as $sesion): ?>
                                <tr>
                                    <td><code class="codigo"><?= Helper::e((string) $sesion['direccion_ip']) ?></code></td>
                                    <td class="texto-mini"><?= Helper::e(Helper::fechaHora($sesion['fecha_inicio'])) ?></td>
                                    <td class="texto-mini"><?= Helper::e(Helper::hace($sesion['ultima_actividad'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>

        <section class="tarjeta">
            <div class="tarjeta__cabecera">
                <h2 class="tarjeta__titulo">Mi actividad reciente</h2>
                <p class="tarjeta__subtitulo">
                    Registro de las acciones mas recientes de su cuenta.
                </p>
            </div>
            <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
                <?php if (empty($actividad)): ?>
                    <p class="vacio">No se registra actividad todavia.</p>
                <?php else: ?>
                    <ul class="lista-actividad">
                        <?php foreach ($actividad as $evento): ?>
                            <li class="lista-actividad__item">
                                <div class="lista-actividad__contenido">
                                    <span class="lista-actividad__texto">
                                        <?= Helper::e(mb_strimwidth((string) $evento['descripcion'], 0, 70, '...')) ?>
                                    </span>
                                    <span class="lista-actividad__meta">
                                        <?= Helper::e((string) $evento['modulo']) ?>
                                        · <?= Helper::e(Helper::hace($evento['fecha_evento'])) ?>
                                        · <?= Helper::e((string) $evento['direccion_ip']) ?>
                                    </span>
                                </div>
                                <span class="badge badge--neutro">
                                    <?= Helper::e((string) $evento['accion']) ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
