<?php
/**
 * Vista: app/views/panel/buscar.php
 * Consulta documental. Sustituye al antiguo chat con IA: el usuario
 * escribe palabras clave y el sistema devuelve los documentos y los
 * fragmentos donde aparecen, con acceso directo a la fuente.
 * RF-009, RF-010, RF-011, RF-013, RF-020, RF-024.
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<!-- =================================================================
     BUSCADOR
     ================================================================= -->
<section class="buscador">
    <form method="get" action="index.php" class="buscador__formulario">
        <input type="hidden" name="modulo" value="panel">
        <input type="hidden" name="accion" value="buscar">

        <div class="buscador__caja">
            <span class="buscador__icono" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
            <input type="search"
                   class="buscador__entrada"
                   name="q"
                   value="<?= Helper::e($termino) ?>"
                   placeholder="Escriba palabras clave: contrato, acta de comite, politica de datos..."
                   autofocus
                   aria-label="Termino de busqueda">
            <button type="submit" class="boton boton--primario">Buscar</button>
        </div>

        <p class="buscador__ayuda">
            El sistema busca en titulo, codigo, descripcion, palabras clave y en el
            texto extraido de los archivos PDF, DOCX, XLSX y TXT.
        </p>

        <!-- Filtros avanzados (RF-024) -->
        <details class="filtros" <?= count(array_diff_key($filtros, ['busqueda' => 1])) > 0 ? 'open' : '' ?>>
            <summary class="filtros__resumen">Filtros avanzados</summary>

            <div class="filtros__cuerpo">
                <div class="formulario__fila formulario__fila--3">
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
                                    <?= Helper::e($caso['codigo'] . ' — ' . mb_strimwidth($caso['titulo'], 0, 40, '...')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="formulario__fila formulario__fila--3">
                    <div class="campo">
                        <label class="campo__etiqueta" for="estado">Estado</label>
                        <select class="campo__control" id="estado" name="estado">
                            <option value="">Todos</option>
                            <?php foreach (['PUBLICADO', 'BORRADOR', 'EN_PROCESO', 'ARCHIVADO'] as $opcion): ?>
                                <option value="<?= $opcion ?>"
                                    <?= ($filtros['estado'] ?? '') === $opcion ? 'selected' : '' ?>>
                                    <?= Helper::e($opcion) ?>
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
                </div>

                <div class="formulario__fila">
                    <div class="campo">
                        <label class="campo__etiqueta" for="fecha_desde">Fecha del documento desde</label>
                        <input type="date" class="campo__control" id="fecha_desde"
                               name="fecha_desde" value="<?= Helper::e($filtros['fecha_desde'] ?? '') ?>">
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="fecha_hasta">Hasta</label>
                        <input type="date" class="campo__control" id="fecha_hasta"
                               name="fecha_hasta" value="<?= Helper::e($filtros['fecha_hasta'] ?? '') ?>">
                    </div>
                </div>

                <div class="formulario__acciones">
                    <button type="submit" class="boton boton--primario">Aplicar filtros</button>
                    <a class="boton boton--fantasma"
                       href="<?= Helper::url('panel', 'buscar') ?>">Limpiar</a>
                </div>
            </div>
        </details>
    </form>
</section>

<!-- =================================================================
     RESULTADOS
     ================================================================= -->
<?php if ($termino === '' && count($filtros) <= 1): ?>

    <div class="estado-vacio">
        <div class="estado-vacio__icono" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></div>
        <h2 class="estado-vacio__titulo">Comience su consulta</h2>
        <p class="estado-vacio__texto">
            Escriba palabras clave para localizar documentos institucionales.
            El sistema devuelve los documentos y los fragmentos donde aparece
            el termino, indicando siempre la fuente.
        </p>
        <div class="estado-vacio__sugerencias">
            <span class="texto-mini">Sugerencias:</span>
            <a class="etiqueta-sugerida" href="<?= Helper::url('panel', 'buscar', ['q' => 'contrato']) ?>">contrato</a>
            <a class="etiqueta-sugerida" href="<?= Helper::url('panel', 'buscar', ['q' => 'acta de comite']) ?>">acta de comite</a>
            <a class="etiqueta-sugerida" href="<?= Helper::url('panel', 'buscar', ['q' => 'politica de datos']) ?>">politica de datos</a>
            <a class="etiqueta-sugerida" href="<?= Helper::url('panel', 'buscar', ['q' => 'concepto juridico']) ?>">concepto juridico</a>
        </div>
    </div>

<?php else: ?>

    <div class="resultados__cabecera">
        <div>
            <h2 class="resultados__titulo">
                <?php if ($termino !== ''): ?>
                    Resultados para «<?= Helper::e($termino) ?>»
                <?php else: ?>
                    Documentos filtrados
                <?php endif; ?>
            </h2>
            <p class="resultados__resumen">
                <?= (int) $paginacion['total'] ?> documento(s) encontrado(s)
                <?php if ($tiempoMs > 0): ?>
                    · consulta procesada en <?= (int) $tiempoMs ?> ms
                <?php endif; ?>
                <?php if (count($filtros) > 1): ?>
                    · con filtros aplicados
                <?php endif; ?>
            </p>
        </div>

        <?php if (!empty($_SESSION['ultima_consulta_id'])): ?>
            <a class="enlace"
               href="<?= Helper::url('panel', 'detalle-consulta', ['id' => $_SESSION['ultima_consulta_id']]) ?>">
                Ver esta consulta registrada
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($resultados)): ?>

        <!-- Regla de negocio: si no hay informacion suficiente se informa (RF-020) -->
        <div class="aviso aviso--advertencia aviso--grande">
            <strong class="aviso__titulo">No se encontro informacion suficiente</strong>
            <p class="aviso__texto">
                Ningun documento autorizado para su perfil coincide con
                «<strong><?= Helper::e($termino) ?></strong>».
            </p>
            <ul class="aviso__lista">
                <li>Verifique la ortografia del termino.</li>
                <li>Pruebe con palabras mas generales o sinonimos.</li>
                <li>Retire algunos filtros avanzados.</li>
                <li>
                    Si el documento deberia existir, solicite su carga al
                    responsable documental de su area.
                </li>
            </ul>
            <p class="aviso__texto">
                Tenga en cuenta que el sistema solo muestra documentos
                autorizados para su rol y area.
            </p>
        </div>

    <?php else: ?>

        <div class="resultados">

            <?php foreach ($resultados as $resultado): ?>
                <?php
                // Se localiza el mejor fragmento disponible para mostrar
                $fuenteTexto = $resultado['texto_extraido']
                            ?? $resultado['descripcion']
                            ?? $resultado['palabra_clave']
                            ?? '';

                $fragmento = $termino !== ''
                    ? Helper::extracto($fuenteTexto, $termino, 320)
                    : mb_substr((string) $fuenteTexto, 0, 320);
                ?>

                <article class="resultado">
                    <div class="resultado__cabecera">
                        <div class="resultado__identificacion">
                            <span class="resultado__codigo"><?= Helper::e($resultado['codigo']) ?></span>
                            <span class="<?= Helper::claseConfidencialidad($resultado['nivel_confidencialidad']) ?>">
                                <?= Helper::e($resultado['nivel_confidencialidad']) ?>
                            </span>
                            <span class="<?= Helper::claseEstado($resultado['estado']) ?>">
                                <?= Helper::e($resultado['estado']) ?>
                            </span>

                            <?php if (!empty($resultado['puntaje'])): ?>
                                <span class="badge badge--puntaje" title="Nivel de coincidencia">
                                    Coincidencia: <?= (int) $resultado['puntaje'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <a class="boton boton--secundario boton--mini"
                           href="<?= Helper::url('documentos', 'ver', ['id' => $resultado['id_documento']]) ?>">
                            Abrir ficha
                        </a>
                    </div>

                    <h3 class="resultado__titulo">
                        <a class="enlace-titulo"
                           href="<?= Helper::url('documentos', 'ver', ['id' => $resultado['id_documento']]) ?>">
                            <?= Helper::e($resultado['titulo']) ?>
                        </a>
                    </h3>

                    <?php if ($fragmento !== ''): ?>
                        <blockquote class="resultado__fragmento">
                            <?= Helper::resaltar($fragmento, $termino) ?>
                        </blockquote>
                    <?php endif; ?>

                    <dl class="resultado__metadatos">
                        <div class="resultado__dato">
                            <dt>Categoria</dt>
                            <dd><?= Helper::e($resultado['categoria']) ?></dd>
                        </div>
                        <div class="resultado__dato">
                            <dt>Area</dt>
                            <dd><?= Helper::eo($resultado['area'], 'Sin area') ?></dd>
                        </div>
                        <div class="resultado__dato">
                            <dt>Caso</dt>
                            <dd><?= Helper::eo($resultado['caso'], 'Sin caso') ?></dd>
                        </div>
                        <div class="resultado__dato">
                            <dt>Responsable</dt>
                            <dd><?= Helper::e($resultado['responsable']) ?></dd>
                        </div>
                        <div class="resultado__dato">
                            <dt>Fecha del documento</dt>
                            <dd><?= Helper::e(Helper::fecha($resultado['fecha_documento'])) ?></dd>
                        </div>
                        <div class="resultado__dato">
                            <dt>Archivo</dt>
                            <dd>
                                <span class="chip-archivo">
                                    <?= Helper::e(Helper::iconoArchivo((string) $resultado['extension'])) ?>
                                </span>
                                <?= Helper::eo($resultado['nombre_archivo'], 'Sin archivo') ?>
                                <?php if (!empty($resultado['peso_bytes'])): ?>
                                    <span class="texto-mini">
                                        (<?= Helper::e(Helper::pesoLegible((int) $resultado['peso_bytes'])) ?>)
                                    </span>
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>

                    <?php if (!empty($resultado['palabra_clave'])): ?>
                        <div class="resultado__palabras">
                            <?php foreach (explode(',', $resultado['palabra_clave']) as $palabra): ?>
                                <span class="etiqueta"><?= Helper::e(trim($palabra)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Paginacion -->
        <?php if ($paginacion['total_paginas'] > 1): ?>
            <?php
            // Se conservan los parametros actuales de la URL
            $parametrosBase = array_filter([
                'modulo'           => 'panel',
                'accion'           => 'buscar',
                'q'                => $termino,
                'categoria'        => $filtros['id_categoria'] ?? '',
                'area'             => $filtros['id_area'] ?? '',
                'caso'             => $filtros['id_caso'] ?? '',
                'estado'           => $filtros['estado'] ?? '',
                'confidencialidad' => $filtros['confidencialidad'] ?? '',
                'etiqueta'         => $filtros['etiqueta'] ?? '',
                'fecha_desde'      => $filtros['fecha_desde'] ?? '',
                'fecha_hasta'      => $filtros['fecha_hasta'] ?? '',
            ], fn($valor) => $valor !== '' && $valor !== null);
            ?>
            <nav class="paginacion" aria-label="Paginacion de resultados">
                <?php if ($paginacion['pagina_actual'] > 1): ?>
                    <a class="paginacion__enlace"
                       href="index.php?<?= http_build_query(array_merge($parametrosBase, ['pagina' => $paginacion['pagina_actual'] - 1])) ?>">
                        &laquo; Anterior
                    </a>
                <?php endif; ?>

                <span class="paginacion__info">
                    Pagina <?= (int) $paginacion['pagina_actual'] ?>
                    de <?= (int) $paginacion['total_paginas'] ?>
                    (<?= (int) $paginacion['desde'] ?>–<?= (int) $paginacion['hasta'] ?>
                    de <?= (int) $paginacion['total'] ?>)
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

<?php endif; ?>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
