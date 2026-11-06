<?php
/**
 * Vista: app/views/usuarios/areas.php
 * Gestion de areas de la organizacion.
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Areas de la organizacion</h2>
        <p class="encabezado-seccion__meta">
            Las areas segmentan el acceso a los documentos y agrupan a los usuarios.
        </p>
    </div>

    <button type="button" class="boton boton--primario"
            data-abrir-modal="modalArea" data-modo="crear">
        Nueva area
    </button>
</section>

<section class="tarjeta">
    <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
        <div class="tabla-contenedor">
            <table class="tabla tabla--interactiva">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th>Descripcion</th>
                        <th class="tabla__celda-centrada">Usuarios</th>
                        <th class="tabla__celda-centrada">Documentos</th>
                        <th>Estado</th>
                        <th class="tabla__celda-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($areas as $area): ?>
                        <tr>
                            <td>
                                <strong class="texto-fuerte"><?= Helper::e($area['nombre']) ?></strong>
                            </td>
                            <td class="texto-mini">
                                <?= Helper::eo($area['descripcion'], 'Sin descripcion') ?>
                            </td>
                            <td class="tabla__celda-centrada">
                                <a class="enlace-fuerte"
                                   href="<?= Helper::url('usuarios', '', ['area' => $area['id_area']]) ?>">
                                    <?= (int) $area['total_usuarios'] ?>
                                </a>
                            </td>
                            <td class="tabla__celda-centrada">
                                <a class="enlace-fuerte"
                                   href="<?= Helper::url('documentos', '', ['area' => $area['id_area']]) ?>">
                                    <?= (int) $area['total_documentos'] ?>
                                </a>
                            </td>
                            <td>
                                <span class="<?= $area['activo'] ? 'badge badge--exito' : 'badge badge--neutro' ?>">
                                    <?= $area['activo'] ? 'ACTIVA' : 'INACTIVA' ?>
                                </span>
                            </td>
                            <td class="tabla__celda-acciones">
                                <button type="button" class="boton boton--fantasma boton--mini"
                                        data-abrir-modal="modalArea"
                                        data-modo="editar"
                                        data-id="<?= (int) $area['id_area'] ?>"
                                        data-nombre="<?= Helper::e($area['nombre']) ?>"
                                        data-descripcion="<?= Helper::e((string) $area['descripcion']) ?>"
                                        data-activo="<?= (int) $area['activo'] ?>">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal" id="modalArea" hidden>
    <div class="modal__fondo" data-cerrar-modal></div>

    <div class="modal__caja" role="dialog" aria-modal="true" aria-labelledby="modalAreaTitulo">
        <div class="modal__cabecera">
            <h3 class="modal__titulo" id="modalAreaTitulo">Nueva area</h3>
            <button type="button" class="modal__cerrar" data-cerrar-modal aria-label="Cerrar">&times;</button>
        </div>

        <form method="post" action="<?= Helper::url('areas', 'guardar') ?>" class="formulario">
            <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
            <input type="hidden" name="id" id="areaId" value="0">

            <div class="modal__cuerpo">
                <div class="campo">
                    <label class="campo__etiqueta" for="areaNombre">
                        Nombre <span class="campo__obligatorio">*</span>
                    </label>
                    <input type="text" class="campo__control" id="areaNombre"
                           name="nombre" maxlength="100" required>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="areaDescripcion">Descripcion</label>
                    <textarea class="campo__control" id="areaDescripcion"
                              name="descripcion" rows="3" maxlength="200"></textarea>
                </div>

                <label class="checkbox">
                    <input type="checkbox" id="areaActivo" name="activo" value="1" checked>
                    <span>Area activa</span>
                </label>
            </div>

            <div class="modal__pie">
                <button type="button" class="boton boton--fantasma" data-cerrar-modal>Cancelar</button>
                <button type="submit" class="boton boton--primario">Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
