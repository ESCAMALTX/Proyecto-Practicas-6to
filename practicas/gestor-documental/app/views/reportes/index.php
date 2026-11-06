<?php
/**
 * Vista: app/views/reportes/index.php
 * Reportes e indicadores exportables (RF-018, RF-025, RF-028).
 */
require RUTA_VISTAS . '/partials/header.php';

// Se calculan los maximos para escalar los graficos de barras
$maxCategoria = !empty($porCategoria)
    ? max(1, max(array_map(fn($c) => (int) $c['total_documentos'], $porCategoria))) : 1;
$maxArea = !empty($porArea)
    ? max(1, max(array_map(fn($a) => (int) $a['total_documentos'], $porArea))) : 1;
$maxDia = !empty($consultasPorDia)
    ? max(1, max(array_map(fn($d) => (int) $d['total'], $consultasPorDia))) : 1;
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Reportes e indicadores</h2>
        <p class="encabezado-seccion__meta">
            Vision consolidada del inventario documental, las consultas y el uso del sistema.
        </p>
    </div>

    <div class="encabezado-seccion__acciones">
        <a class="boton boton--secundario" href="<?= Helper::url('reportes', 'inventario') ?>">
            Inventario CSV
        </a>
        <a class="boton boton--secundario" href="<?= Helper::url('reportes', 'consultas') ?>">
            Consultas CSV
        </a>
        <?php if (Sesion::nivel() >= 3): ?>
            <a class="boton boton--secundario" href="<?= Helper::url('reportes', 'usuarios') ?>">
                Usuarios CSV
            </a>
        <?php endif; ?>
        <a class="boton boton--primario" href="<?= Helper::url('reportes', 'imprimir') ?>"
           target="_blank" rel="noopener">
            Version imprimible
        </a>
    </div>
</section>

<!-- =================================================================
     INDICADORES GENERALES
     ================================================================= -->
<section class="metricas">
    <article class="metrica">
        <div class="metrica__icono" aria-hidden="true">&#128196;</div>
        <div class="metrica__datos">
            <span class="metrica__valor"><?= (int) $totales['total'] ?></span>
            <span class="metrica__etiqueta">Documentos registrados</span>
        </div>
    </article>

    <article class="metrica">
        <div class="metrica__icono" aria-hidden="true">&#9989;</div>
        <div class="metrica__datos">
            <span class="metrica__valor"><?= (int) $totales['publicados'] ?></span>
            <span class="metrica__etiqueta">Publicados</span>
        </div>
    </article>

    <article class="metrica">
        <div class="metrica__icono" aria-hidden="true">&#128202;</div>
        <div class="metrica__datos">
            <span class="metrica__valor"><?= (int) $totales['versiones'] ?></span>
            <span class="metrica__etiqueta">Versiones almacenadas</span>
        </div>
    </article>

    <article class="metrica">
        <div class="metrica__icono" aria-hidden="true">&#128190;</div>
        <div class="metrica__datos">
            <span class="metrica__valor"><?= Helper::e(Helper::pesoLegible((int) $totales['peso'])) ?></span>
            <span class="metrica__etiqueta">Espacio ocupado</span>
        </div>
    </article>

    <article class="metrica metrica--destacada">
        <div class="metrica__icono" aria-hidden="true">&#9201;</div>
        <div class="metrica__datos">
            <span class="metrica__valor"><?= (int) $tiempoPromedio ?> ms</span>
            <span class="metrica__etiqueta">Tiempo promedio de consulta</span>
        </div>
    </article>
</section>

<!-- =================================================================
     DISTRIBUCIONES
     ================================================================= -->
<div class="rejilla rejilla--2">

    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Documentos por categoria</h3>
        </div>
        <div class="tarjeta__cuerpo">
            <?php if (empty($porCategoria)): ?>
                <p class="vacio">Sin documentos registrados.</p>
            <?php else: ?>
                <div class="grafico-barras">
                    <?php foreach ($porCategoria as $cat): ?>
                        <?php $porcentaje = round(((int) $cat['total_documentos'] / $maxCategoria) * 100); ?>
                        <div class="grafico-barras__fila">
                            <span class="grafico-barras__etiqueta"><?= Helper::e($cat['categoria']) ?></span>
                            <div class="grafico-barras__pista">
                                <div class="grafico-barras__relleno" style="width: <?= $porcentaje ?>%"></div>
                            </div>
                            <span class="grafico-barras__valor">
                                <?= (int) $cat['total_documentos'] ?> /
                                <?= (int) $cat['publicados'] ?> pub.
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Documentos por area</h3>
        </div>
        <div class="tarjeta__cuerpo">
            <?php if (empty($porArea)): ?>
                <p class="vacio">Sin areas registradas.</p>
            <?php else: ?>
                <div class="grafico-barras">
                    <?php foreach ($porArea as $area): ?>
                        <?php $porcentaje = round(((int) $area['total_documentos'] / $maxArea) * 100); ?>
                        <div class="grafico-barras__fila">
                            <span class="grafico-barras__etiqueta"><?= Helper::e($area['area']) ?></span>
                            <div class="grafico-barras__pista">
                                <div class="grafico-barras__relleno grafico-barras__relleno--alt"
                                     style="width: <?= $porcentaje ?>%"></div>
                            </div>
                            <span class="grafico-barras__valor">
                                <?= (int) $area['total_documentos'] ?>
                                <?php if ((int) $area['documentos_reservados'] > 0): ?>
                                    / <?= (int) $area['documentos_reservados'] ?> res.
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="rejilla rejilla--2">

    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Documentos por nivel de confidencialidad</h3>
        </div>
        <div class="tarjeta__cuerpo">
            <?php if (empty($porNivel)): ?>
                <p class="vacio">Sin datos disponibles.</p>
            <?php else: ?>
                <div class="tabla-contenedor">
                    <table class="tabla tabla--compacta">
                        <thead>
                            <tr><th>Nivel</th><th class="tabla__celda-centrada">Documentos</th><th>Proporcion</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalNiveles = array_sum(array_column($porNivel, 'total')) ?: 1;
                            foreach ($porNivel as $fila):
                                $porcentaje = round(((int) $fila['total'] / $totalNiveles) * 100, 1);
                            ?>
                                <tr>
                                    <td>
                                        <span class="<?= Helper::claseConfidencialidad($fila['nivel_confidencialidad']) ?>">
                                            <?= Helper::e($fila['nivel_confidencialidad']) ?>
                                        </span>
                                    </td>
                                    <td class="tabla__celda-centrada"><?= (int) $fila['total'] ?></td>
                                    <td class="texto-mini"><?= $porcentaje ?> %</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Documentos por estado</h3>
        </div>
        <div class="tarjeta__cuerpo">
            <?php if (empty($porEstado)): ?>
                <p class="vacio">Sin datos disponibles.</p>
            <?php else: ?>
                <div class="tabla-contenedor">
                    <table class="tabla tabla--compacta">
                        <thead>
                            <tr><th>Estado</th><th class="tabla__celda-centrada">Documentos</th><th>Proporcion</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalEstados = array_sum(array_column($porEstado, 'total')) ?: 1;
                            foreach ($porEstado as $fila):
                                $porcentaje = round(((int) $fila['total'] / $totalEstados) * 100, 1);
                            ?>
                                <tr>
                                    <td>
                                        <span class="<?= Helper::claseEstado($fila['estado']) ?>">
                                            <?= Helper::e($fila['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="tabla__celda-centrada"><?= (int) $fila['total'] ?></td>
                                    <td class="texto-mini"><?= $porcentaje ?> %</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- =================================================================
     CONSULTAS
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cabecera">
        <h3 class="tarjeta__titulo">Consultas en los ultimos 14 dias</h3>
        <p class="tarjeta__subtitulo">
            Tiempo promedio de respuesta: <?= (int) $tiempoPromedio ?> ms.
        </p>
    </div>
    <div class="tarjeta__cuerpo">
        <?php if (empty($consultasPorDia)): ?>
            <p class="vacio">No se registran consultas en el periodo.</p>
        <?php else: ?>
            <div class="grafico-columnas">
                <?php foreach ($consultasPorDia as $dia): ?>
                    <?php $altura = round(((int) $dia['total'] / $maxDia) * 100); ?>
                    <div class="grafico-columnas__columna">
                        <span class="grafico-columnas__valor"><?= (int) $dia['total'] ?></span>
                        <div class="grafico-columnas__barra" style="height: <?= max(4, $altura) ?>%"></div>
                        <span class="grafico-columnas__etiqueta">
                            <?= Helper::e(date('d/m', strtotime($dia['dia']))) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="rejilla rejilla--2">

    <!-- Terminos mas buscados -->
    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Terminos mas consultados</h3>
        </div>
        <div class="tarjeta__cuerpo">
            <?php if (empty($masFrecuentes)): ?>
                <p class="vacio">Sin consultas registradas.</p>
            <?php else: ?>
                <ol class="ranking">
                    <?php foreach ($masFrecuentes as $indice => $fila): ?>
                        <li class="ranking__item">
                            <span class="ranking__posicion"><?= $indice + 1 ?></span>
                            <div class="ranking__datos">
                                <strong class="ranking__titulo">
                                    <?= Helper::e(mb_strimwidth($fila['texto_consulta'], 0, 46, '...')) ?>
                                </strong>
                            </div>
                            <span class="ranking__valor"><?= (int) $fila['veces'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </section>

    <!-- Documentos mas descargados -->
    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Documentos mas descargados</h3>
        </div>
        <div class="tarjeta__cuerpo">
            <?php if (empty($descargas)): ?>
                <p class="vacio">Sin descargas registradas.</p>
            <?php else: ?>
                <ol class="ranking">
                    <?php foreach ($descargas as $indice => $fila): ?>
                        <li class="ranking__item">
                            <span class="ranking__posicion"><?= $indice + 1 ?></span>
                            <div class="ranking__datos">
                                <strong class="ranking__titulo">
                                    <?= Helper::e(mb_strimwidth($fila['titulo'], 0, 44, '...')) ?>
                                </strong>
                                <span class="ranking__meta"><?= Helper::e($fila['codigo']) ?></span>
                            </div>
                            <span class="ranking__valor"><?= (int) $fila['total_descargas'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="rejilla rejilla--2">

    <!-- Documentos mas consultados -->
    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Documentos mas consultados</h3>
        </div>
        <div class="tarjeta__cuerpo">
            <?php if (empty($masConsultados)): ?>
                <p class="vacio">Sin datos de consulta.</p>
            <?php else: ?>
                <ol class="ranking">
                    <?php foreach ($masConsultados as $indice => $doc): ?>
                        <li class="ranking__item">
                            <span class="ranking__posicion"><?= $indice + 1 ?></span>
                            <div class="ranking__datos">
                                <strong class="ranking__titulo">
                                    <?= Helper::e(mb_strimwidth($doc['titulo'], 0, 44, '...')) ?>
                                </strong>
                                <span class="ranking__meta">
                                    <?= Helper::e($doc['codigo']) ?> · <?= Helper::e($doc['categoria']) ?>
                                </span>
                            </div>
                            <span class="ranking__valor"><?= (int) $doc['total_consultas'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </section>

    <!-- Consultas sin resultados: vacios de informacion -->
    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Consultas sin resultados</h3>
            <p class="tarjeta__subtitulo">
                Terminos solicitados que no encontraron documentos. Indican
                vacios de informacion que conviene revisar.
            </p>
        </div>
        <div class="tarjeta__cuerpo">
            <?php if (empty($sinResultados)): ?>
                <p class="vacio">
                    Todas las consultas han encontrado informacion. Buen indicador
                    de cobertura documental.
                </p>
            <?php else: ?>
                <div class="tabla-contenedor">
                    <table class="tabla tabla--compacta">
                        <thead>
                            <tr><th>Termino buscado</th><th class="tabla__celda-centrada">Veces</th><th>Ultima vez</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sinResultados as $fila): ?>
                                <tr>
                                    <td class="texto-mini">
                                        <?= Helper::e(mb_strimwidth($fila['texto_consulta'], 0, 52, '...')) ?>
                                    </td>
                                    <td class="tabla__celda-centrada"><?= (int) $fila['veces'] ?></td>
                                    <td class="texto-mini"><?= Helper::e(Helper::hace($fila['ultima'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- =================================================================
     USUARIOS MAS ACTIVOS
     ================================================================= -->
<?php if (!empty($usuariosActivos)): ?>
    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Usuarios con mas consultas</h3>
        </div>
        <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
            <table class="tabla">
                <thead>
                    <tr><th>Usuario</th><th>Rol</th><th class="tabla__celda-centrada">Consultas</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($usuariosActivos as $fila): ?>
                        <tr>
                            <td><strong class="texto-fuerte"><?= Helper::e($fila['nombre_completo']) ?></strong></td>
                            <td>
                                <span class="badge badge--rol"><?= Helper::e($fila['rol']) ?></span>
                            </td>
                            <td class="tabla__celda-centrada"><?= (int) $fila['total'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
