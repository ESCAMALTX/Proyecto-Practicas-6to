<?php
/**
 * Vista: app/views/reportes/imprimir.php
 * Version imprimible del reporte de indicadores (RF-025).
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Reporte de indicadores · <?= Helper::e(NOMBRE_SISTEMA) ?></title>

    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/formularios.css">
    <link rel="stylesheet" href="assets/css/componentes.css">
    <link rel="stylesheet" href="assets/css/avisos.css">
    <link rel="stylesheet" href="assets/css/impresion.css">
</head>
<body class="impresion">

<div class="impresion__hoja">

    <header class="impresion__cabecera">
        <div class="impresion__marca">
            <div class="marca__icono" aria-hidden="true">GD</div>
            <div>
                <h1 class="impresion__titulo"><?= Helper::e(NOMBRE_SISTEMA) ?></h1>
                <p class="impresion__subtitulo">Reporte de indicadores generales</p>
            </div>
        </div>

        <dl class="impresion__meta">
            <div>
                <dt>Fecha de generacion</dt>
                <dd><?= Helper::e(Helper::fechaHora(date('Y-m-d H:i:s'))) ?></dd>
            </div>
            <div>
                <dt>Generado por</dt>
                <dd><?= Helper::e((string) $generadoPor) ?></dd>
            </div>
            <div>
                <dt>Version del sistema</dt>
                <dd><?= Helper::e(VERSION_SISTEMA) ?></dd>
            </div>
        </dl>
    </header>

    <!-- =============================================================
         RESUMEN GENERAL
         ============================================================= -->
    <section class="impresion__seccion">
        <h2 class="impresion__seccion-titulo">1. Resumen general</h2>

        <table class="tabla tabla--impresion">
            <tbody>
                <tr>
                    <th>Documentos registrados</th>
                    <td><?= (int) $totales['total'] ?></td>
                    <th>Documentos publicados</th>
                    <td><?= (int) $totales['publicados'] ?></td>
                </tr>
                <tr>
                    <th>Versiones almacenadas</th>
                    <td><?= (int) $totales['versiones'] ?></td>
                    <th>Documentos reservados</th>
                    <td><?= (int) $totales['reservados'] ?></td>
                </tr>
                <tr>
                    <th>Espacio ocupado</th>
                    <td><?= Helper::e(Helper::pesoLegible((int) $totales['peso'])) ?></td>
                    <th>Tiempo promedio de consulta</th>
                    <td><?= (int) $tiempoPromedio ?> ms</td>
                </tr>
            </tbody>
        </table>
    </section>

    <!-- =============================================================
         DOCUMENTOS POR CATEGORIA
         ============================================================= -->
    <section class="impresion__seccion">
        <h2 class="impresion__seccion-titulo">2. Documentos por categoria</h2>

        <?php if (empty($porCategoria)): ?>
            <p>Sin documentos registrados.</p>
        <?php else: ?>
            <table class="tabla tabla--impresion">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th class="tabla__celda-centrada">Total</th>
                        <th class="tabla__celda-centrada">Publicados</th>
                        <th class="tabla__celda-centrada">Borradores</th>
                        <th class="tabla__celda-centrada">Archivados</th>
                        <th class="tabla__celda-centrada">Consultas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($porCategoria as $cat): ?>
                        <tr>
                            <td><?= Helper::e($cat['categoria']) ?></td>
                            <td class="tabla__celda-centrada"><?= (int) $cat['total_documentos'] ?></td>
                            <td class="tabla__celda-centrada"><?= (int) $cat['publicados'] ?></td>
                            <td class="tabla__celda-centrada"><?= (int) $cat['borradores'] ?></td>
                            <td class="tabla__celda-centrada"><?= (int) $cat['archivados'] ?></td>
                            <td class="tabla__celda-centrada"><?= (int) $cat['total_consultas'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <!-- =============================================================
         DOCUMENTOS POR AREA
         ============================================================= -->
    <section class="impresion__seccion">
        <h2 class="impresion__seccion-titulo">3. Documentos por area</h2>

        <?php if (empty($porArea)): ?>
            <p>Sin areas registradas.</p>
        <?php else: ?>
            <table class="tabla tabla--impresion">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th class="tabla__celda-centrada">Documentos</th>
                        <th class="tabla__celda-centrada">Reservados o confidenciales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($porArea as $area): ?>
                        <tr>
                            <td><?= Helper::e($area['area']) ?></td>
                            <td class="tabla__celda-centrada"><?= (int) $area['total_documentos'] ?></td>
                            <td class="tabla__celda-centrada"><?= (int) $area['documentos_reservados'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <!-- =============================================================
         NIVEL DE CONFIDENCIALIDAD
         ============================================================= -->
    <section class="impresion__seccion">
        <h2 class="impresion__seccion-titulo">4. Distribucion por nivel de confidencialidad</h2>

        <?php if (empty($porNivel)): ?>
            <p>Sin datos disponibles.</p>
        <?php else: ?>
            <?php $totalNiveles = array_sum(array_column($porNivel, 'total')) ?: 1; ?>
            <table class="tabla tabla--impresion">
                <thead>
                    <tr>
                        <th>Nivel</th>
                        <th class="tabla__celda-centrada">Documentos</th>
                        <th class="tabla__celda-centrada">Proporcion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($porNivel as $fila): ?>
                        <tr>
                            <td><?= Helper::e($fila['nivel_confidencialidad']) ?></td>
                            <td class="tabla__celda-centrada"><?= (int) $fila['total'] ?></td>
                            <td class="tabla__celda-centrada">
                                <?= round(((int) $fila['total'] / $totalNiveles) * 100, 1) ?> %
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <!-- =============================================================
         DOCUMENTOS MAS CONSULTADOS
         ============================================================= -->
    <section class="impresion__seccion">
        <h2 class="impresion__seccion-titulo">5. Documentos mas consultados</h2>

        <?php if (empty($masConsultados)): ?>
            <p>Sin datos de consulta registrados.</p>
        <?php else: ?>
            <table class="tabla tabla--impresion">
                <thead>
                    <tr>
                        <th class="tabla__celda-centrada" style="width:40px">#</th>
                        <th>Codigo</th>
                        <th>Titulo</th>
                        <th>Categoria</th>
                        <th class="tabla__celda-centrada">Consultas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($masConsultados as $indice => $doc): ?>
                        <tr>
                            <td class="tabla__celda-centrada"><?= $indice + 1 ?></td>
                            <td><?= Helper::e($doc['codigo']) ?></td>
                            <td><?= Helper::e($doc['titulo']) ?></td>
                            <td><?= Helper::e($doc['categoria']) ?></td>
                            <td class="tabla__celda-centrada"><?= (int) $doc['total_consultas'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <!-- =============================================================
         FIRMAS
         ============================================================= -->
    <section class="impresion__seccion impresion__firmas">
        <div class="impresion__firma">
            <div class="impresion__firma-linea"></div>
            <span class="impresion__firma-cargo">Responsable documental</span>
        </div>
        <div class="impresion__firma">
            <div class="impresion__firma-linea"></div>
            <span class="impresion__firma-cargo">Administrador del sistema</span>
        </div>
    </section>

    <footer class="impresion__pie">
        <p>
            Documento generado automaticamente por <?= Helper::e(NOMBRE_SISTEMA) ?>
            v<?= Helper::e(VERSION_SISTEMA) ?>.
            La informacion presentada corresponde al estado del sistema en la fecha
            de generacion indicada.
        </p>
        <p class="texto-mini">
            Este reporte puede contener informacion reservada. Su manejo debe
            ajustarse a las politicas internas de confidencialidad.
        </p>
    </footer>
</div>

<div class="impresion__acciones">
    <button type="button" class="boton boton--primario" onclick="window.print()">
        Imprimir o guardar como PDF
    </button>
    <a class="boton boton--fantasma" href="<?= Helper::url('reportes') ?>">Cerrar</a>
</div>

</body>
</html>
