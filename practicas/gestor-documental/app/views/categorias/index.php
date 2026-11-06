<?php
/**
 * Vista: app/views/categorias/index.php
 * Gestion de categorias documentales (RF-016).
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Categorias documentales</h2>
        <p class="encabezado-seccion__meta">
            <?= count($categorias) ?> categoria(s) registrada(s) ·
            Las categorias con documentos asociados no se eliminan, se desactivan.
        </p>
    </div>

    <?php if (Sesion::puede('categorias', 'crear')): ?>
        <button type="button" class="boton boton--primario" data-abrir-modal="modalCategoria"
                data-modo="crear">
            Nueva categoria
        </button>
    <?php endif; ?>
</section>

<section class="tarjeta">
    <div class="tarjeta__cuerpo">

        <form method="get" action="index.php" class="formulario formulario--en-linea-resumen">
            <input type="hidden" name="modulo" value="categorias">
            <div class="campo campo--amplio">
                <input type="search" class="campo__control" name="q"
                       value="<?= Helper::e($busqueda) ?>"
                       placeholder="Buscar categoria por nombre o descripcion">
            </div>
            <button type="submit" class="boton boton--secundario">Buscar</button>
            <?php if ($busqueda !== ''): ?>
                <a class="boton boton--fantasma" href="<?= Helper::url('categorias') ?>">Limpiar</a>
            <?php endif; ?>
        </form>

        <?php if (empty($categorias)): ?>
            <div class="estado-vacio estado-vacio--compacto">
                <div class="estado-vacio__icono" aria-hidden="true">&#128193;</div>
                <h3 class="estado-vacio__titulo">No hay categorias</h3>
                <p class="estado-vacio__texto">
                    <?= $busqueda !== ''
                        ? 'Ninguna categoria coincide con la busqueda.'
                        : 'Cree la primera categoria para clasificar los documentos.' ?>
                </p>
            </div>
        <?php else: ?>
            <div class="rejilla rejilla--3">
                <?php foreach ($categorias as $cat): ?>
                    <article class="categoria-tarjeta <?= !$cat['activo'] ? 'categoria-tarjeta--inactiva' : '' ?>">
                        <div class="categoria-tarjeta__cabecera">
                            <h3 class="categoria-tarjeta__nombre"><?= Helper::e($cat['nombre']) ?></h3>
                            <span class="<?= $cat['activo'] ? 'badge badge--exito' : 'badge badge--neutro' ?>">
                                <?= $cat['activo'] ? 'Activa' : 'Inactiva' ?>
                            </span>
                        </div>

                        <p class="categoria-tarjeta__descripcion">
                            <?= Helper::eo($cat['descripcion'], 'Sin descripcion') ?>
                        </p>

                        <div class="categoria-tarjeta__pie">
                            <span class="categoria-tarjeta__conteo">
                                <strong><?= (int) $cat['total_documentos'] ?></strong> documento(s)
                            </span>

                            <div class="acciones-fila">
                                <?php if (Sesion::puede('categorias', 'editar')): ?>
                                    <button type="button" class="boton boton--fantasma boton--mini"
                                            data-abrir-modal="modalCategoria"
                                            data-modo="editar"
                                            data-id="<?= (int) $cat['id_categoria'] ?>"
                                            data-nombre="<?= Helper::e($cat['nombre']) ?>"
                                            data-descripcion="<?= Helper::e((string) $cat['descripcion']) ?>"
                                            data-activo="<?= (int) $cat['activo'] ?>">
                                        Editar
                                    </button>
                                <?php endif; ?>

                                <?php if (Sesion::puede('categorias', 'eliminar')): ?>
                                    <form method="post" action="<?= Helper::url('categorias', 'eliminar') ?>"
                                          class="formulario--en-linea"
                                          data-confirmar="¿Confirma que desea eliminar o desactivar la categoria «<?= Helper::e($cat['nombre']) ?>»?">
                                        <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $cat['id_categoria'] ?>">
                                        <button type="submit" class="boton boton--peligro boton--mini">
                                            Eliminar
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <a class="boton boton--fantasma boton--mini"
                                   href="<?= Helper::url('documentos', '', ['categoria' => $cat['id_categoria']]) ?>">
                                    Ver documentos
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- =================================================================
     MODAL DE CATEGORIA
     ================================================================= -->
<?php if (Sesion::puede('categorias', 'crear') || Sesion::puede('categorias', 'editar')): ?>
    <div class="modal" id="modalCategoria" hidden>
        <div class="modal__fondo" data-cerrar-modal></div>

        <div class="modal__caja" role="dialog" aria-modal="true"
             aria-labelledby="modalCategoriaTitulo">
            <div class="modal__cabecera">
                <h3 class="modal__titulo" id="modalCategoriaTitulo">Nueva categoria</h3>
                <button type="button" class="modal__cerrar" data-cerrar-modal
                        aria-label="Cerrar">&times;</button>
            </div>

            <form method="post" action="<?= Helper::url('categorias', 'guardar') ?>"
                  class="formulario" id="formCategoria">
                <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                <input type="hidden" name="id" id="catId" value="0">

                <div class="modal__cuerpo">
                    <div class="campo">
                        <label class="campo__etiqueta" for="catNombre">
                            Nombre <span class="campo__obligatorio">*</span>
                        </label>
                        <input type="text" class="campo__control" id="catNombre"
                               name="nombre" maxlength="100" required>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="catDescripcion">Descripcion</label>
                        <textarea class="campo__control" id="catDescripcion"
                                  name="descripcion" rows="3" maxlength="250"></textarea>
                    </div>

                    <label class="checkbox">
                        <input type="checkbox" id="catActivo" name="activo" value="1" checked>
                        <span>Categoria activa y disponible para clasificar</span>
                    </label>
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
