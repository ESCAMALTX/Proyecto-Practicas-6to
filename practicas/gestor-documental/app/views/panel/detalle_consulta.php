<?php
/**
 * Vista: app/views/panel/detalle_consulta.php
 * Detalle de una consulta registrada y sus fuentes (RF-013, RF-015).
 */
require RUTA_VISTAS . '/partials/header.php';

$consulta  = $detalle['consulta'];
$resultados = $detalle['resultados'];
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">
            «<?= Helper::e($consulta['texto_consulta']) ?>»
        </h2>
        <p class="encabezado-seccion__meta">
            Consulta No. <?= (int) $consulta['id_consulta'] ?>
            · <?= Helper::e(Helper::fechaHora($consulta['fecha_consulta'])) ?>
            · Usuario: <?= Helper::e($consulta['usuario']) ?>
            · Tipo: <?= Helper::e($consulta['tipo_consulta']) ?>
            · Tiempo: <?= (int) $consulta['tiempo_ms'] ?> ms
        </p>
    </div>

    <a class="boton boton--fantasma"
       href="<?= Helper::url('panel', 'buscar', ['q' => $consulta['texto_consulta']]) ?>">
        Repetir consulta
    </a>
</section>

<div class="rejilla rejilla--2">

    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Resumen</h3>
        </div>
        <div class="tarjeta__cuerpo">
            <dl class="lista-datos">
                <div class="lista-datos__fila">
                    <dt>Resultados devueltos</dt>
                    <dd><?= (int) $consulta['total_resultados'] ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Se encontro informacion</dt>
                    <dd>
                        <span class="<?= $consulta['resultado_encontrado']
                            ? 'badge badge--exito' : 'badge badge--aviso' ?>">
                            <?= $consulta['resultado_encontrado'] ? 'SI' : 'NO' ?>
                        </span>
                    </dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Direccion IP</dt>
                    <dd><?= Helper::e((string) $consulta['direccion_ip']) ?></dd>
                </div>
                <?php if (!empty($consulta['filtros_aplicados'])): ?>
                    <div class="lista-datos__fila">
                        <dt>Filtros aplicados</dt>
                        <dd><code class="codigo"><?= Helper::e($consulta['filtros_aplicados']) ?></code></dd>
                    </div>
                <?php endif; ?>
            </dl>

            <?php if (!$consulta['resultado_encontrado']): ?>
                <div class="aviso aviso--advertencia">
                    <strong class="aviso__titulo">Consulta sin resultados</strong>
                    <p class="aviso__texto">
                        Esta consulta no encontro documentos autorizados con ese termino.
                        Puede ser util registrarla para solicitar la carga del documento.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Fuentes utilizadas</h3>
            <p class="tarjeta__subtitulo">
                Documentos que el sistema recupero para esta consulta.
            </p>
        </div>
        <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
            <?php if (empty($resultados)): ?>
                <p class="vacio">No se recupero ninguna fuente en esta consulta.</p>
            <?php else: ?>
                <ul class="lista-fuentes">
                    <?php foreach ($resultados as $fuente): ?>
                        <li class="lista-fuentes__item">
                            <div class="lista-fuentes__cabecera">
                                <strong class="lista-fuentes__codigo"><?= Helper::e($fuente['codigo']) ?></strong>
                                <span class="<?= Helper::claseConfidencialidad($fuente['nivel_confidencialidad']) ?>">
                                    <?= Helper::e($fuente['nivel_confidencialidad']) ?>
                                </span>
                                <span class="badge badge--puntaje">
                                    Relevancia: <?= (int) $fuente['relevancia'] ?>
                                </span>
                            </div>

                            <a class="enlace-fuerte"
                               href="<?= Helper::url('documentos', 'ver', ['id' => $fuente['id_documento']]) ?>">
                                <?= Helper::e($fuente['titulo']) ?>
                            </a>

                            <?php if (!empty($fuente['fragmento'])): ?>
                                <blockquote class="resultado__fragmento resultado__fragmento--compacto">
                                    <?= Helper::resaltar($fuente['fragmento'], $consulta['texto_consulta']) ?>
                                </blockquote>
                            <?php endif; ?>

                            <span class="texto-mini">
                                <?= Helper::e($fuente['categoria']) ?>
                                · <?= $fuente['fue_abierto'] ? 'Fuente revisada' : 'Fuente no revisada' ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
