<?php
/**
 * Vista: app/views/usuarios/index.php
 * Listado y gestion de usuarios (RF-002, RF-003).
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Usuarios del sistema</h2>
        <p class="encabezado-seccion__meta">
            <?= (int) $paginacion['total'] ?> usuario(s) registrado(s).
        </p>
    </div>

    <div class="encabezado-seccion__acciones">
        <a class="boton boton--secundario" href="<?= Helper::url('reportes', 'usuarios') ?>">
            Exportar CSV
        </a>
        <?php if (Sesion::puede('usuarios', 'crear')): ?>
            <a class="boton boton--primario" href="<?= Helper::url('usuarios', 'crear') ?>">
                Nuevo usuario
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- =================================================================
     RESUMEN POR ROL
     ================================================================= -->
<section class="metricas metricas--compactas">
    <?php foreach ($porRol as $fila): ?>
        <article class="metrica">
            <div class="metrica__datos">
                <span class="metrica__valor"><?= (int) $fila['total'] ?></span>
                <span class="metrica__etiqueta"><?= Helper::e($fila['nombre']) ?></span>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<!-- =================================================================
     FILTROS
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cuerpo">
        <form method="get" action="index.php" class="formulario">
            <input type="hidden" name="modulo" value="usuarios">

            <div class="formulario__fila formulario__fila--4">
                <div class="campo campo--amplio">
                    <label class="campo__etiqueta" for="q">Buscar</label>
                    <input type="search" class="campo__control" id="q" name="q"
                           value="<?= Helper::e($busqueda) ?>"
                           placeholder="Nombre, usuario, correo o documento">
                </div>

                <div class="campo" style="max-width:220px">
                    <label class="campo__etiqueta" for="rol">Rol</label>
                    <select class="campo__control" id="rol" name="rol">
                        <option value="">Todos</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= (int) $rol['id_rol'] ?>"
                                <?= $idRol === (int) $rol['id_rol'] ? 'selected' : '' ?>>
                                <?= Helper::e($rol['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo" style="max-width:200px">
                    <label class="campo__etiqueta" for="estado">Estado</label>
                    <select class="campo__control" id="estado" name="estado">
                        <option value="">Todos</option>
                        <?php foreach (['ACTIVO', 'INACTIVO', 'BLOQUEADO'] as $opcion): ?>
                            <option value="<?= $opcion ?>" <?= $estado === $opcion ? 'selected' : '' ?>>
                                <?= Helper::e($opcion) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo campo--acciones">
                    <button type="submit" class="boton boton--primario">Filtrar</button>
                    <a class="boton boton--fantasma" href="<?= Helper::url('usuarios') ?>">Limpiar</a>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- =================================================================
     TABLA
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">

        <?php if (empty($usuarios)): ?>
            <div class="estado-vacio estado-vacio--compacto">
                <div class="estado-vacio__icono" aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>
                <h3 class="estado-vacio__titulo">No hay usuarios que mostrar</h3>
                <p class="estado-vacio__texto">
                    <?= ($busqueda !== '' || $idRol > 0 || $estado !== '')
                        ? 'Ningun usuario coincide con los filtros aplicados.'
                        : 'Todavia no se han registrado usuarios.' ?>
                </p>
            </div>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table class="tabla tabla--interactiva">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Documento</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Area</th>
                            <th>Estado</th>
                            <th>Ultimo acceso</th>
                            <th class="tabla__celda-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <div class="celda-usuario">
                                        <div class="celda-usuario__avatar" aria-hidden="true">
                                            <?= Helper::e(mb_strtoupper(mb_substr($u['nombre_completo'], 0, 1))) ?>
                                        </div>
                                        <div>
                                            <strong class="texto-fuerte"><?= Helper::e($u['nombre_completo']) ?></strong>
                                            <span class="texto-bloque texto-mini texto-mono">
                                                <?= Helper::e($u['usuario']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="texto-mini"><?= Helper::e($u['documento']) ?></td>
                                <td class="texto-mini"><?= Helper::e($u['correo']) ?></td>
                                <td>
                                    <span class="badge badge--rol"><?= Helper::e($u['rol']) ?></span>
                                    <?php if (!empty($u['cargo'])): ?>
                                        <span class="texto-bloque texto-mini">
                                            <?= Helper::e(mb_strimwidth($u['cargo'], 0, 30, '...')) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="texto-mini"><?= Helper::eo($u['area'], '—') ?></td>
                                <td>
                                    <span class="<?= Helper::claseEstado($u['estado']) ?>">
                                        <?= Helper::e($u['estado']) ?>
                                    </span>
                                    <?php if ((int) $u['intentos_fallidos'] > 0): ?>
                                        <span class="texto-bloque texto-mini">
                                            <?= (int) $u['intentos_fallidos'] ?> intento(s) fallido(s)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="texto-mini"><?= Helper::e(Helper::hace($u['ultimo_acceso'])) ?></td>
                                <td class="tabla__celda-acciones">
                                    <div class="acciones-fila">
                                        <?php if (Sesion::puede('usuarios', 'editar')): ?>
                                            <a class="boton boton--fantasma boton--mini"
                                               href="<?= Helper::url('usuarios', 'editar', ['id' => $u['id_usuario']]) ?>">
                                                Editar
                                            </a>

                                            <!-- Cambio de estado -->
                                            <form method="post" action="<?= Helper::url('usuarios', 'cambiar-estado') ?>"
                                                  class="formulario--en-linea">
                                                <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">

                                                <?php if ($u['estado'] === 'ACTIVO'): ?>
                                                    <input type="hidden" name="estado" value="INACTIVO">
                                                    <button type="submit" class="boton boton--aviso boton--mini"
                                                            data-confirmar="¿Desactivar la cuenta de <?= Helper::e($u['nombre_completo']) ?>?">
                                                        Desactivar
                                                    </button>
                                                <?php else: ?>
                                                    <input type="hidden" name="estado" value="ACTIVO">
                                                    <button type="submit" class="boton boton--secundario boton--mini">
                                                        Activar
                                                    </button>
                                                <?php endif; ?>
                                            </form>

                                            <!-- Restablecer contrasena -->
                                            <form method="post" action="<?= Helper::url('usuarios', 'restablecer-clave') ?>"
                                                  class="formulario--en-linea">
                                                <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">
                                                <input type="hidden" name="nueva_clave" value="Temporal123*">
                                                <button type="submit" class="boton boton--fantasma boton--mini"
                                                        data-confirmar="Se asignara la contrasena temporal «Temporal123*» a <?= Helper::e($u['nombre_completo']) ?>. El usuario debera cambiarla al ingresar. ¿Continuar?">
                                                    Restablecer clave
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="texto-mini">Sin permisos de gestion</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($paginacion['total_paginas'] > 1): ?>
                <nav class="paginacion">
                    <?php if ($paginacion['pagina_actual'] > 1): ?>
                        <a class="paginacion__enlace"
                           href="<?= Helper::url('usuarios', '', ['q' => $busqueda, 'rol' => $idRol, 'estado' => $estado, 'pagina' => $paginacion['pagina_actual'] - 1]) ?>">
                            &laquo; Anterior
                        </a>
                    <?php endif; ?>
                    <span class="paginacion__info">
                        Pagina <?= (int) $paginacion['pagina_actual'] ?>
                        de <?= (int) $paginacion['total_paginas'] ?>
                        · <?= (int) $paginacion['desde'] ?>–<?= (int) $paginacion['hasta'] ?>
                        de <?= (int) $paginacion['total'] ?>
                    </span>
                    <?php if ($paginacion['pagina_actual'] < $paginacion['total_paginas']): ?>
                        <a class="paginacion__enlace"
                           href="<?= Helper::url('usuarios', '', ['q' => $busqueda, 'rol' => $idRol, 'estado' => $estado, 'pagina' => $paginacion['pagina_actual'] + 1]) ?>">
                            Siguiente &raquo;
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
