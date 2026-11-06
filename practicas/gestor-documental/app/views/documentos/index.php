<?php
/**
 * Vista: app/views/documentos/index.php
 * Estructura e interfaz inspirada en ISOTools Gestor Documental.
 */
require RUTA_VISTAS . '/partials/header.php';

$categoriaActual = (int) ($filtros['id_categoria'] ?? 0);
$nombreCarpetaActual = 'Procedimientos';
foreach ($categorias as $c) {
    if ((int)$c['id_categoria'] === $categoriaActual) {
        $nombreCarpetaActual = $c['nombre'];
        break;
    }
}
?>

<div class="iso-workspace">

    <!-- =============================================================
         PANEL IZQUIERDO: ARBOL DE CARPETAS / SISTEMA DE GESTION
         ============================================================= -->
    <aside class="iso-sidebar">

        <!-- Herramientas superiores de vista -->
        <div class="iso-sidebar__tools">
            <button type="button" class="iso-tool-btn" title="Buscar en árbol" onclick="document.getElementById('inputArbolBusqueda').focus();"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></button>
            <button type="button" class="iso-tool-btn" title="Vista de lista / jerarquía"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg></button>
        </div>

        <!-- Selector desplegable de sistema -->
        <select class="iso-select" id="selectSistemaGestion" onchange="if(this.value) window.location.href=this.value;">
            <option value="<?= Helper::url('documentos') ?>">Sistema de Gestión</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= Helper::url('documentos', '', ['categoria' => $cat['id_categoria']]) ?>"
                    <?= $categoriaActual === (int) $cat['id_categoria'] ? 'selected' : '' ?>>
                    <?= Helper::e($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Boton relaciones de documentos -->
        <button type="button" class="iso-btn-relations" onclick="alert('Relaciones de documentos: Vinculación de procesos y normativas.')">
            Relaciones de documentos
        </button>

        <!-- Buscador rapido del árbol -->
        <form method="get" action="index.php" class="iso-tree-search">
            <input type="hidden" name="modulo" value="documentos">
            <input type="text" id="inputArbolBusqueda" name="q" placeholder="" value="<?= Helper::e($filtros['busqueda'] ?? '') ?>">
            <button type="submit" title="Buscar"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></button>
        </form>

        <!-- Arbol jerarquico -->
        <div class="iso-tree">
            <!-- Rama General -->
            <div class="iso-tree__item">
                <span class="iso-tree__toggle">&#8250;</span>
                <a href="<?= Helper::url('documentos') ?>" class="iso-tree__title">General</a>
            </div>

            <!-- Rama Procesos Clave -->
            <div class="iso-tree__item">
                <span class="iso-tree__toggle">&#709;</span>
                <span class="iso-tree__title">Procesos Clave</span>
            </div>

            <div class="iso-tree__subgroup">
                <div class="iso-tree__header-branch">Vigor</div>

                <!-- Carpeta 1 -->
                <div class="iso-tree__leaf">
                    <span class="iso-tree__dots">...</span>
                    <span class="iso-tree__folder-icon"></span>
                    <span style="font-weight:600;">Carpeta 1</span>
                </div>

                <div style="padding-left: 16px;">
                    <!-- Procedimientos (Activo) -->
                    <a href="<?= Helper::url('documentos') ?>" class="iso-tree__leaf <?= empty($categoriaActual) ? 'iso-tree__leaf--active' : '' ?>">
                        <span class="iso-tree__dots">...</span>
                        <span class="iso-tree__folder-icon" style="background:#00688f;"></span>
                        <span>Procedimientos</span>
                    </a>
                    <a href="<?= Helper::url('documentos', '', ['categoria' => 1]) ?>" class="iso-tree__leaf <?= $categoriaActual === 1 ? 'iso-tree__leaf--active' : '' ?>">
                        <span class="iso-tree__dots">...</span>
                        <span class="iso-tree__folder-icon"></span>
                        <span>Carpeta 1a</span>
                    </a>
                </div>

                <a href="<?= Helper::url('documentos', '', ['categoria' => 2]) ?>" class="iso-tree__leaf <?= $categoriaActual === 2 ? 'iso-tree__leaf--active' : '' ?>">
                    <span class="iso-tree__dots">...</span>
                    <span class="iso-tree__folder-icon"></span>
                    <span>Carpeta 1b</span>
                </a>
                <a href="<?= Helper::url('documentos', '', ['categoria' => 3]) ?>" class="iso-tree__leaf <?= $categoriaActual === 3 ? 'iso-tree__leaf--active' : '' ?>">
                    <span class="iso-tree__dots">...</span>
                    <span class="iso-tree__folder-icon"></span>
                    <span>Carpeta 1c</span>
                </a>
                <a href="<?= Helper::url('documentos', '', ['categoria' => 4]) ?>" class="iso-tree__leaf <?= $categoriaActual === 4 ? 'iso-tree__leaf--active' : '' ?>">
                    <span class="iso-tree__dots">...</span>
                    <span class="iso-tree__folder-icon"></span>
                    <span>Carpeta 1d</span>
                </a>
                <a href="<?= Helper::url('documentos', '', ['categoria' => 5]) ?>" class="iso-tree__leaf <?= $categoriaActual === 5 ? 'iso-tree__leaf--active' : '' ?>">
                    <span class="iso-tree__dots">...</span>
                    <span class="iso-tree__folder-icon"></span>
                    <span>Carpeta 1e</span>
                </a>

                <?php if (!empty($categorias)): ?>
                    <div class="iso-tree__header-branch" style="margin-top: 10px;">Categorías</div>
                    <?php foreach ($categorias as $cat): ?>
                        <a href="<?= Helper::url('documentos', '', ['categoria' => $cat['id_categoria']]) ?>"
                           class="iso-tree__leaf <?= $categoriaActual === (int)$cat['id_categoria'] ? 'iso-tree__leaf--active' : '' ?>">
                            <span class="iso-tree__dots">...</span>
                            <span class="iso-tree__folder-icon"></span>
                            <span><?= Helper::e($cat['nombre']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </aside>

    <!-- =============================================================
         PANEL DERECHO: CONTENIDO, ACCIONES Y TABLA DE DOCUMENTOS
         ============================================================= -->
    <main class="iso-content">

        <!-- Ruta de migas de pan -->
        <div class="iso-breadcrumb">
            <strong>Documentos de la carpeta:</strong> / Sistema de Gestión / Procesos Clave / Vigor / Carpeta 1 / <?= Helper::e($nombreCarpetaActual) ?>
            <?php if (!empty($filtros['busqueda'])): ?>
                &nbsp;·&nbsp;<em>Filtro: "<?= Helper::e($filtros['busqueda']) ?>"</em>
            <?php endif; ?>
        </div>

        <!-- Barra de botones de acción -->
        <div class="iso-actions-bar">
            <button type="button" class="iso-btn" onclick="window.history.back()">Atrás</button>
            <?php if (Sesion::puede('documentos', 'crear')): ?>
                <a href="<?= Helper::url('documentos', 'crear') ?>" class="iso-btn">Nuevo documento</a>
                <a href="<?= Helper::url('documentos', 'crear') ?>" class="iso-btn">Nuevo fichero</a>
                <a href="javascript:void(0)" onclick="alert('Registrar nuevo enlace web.')" class="iso-btn">Nuevo enlace</a>
            <?php endif; ?>
            <button type="button" class="iso-btn" onclick="window.print()">Imprimir</button>
        </div>

        <!-- Tabla con estilo ISOTools -->
        <div class="iso-table-container">
            <table class="iso-table">
                <thead>
                    <tr>
                        <th style="width: 24px;" class="center">
                            <input type="checkbox" id="checkAllDocs" onclick="document.querySelectorAll('.doc-check').forEach(function(c){ c.checked = this.checked; }, this)">
                        </th>
                        <th style="width: 26px;" class="center">
                            <span title="Acciones" style="font-size:12px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg></span>
                        </th>
                        <th>Título &#9660;</th>
                        <th style="width: 140px;">Fase</th>
                        <th style="width: 220px;">Propietario</th>
                        <th class="center" style="width: 44px;">Ver.</th>
                        <th style="width: 90px;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($documentos)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #666;">
                                No hay documentos en esta carpeta.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documentos as $doc): ?>
                            <tr>
                                <td class="center">
                                    <input type="checkbox" class="doc-check" value="<?= (int) $doc['id_documento'] ?>">
                                </td>
                                <td class="center">
                                    <a href="<?= Helper::url('documentos', 'ver', ['id' => $doc['id_documento']]) ?>"
                                       class="iso-action-doc-icon" title="Ver ficha del documento">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                                    </a>
                                </td>
                                <td>
                                    <div class="iso-doc-cell">
                                        <?= Helper::renderIsoIconoArchivo($doc['extension'] ?? '', $doc['titulo'] ?? '') ?>
                                        <a href="<?= Helper::url('documentos', 'ver', ['id' => $doc['id_documento']]) ?>"
                                           class="iso-doc-title">
                                            <?= Helper::e($doc['titulo']) ?>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <?= Helper::e($doc['estado'] === 'PUBLICADO' ? 'Vigente' : ($doc['estado'] === 'BORRADOR' ? 'Borrador' : $doc['estado'])) ?>
                                </td>
                                <td>
                                    <?= Helper::e(!empty($doc['autor']) ? $doc['autor'] : $doc['responsable']) ?>
                                </td>
                                <td class="center">
                                    <?= (int) $doc['version_actual'] ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($doc['fecha_documento'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7">
                            Total: <?= (int) $paginacion['total'] ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </main>

</div>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
