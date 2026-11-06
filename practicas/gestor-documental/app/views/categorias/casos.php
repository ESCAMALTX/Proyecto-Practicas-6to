<?php
/**
 * Vista: app/views/categorias/casos.php
 * Gestion de casos y expedientes.
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Casos y expedientes</h2>
        <p class="encabezado-seccion__meta">
            <?= (int) $paginacion['total'] ?> caso(s) registrado(s).
            Los documentos se pueden asociar a un caso para agruparlos.
        </p>
    </div>

    <?php if (Sesion::puede('casos', 'crear')): ?>
        <button type="button" class="boton boton--primario" data-abrir-modal="modalCaso"
                data-modo="crear">
            Nuevo caso
        </button>
    <?php endif; ?>
</section>

<section class="tarjeta">
    <div class="tarjeta__cuerpo">

        <form method="get" action="index.php" class="formulario formulario--en-linea-resumen">
            <input type="hidden" name="modulo" value="casos">
            <div class="campo campo--amplio">
                <input type="search" class="campo__control" name="q"
                       value="<?= Helper::e($busqueda) ?>"
                       placeholder="Buscar por codigo o titulo del caso">
            </div>
            <div class="campo">
                <select class="campo__control" name="estado">
                    <option value="">Todos los estados</option>
                    <?php foreach (['ABIERTO', 'EN_PROCESO', 'CERRADO', 'ARCHIVADO'] as $opcion): ?>
                        <option value="<?= $opcion ?>" <?= $estado === $opcion ? 'selected' : '' ?>>
                            <?= Helper::e($opcion) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="boton boton--secundario">Filtrar</button>
            <?php if ($busqueda !== '' || $estado !== ''): ?>
                <a class="boton boton--fantasma" href="<?= Helper::url('casos') ?>">Limpiar</a>
            <?php endif; ?>
        </form>

        <?php if (empty($casos)): ?>
            <div class="estado-vacio estado-vacio--compacto">
                <div class="estado-vacio__icono" aria-hidden="true">&#128210;</div>
                <h3 class="estado-vacio__titulo">No hay casos registrados</h3>
                <p class="estado-vacio__texto">
                    Los casos permiten agrupar documentos relacionados con un
                    mismo proceso o expediente.
                </p>
            </div>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table class="tabla tabla--interactiva">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Titulo</th>
                            <th>Area</th>
                            <th>Estado</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th class="tabla__celda-centrada">Documentos</th>
                            <th class="tabla__celda-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($casos as $caso): ?>
                            <tr>
                                <td><span class="texto-mono"><?= Helper::e($caso['codigo']) ?></span></td>
                                <td>
                                    <strong class="texto-fuerte">
                                        <?= Helper::e(mb_strimwidth($caso['titulo'], 0, 60, '...')) ?>
                                    </strong>
                                    <?php if (!empty($caso['descripcion'])): ?>
                                        <span class="texto-bloque texto-mini">
                                            <?= Helper::e(mb_strimwidth($caso['descripcion'], 0, 80, '...')) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="texto-mini"><?= Helper::eo($caso['area'], '—') ?></td>
                                <td>
                                    <span class="<?= Helper::claseEstado($caso['estado']) ?>">
                                        <?= Helper::e($caso['estado']) ?>
                                    </span>
                                </td>
                                <td class="texto-mini"><?= Helper::e(Helper::fecha($caso['fecha_apertura'])) ?></td>
                                <td class="texto-mini"><?= Helper::e(Helper::fecha($caso['fecha_cierre'])) ?></td>
                                <td class="tabla__celda-centrada">
                                    <a class="enlace-fuerte"
                                       href="<?= Helper::url('documentos', '', ['caso' => $caso['id_caso']]) ?>">
                                        <?= (int) $caso['total_documentos'] ?>
                                    </a>
                                </td>
                                <td class="tabla__celda-acciones">
                                    <div class="acciones-fila">
                                        <?php if (Sesion::puede('casos', 'editar')): ?>
                                            <button type="button" class="boton boton--fantasma boton--mini"
                                                    data-abrir-modal="modalCaso"
                                                    data-modo="editar"
                                                    data-id="<?= (int) $caso['id_caso'] ?>"
                                                    data-codigo="<?= Helper::e($caso['codigo']) ?>"
                                                    data-titulo="<?= Helper::e($caso['titulo']) ?>"
                                                    data-descripcion="<?= Helper::e((string) $caso['descripcion']) ?>"
                                                    data-area="<?= (int) $caso['id_area'] ?>"
                                                    data-estado="<?= Helper::e($caso['estado']) ?>"
                                                    data-apertura="<?= Helper::e($caso['fecha_apertura']) ?>"
                                                    data-cierre="<?= Helper::e((string) $caso['fecha_cierre']) ?>">
                                                Editar
                                            </button>
                                        <?php endif; ?>

                                        <?php if (Sesion::puede('casos', 'eliminar')): ?>
                                            <form method="post" action="<?= Helper::url('casos', 'eliminar') ?>"
                                                  class="formulario--en-linea"
                                                  data-confirmar="¿Confirma que desea eliminar o archivar el caso <?= Helper::e($caso['codigo']) ?>?">
                                                <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $caso['id_caso'] ?>">
                                                <button type="submit" class="boton boton--peligro boton--mini">
                                                    Eliminar
                                                </button>
                                            </form>
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
                           href="<?= Helper::url('casos', '', ['q' => $busqueda, 'estado' => $estado, 'pagina' => $paginacion['pagina_actual'] - 1]) ?>">
                            &laquo; Anterior
                        </a>
                    <?php endif; ?>
                    <span class="paginacion__info">
                        Pagina <?= (int) $paginacion['pagina_actual'] ?>
                        de <?= (int) $paginacion['total_paginas'] ?>
                    </span>
                    <?php if ($paginacion['pagina_actual'] < $paginacion['total_paginas']): ?>
                        <a class="paginacion__enlace"
                           href="<?= Helper::url('casos', '', ['q' => $busqueda, 'estado' => $estado, 'pagina' => $paginacion['pagina_actual'] + 1]) ?>">
                            Siguiente &raquo;
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- =================================================================
     MODAL DE CASO
     ================================================================= -->
<?php if (Sesion::puede('casos', 'crear') || Sesion::puede('casos', 'editar')): ?>
    <div class="modal" id="modalCaso" hidden>
        <div class="modal__fondo" data-cerrar-modal></div>

        <div class="modal__caja modal__caja--amplia" role="dialog" aria-modal="true"
             aria-labelledby="modalCasoTitulo">
            <div class="modal__cabecera">
                <h3 class="modal__titulo" id="modalCasoTitulo">Nuevo caso</h3>
                <button type="button" class="modal__cerrar" data-cerrar-modal
                        aria-label="Cerrar">&times;</button>
            </div>

            <form method="post" action="<?= Helper::url('casos', 'guardar') ?>"
                  class="formulario" id="formCaso">
                <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                <input type="hidden" name="id" id="casoId" value="0">

                <div class="modal__cuerpo">
                    <div class="formulario__fila">
                        <div class="campo">
                            <label class="campo__etiqueta" for="casoCodigo">Codigo</label>
                            <input type="text" class="campo__control" id="casoCodigo"
                                   name="codigo" maxlength="50"
                                   placeholder="Se genera automaticamente si se deja vacio">
                            <span class="campo__ayuda">Solo al crear. No se puede cambiar despues.</span>
                        </div>

                        <div class="campo">
                            <label class="campo__etiqueta" for="casoEstado">Estado</label>
                            <select class="campo__control" id="casoEstado" name="estado">
                                <?php foreach (['ABIERTO', 'EN_PROCESO', 'CERRADO', 'ARCHIVADO'] as $opcion): ?>
                                    <option value="<?= $opcion ?>"><?= Helper::e($opcion) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="casoTitulo">
                            Titulo <span class="campo__obligatorio">*</span>
                        </label>
                        <input type="text" class="campo__control" id="casoTitulo"
                               name="titulo" maxlength="200" required>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="casoDescripcion">Descripcion</label>
                        <textarea class="campo__control" id="casoDescripcion"
                                  name="descripcion" rows="3"></textarea>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="casoArea">Area responsable</label>
                        <select class="campo__control" id="casoArea" name="id_area">
                            <option value="">Sin area especifica</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= (int) $area['id_area'] ?>">
                                    <?= Helper::e($area['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="formulario__fila">
                        <div class="campo">
                            <label class="campo__etiqueta" for="casoApertura">
                                Fecha de apertura <span class="campo__obligatorio">*</span>
                            </label>
                            <input type="date" class="campo__control" id="casoApertura"
                                   name="fecha_apertura" max="<?= date('Y-m-d') ?>"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="campo">
                            <label class="campo__etiqueta" for="casoCierre">Fecha de cierre</label>
                            <input type="date" class="campo__control" id="casoCierre"
                                   name="fecha_cierre">
                            <span class="campo__ayuda">
                                Se completa sola al marcar el caso como cerrado.
                            </span>
                        </div>
                    </div>
                </div>

                <div class="modal__pie">
                    <button type="button" class="boton boton--fantasma" data-cerrar-modal>
                        Cancelar
                    </button>
                    <button type="submit" class="boton boton--primario">Guardar</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
