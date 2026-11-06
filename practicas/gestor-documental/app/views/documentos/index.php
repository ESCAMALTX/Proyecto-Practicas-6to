<?php
/**
 * Vista: app/views/documentos/index.php
 * Listado de documentos con filtros y paginacion (RF-004, RF-016, RF-024).
 */
require RUTA_VISTAS . '/partials/header.php';

// Parametros conservados en la paginacion
$parametrosBase = array_filter([
    'modulo'           => 'documentos',
    'q'                => $filtros['busqueda'] ?? '',
    'categoria'        => $filtros['id_categoria'] ?? '',
    'area'             => $filtros['id_area'] ?? '',
    'caso'             => $filtros['id_caso'] ?? '',
    'responsable'      => $filtros['id_responsable'] ?? '',
    'estado'           => $filtros['estado'] ?? '',
    'confidencialidad' => $filtros['confidencialidad'] ?? '',
    'etiqueta'         => $filtros['etiqueta'] ?? '',
    'fecha_desde'      => $filtros['fecha_desde'] ?? '',
    'fecha_hasta'      => $filtros['fecha_hasta'] ?? '',
], fn($valor) => $valor !== '' && $valor !== null);
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Inventario documental</h2>
        <p class="encabezado-seccion__meta">
            <?= (int) $paginacion['total'] ?> documento(s) a su alcance
            <?= count($filtros) > 0 ? ' · con filtros aplicados' : '' ?>
        </p>
    </div>

    <div class="encabezado-seccion__acciones">
        <a class="boton boton--secundario"
           href="<?= Helper::url('reportes', 'inventario') ?>">Exportar CSV</a>

        <?php if (Sesion::puede('documentos', 'crear')): ?>
            <a class="boton boton--primario"
               href="<?= Helper::url('documentos', 'crear') ?>">Cargar documento</a>
        <?php endif; ?>
    </div>
</section>

<!-- =================================================================
     FILTROS
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cuerpo">
        <form method="get" action="index.php" class="formulario">
            <input type="hidden" name="modulo" value="documentos">

            <div class="formulario__fila formulario__fila--3">
                <div class="campo campo--amplio">
                    <label class="campo__etiqueta" for="q">Buscar</label>
                    <input type="search" class="campo__control" id="q" name="q"
                           value="<?= Helper::e($filtros['busqueda'] ?? '') ?>"
                           placeholder="Titulo, codigo, descripcion o palabra clave">
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="categoria">Categoria</label>
                    <select class="campo__control" id="categoria" name="categoria">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= (int) $cat['id_categoria'] ?>"
                                <?= (int) ($filtros['id_categoria'] ?? 0) === (int) $cat['id_categoria'] ? 'selected' : '' ?>>
                                <?= Helper::e($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="estado">Estado</label>
                    <select class="campo__control" id="estado" name="estado">
                        <option value="">Todos</option>
                        <?php foreach (['BORRADOR', 'EN_PROCESO', 'PUBLICADO', 'ARCHIVADO', 'ELIMINADO'] as $opcion): ?>
                            <option value="<?= $opcion ?>"
                                <?= ($filtros['estado'] ?? '') === $opcion ? 'selected' : '' ?>>
                                <?= Helper::e($opcion) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="formulario__fila formulario__fila--4">
                <div class="campo">
                    <label class="campo__etiqueta" for="area">Area</label>
                    <select class="campo__control" id="area" name="area">
                        <option value="">Todas</option>
                        <?php foreach ($areas as $area): ?>
                            <option value="<?= (int) $area['id_area'] ?>"
                                <?= (int) ($filtros['id_area'] ?? 0) === (int) $area['id_area'] ? 'selected' : '' ?>>
                                <?= Helper::e($area['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="caso">Caso</label>
                    <select class="campo__control" id="caso" name="caso">
                        <option value="">Todos</option>
                        <?php foreach ($casos as $caso): ?>
                            <option value="<?= (int) $caso['id_caso'] ?>"
                                <?= (int) ($filtros['id_caso'] ?? 0) === (int) $caso['id_caso'] ? 'selected' : '' ?>>
                                <?= Helper::e($caso['codigo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="confidencialidad">Confidencialidad</label>
                    <select class="campo__control" id="confidencialidad" name="confidencialidad">
                        <option value="">Todas</option>
                        <?php foreach (['PUBLICO', 'INTERNO', 'RESERVADO', 'CONFIDENCIAL'] as $opcion): ?>
                            <option value="<?= $opcion ?>"
                                <?= ($filtros['confidencialidad'] ?? '') === $opcion ? 'selected' : '' ?>>
                                <?= Helper::e($opcion) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="responsable">Responsable</label>
                    <select class="campo__control" id="responsable" name="responsable">
                        <option value="">Todos</option>
                        <?php foreach ($responsables as $resp): ?>
                            <option value="<?= (int) $resp['id_responsable'] ?>"
                                <?= (int) ($filtros['id_responsable'] ?? 0) === (int) $resp['id_responsable'] ? 'selected' : '' ?>>
                                <?= Helper::e(mb_strimwidth($resp['responsable'], 0, 34, '...')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="formulario__fila formulario__fila--4">
                <div class="campo">
                    <label class="campo__etiqueta" for="etiqueta">Etiqueta</label>
                    <select class="campo__control" id="etiqueta" name="etiqueta">
                        <option value="">Todas</option>
                        <?php foreach ($etiquetas as $etq): ?>
                            <option value="<?= Helper::e($etq['nombre']) ?>"
                                <?= ($filtros['etiqueta'] ?? '') === $etq['nombre'] ? 'selected' : '' ?>>
                                <?= Helper::e($etq['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="fecha_desde">Fecha desde</label>
                    <input type="date" class="campo__control" id="fecha_desde"
                           name="fecha_desde" value="<?= Helper::e($filtros['fecha_desde'] ?? '') ?>">
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="fecha_hasta">Fecha hasta</label>
                    <input type="date" class="campo__control" id="fecha_hasta"
                           name="fecha_hasta" value="<?= Helper::e($filtros['fecha_hasta'] ?? '') ?>">
                </div>

                <div class="campo campo--acciones">
                    <button type="submit" class="boton boton--primario">Filtrar</button>
                    <a class="boton boton--fantasma" href="<?= Helper::url('documentos') ?>">Limpiar</a>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- =================================================================
     TABLA DE DOCUMENTOS
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">

        <?php if (empty($documentos)): ?>
            <div class="estado-vacio estado-vacio--compacto">
                <div class="estado-vacio__icono" aria-hidden="true">&#128193;</div>
                <h3 class="estado-vacio__titulo">No hay documentos que mostrar</h3>
                <p class="estado-vacio__texto">
                    <?= count($filtros) > 0
                        ? 'Ningun documento coincide con los filtros aplicados.'
                        : 'Todavia no se han registrado documentos en el sistema.' ?>
                </p>
                <?php if (Sesion::puede('documentos', 'crear')): ?>
                    <a class="boton boton--primario"
                       href="<?= Helper::url('documentos', 'crear') ?>">Cargar el primer documento</a>
                <?php endif; ?>
            </div>
        <?php else: ?>

            <div class="tabla-contenedor">
                <table class="tabla tabla--interactiva">
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Documento</th>
                            <th>Categoria</th>
                            <th>Area</th>
                            <th>Confidencialidad</th>
                            <th>Estado</th>
                            <th>Version</th>
                            <th>Fecha</th>
                            <th class="tabla__celda-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                            <tr>
                                <td>
                                    <span class="texto-mono"><?= Helper::e($doc['codigo']) ?></span>
                                </td>
                                <td>
                                    <a class="enlace-fuerte"
                                       href="<?= Helper::url('documentos', 'ver', ['id' => $doc['id_documento']]) ?>">
                                        <?= Helper::e(mb_strimwidth($doc['titulo'], 0, 58, '...')) ?>
                                    </a>
                                    <span class="texto-bloque texto-mini">
                                        <?= Helper::e(mb_strimwidth((string) $doc['responsable'], 0, 40, '...')) ?>
                                        <?php if (!empty($doc['nombre_archivo'])): ?>
                                            · <?= Helper::e(Helper::iconoArchivo((string) $doc['extension'])) ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?= Helper::e($doc['categoria']) ?></td>
                                <td class="texto-mini"><?= Helper::eo($doc['area'], '—') ?></td>
                                <td>
                                    <span class="<?= Helper::claseConfidencialidad($doc['nivel_confidencialidad']) ?>">
                                        <?= Helper::e($doc['nivel_confidencialidad']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?= Helper::claseEstado($doc['estado']) ?>">
                                        <?= Helper::e($doc['estado']) ?>
                                    </span>
                                </td>
                                <td class="tabla__celda-centrada">
                                    v<?= (int) $doc['version_actual'] ?>
                                    <span class="texto-mini">/<?= (int) $doc['total_versiones'] ?></span>
                                </td>
                                <td class="texto-mini">
                                    <?= Helper::e(Helper::fecha($doc['fecha_documento'])) ?>
                                </td>
                                <td class="tabla__celda-acciones">
                                    <div class="acciones-fila">
                                        <a class="boton boton--fantasma boton--mini"
                                           href="<?= Helper::url('documentos', 'ver', ['id' => $doc['id_documento']]) ?>"
                                           title="Ver la ficha del documento">Ver</a>

                                        <?php if (Sesion::puede('documentos', 'editar')): ?>
                                            <a class="boton boton--fantasma boton--mini"
                                               href="<?= Helper::url('documentos', 'editar', ['id' => $doc['id_documento']]) ?>"
                                               title="Editar los metadatos">Editar</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginacion -->
            <?php if ($paginacion['total_paginas'] > 1): ?>
                <nav class="paginacion" aria-label="Paginacion de documentos">
                    <?php if ($paginacion['pagina_actual'] > 1): ?>
                        <a class="paginacion__enlace"
                           href="index.php?<?= http_build_query(array_merge($parametrosBase, ['pagina' => $paginacion['pagina_actual'] - 1])) ?>">
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
                           href="index.php?<?= http_build_query(array_merge($parametrosBase, ['pagina' => $paginacion['pagina_actual'] + 1])) ?>">
                            Siguiente &raquo;
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
