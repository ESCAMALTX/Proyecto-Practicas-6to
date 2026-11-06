<?php
/**
 * Vista: app/views/panel/index.php
 * Panel de control con indicadores (RF-018, RF-028).
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<!-- =================================================================
     SALUDO Y ACCIONES RAPIDAS
     ================================================================= -->
<section class="bienvenida">
    <div class="bienvenida__texto">
        <h2 class="bienvenida__titulo">
            Bienvenido, <?= Helper::e(mb_strimwidth((string) Sesion::dato('nombre_completo'), 0, 40, '...')) ?>
        </h2>
        <p class="bienvenida__descripcion">
            Rol: <strong><?= Helper::e((string) Sesion::dato('rol')) ?></strong>
            · Area: <strong><?= Helper::e((string) Sesion::dato('area', 'Sin area')) ?></strong>
            · Ultimo acceso: <?= Helper::e(Helper::fechaHora(Database::valor(
                'SELECT ultimo_acceso FROM usuarios WHERE id_usuario = ?',
                [Sesion::dato('id_usuario')]
            ))) ?>
        </p>
    </div>

    <div class="bienvenida__acciones">
        <a class="boton boton--primario" href="<?= Helper::url('panel', 'buscar') ?>">
            Consultar documentos
        </a>
        <?php if (Sesion::puede('documentos', 'crear')): ?>
            <a class="boton boton--secundario" href="<?= Helper::url('documentos', 'crear') ?>">
                Cargar documento
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- =============================================================
     TARJETAS DE INDICADORES
     ================================================================= -->
<section class="metricas">

    <article class="metrica">
        <div class="metrica__icono" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg></div>
        <div class="metrica__datos">
            <span class="metrica__valor"><?= (int) $visibles ?></span>
            <span class="metrica__etiqueta">Documentos a su alcance</span>
        </div>
    </article>

    <article class="metrica">
        <div class="metrica__icono" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
        <div class="metrica__datos">
            <span class="metrica__valor"><?= (int) ($totales['publicados'] ?? 0) ?></span>
            <span class="metrica__etiqueta">Publicados</span>
        </div>
    </article>

    <article class="metrica">
        <div class="metrica__icono" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></div>
        <div class="metrica__datos">
            <span class="metrica__valor"><?= (int) ($totales['reservados'] ?? 0) ?></span>
            <span class="metrica__etiqueta">Reservados o confidenciales</span>
        </div>
    </article>

    <article class="metrica">
        <div class="metrica__icono" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg></div>
        <div class="metrica__datos">
            <span class="metrica__valor"><?= (int) ($totales['versiones'] ?? 0) ?></span>
            <span class="metrica__etiqueta">Versiones almacenadas</span>
        </div>
    </article>

    <article class="metrica">
        <div class="metrica__icono" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg></div>
        <div class="metrica__datos">
            <span class="metrica__valor"><?= Helper::e(Helper::pesoLegible((int) ($totales['peso'] ?? 0))) ?></span>
            <span class="metrica__etiqueta">Espacio ocupado</span>
        </div>
    </article>

    <?php if (Sesion::nivel() >= 3 && !empty($indicadores)): ?>
        <article class="metrica metrica--destacada">
            <div class="metrica__icono" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></div>
            <div class="metrica__datos">
                <span class="metrica__valor"><?= (int) ($indicadores['usuarios_activos'] ?? 0) ?></span>
                <span class="metrica__etiqueta">Usuarios activos</span>
            </div>
        </article>

        <article class="metrica metrica--destacada">
            <div class="metrica__icono" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></div>
            <div class="metrica__datos">
                <span class="metrica__valor"><?= (int) ($indicadores['consultas_hoy'] ?? 0) ?></span>
                <span class="metrica__etiqueta">Consultas realizadas hoy</span>
            </div>
        </article>
    <?php endif; ?>
</section>

<!-- =================================================================
     CONTENIDO EN DOS COLUMNAS
     ================================================================= -->
<div class="rejilla rejilla--2">

    <!-- Documentos recientes -->
    <section class="tarjeta">
        <div class="tarjeta__cabecera tarjeta__cabecera--fila">
            <h2 class="tarjeta__titulo">Documentos recientes</h2>
            <a class="enlace" href="<?= Helper::url('documentos') ?>">Ver todos</a>
        </div>
        <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
            <?php if (empty($recientes)): ?>
                <p class="vacio">Todavia no se han registrado documentos.</p>
            <?php else: ?>
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Categoria</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recientes as $doc): ?>
                            <tr>
                                <td>
                                    <strong class="texto-fuerte"><?= Helper::e($doc['codigo']) ?></strong>
                                    <span class="texto-bloque"><?= Helper::e(mb_strimwidth($doc['titulo'], 0, 52, '...')) ?></span>
                                </td>
                                <td><?= Helper::e($doc['categoria']) ?></td>
                                <td>
                                    <span class="<?= Helper::claseEstado($doc['estado']) ?>">
                                        <?= Helper::e($doc['estado']) ?>
                                    </span>
                                </td>
                                <td class="texto-mini"><?= Helper::e(Helper::fecha($doc['fecha_creacion'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>

    <!-- Documentos mas consultados -->
    <section class="tarjeta">
        <div class="tarjeta__cabecera tarjeta__cabecera--fila">
            <h2 class="tarjeta__titulo">Documentos mas consultados</h2>
            <a class="enlace" href="<?= Helper::url('reportes') ?>">Reportes</a>
        </div>
        <div class="tarjeta__cuerpo">
            <?php if (empty($masConsultados)): ?>
                <p class="vacio">Sin datos de consulta todavia.</p>
            <?php else: ?>
                <ol class="ranking">
                    <?php foreach ($masConsultados as $indice => $doc): ?>
                        <li class="ranking__item">
                            <span class="ranking__posicion"><?= $indice + 1 ?></span>
                            <div class="ranking__datos">
                                <strong class="ranking__titulo">
                                    <?= Helper::e(mb_strimwidth($doc['titulo'], 0, 46, '...')) ?>
                                </strong>
                                <span class="ranking__meta">
                                    <?= Helper::e($doc['codigo']) ?> · <?= Helper::e($doc['categoria']) ?>
                                </span>
                            </div>
                            <span class="ranking__valor" title="Consultas registradas">
                                <?= (int) $doc['total_consultas'] ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- =================================================================
     DISTRIBUCION POR CATEGORIA
     ================================================================= -->
<?php if (!empty($porCategoria)): ?>
<section class="tarjeta">
    <div class="tarjeta__cabecera">
        <h2 class="tarjeta__titulo">Distribucion documental por categoria</h2>
        <p class="tarjeta__subtitulo">Cantidad de documentos y consultas acumuladas.</p>
    </div>
    <div class="tarjeta__cuerpo">

        <?php
        // Se calcula el maximo para escalar las barras
        $maximo = max(1, max(array_map(fn($c) => (int) $c['total_documentos'], $porCategoria)));
        ?>

        <div class="grafico-barras">
            <?php foreach ($porCategoria as $cat): ?>
                <?php $porcentaje = round(((int) $cat['total_documentos'] / $maximo) * 100); ?>
                <div class="grafico-barras__fila">
                    <span class="grafico-barras__etiqueta"><?= Helper::e($cat['categoria']) ?></span>
                    <div class="grafico-barras__pista">
                        <div class="grafico-barras__relleno" style="width: <?= $porcentaje ?>%"></div>
                    </div>
                    <span class="grafico-barras__valor">
                        <?= (int) $cat['total_documentos'] ?> doc. ·
                        <?= (int) $cat['total_consultas'] ?> cons.
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- =================================================================
     ACTIVIDAD Y CONSULTAS
     ================================================================= -->
<div class="rejilla rejilla--2">

    <section class="tarjeta">
        <div class="tarjeta__cabecera tarjeta__cabecera--fila">
            <h2 class="tarjeta__titulo">Mis consultas recientes</h2>
            <a class="enlace" href="<?= Helper::url('panel', 'buscar') ?>">Nueva consulta</a>
        </div>
        <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
            <?php if (empty($misConsultas)): ?>
                <p class="vacio">Aun no ha realizado consultas.</p>
            <?php else: ?>
                <ul class="lista-actividad">
                    <?php foreach ($misConsultas as $consulta): ?>
                        <li class="lista-actividad__item">
                            <div class="lista-actividad__contenido">
                                <a class="enlace-fuerte"
                                   href="<?= Helper::url('panel', 'detalle-consulta', ['id' => $consulta['id_consulta']]) ?>">
                                    <?= Helper::e(mb_strimwidth($consulta['texto_consulta'], 0, 60, '...')) ?>
                                </a>
                                <span class="lista-actividad__meta">
                                    <?= Helper::e(Helper::hace($consulta['fecha_consulta'])) ?>
                                    · <?= (int) $consulta['total_resultados'] ?> resultado(s)
                                    · <?= (int) $consulta['tiempo_ms'] ?> ms
                                </span>
                            </div>
                            <span class="<?= $consulta['resultado_encontrado']
                                ? 'badge badge--exito' : 'badge badge--aviso' ?>">
                                <?= $consulta['resultado_encontrado'] ? 'Con resultados' : 'Sin resultados' ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>

    <?php if (Sesion::nivel() >= 3): ?>
        <section class="tarjeta">
            <div class="tarjeta__cabecera tarjeta__cabecera--fila">
                <h2 class="tarjeta__titulo">Actividad reciente del sistema</h2>
                <a class="enlace" href="<?= Helper::url('auditoria') ?>">Ver auditoria</a>
            </div>
            <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
                <ul class="lista-actividad">
                    <?php foreach ($actividad as $evento): ?>
                        <li class="lista-actividad__item">
                            <div class="lista-actividad__contenido">
                                <span class="lista-actividad__texto">
                                    <?= Helper::e(mb_strimwidth((string) $evento['descripcion'], 0, 68, '...')) ?>
                                </span>
                                <span class="lista-actividad__meta">
                                    <?= Helper::e((string) $evento['usuario']) ?>
                                    · <?= Helper::e(Helper::hace($evento['fecha_evento'])) ?>
                                </span>
                            </div>
                            <span class="<?= Helper::claseEstado((string) $evento['resultado']) ?>">
                                <?= Helper::e((string) $evento['accion']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    <?php else: ?>
        <section class="tarjeta">
            <div class="tarjeta__cabecera">
                <h2 class="tarjeta__titulo">Consultas sin resultados</h2>
                <p class="tarjeta__subtitulo">
                    Terminos buscados que no encontraron coincidencias.
                </p>
            </div>
            <div class="tarjeta__cuerpo">
                <?php if (empty($sinResultados)): ?>
                    <p class="vacio">Todas las consultas han encontrado informacion.</p>
                <?php else: ?>
                    <ul class="lista-simple">
                        <?php foreach ($sinResultados as $fila): ?>
                            <li>
                                <span class="texto-fuerte"><?= Helper::e($fila['texto_consulta']) ?></span>
                                <span class="texto-mini"><?= (int) $fila['veces'] ?> vez(ces)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<!-- =================================================================
     NOTIFICACIONES
     ================================================================= -->
<?php if (!empty($notificaciones)): ?>
<section class="tarjeta">
    <div class="tarjeta__cabecera tarjeta__cabecera--fila">
        <h2 class="tarjeta__titulo">Notificaciones sin leer</h2>
        <a class="enlace" href="<?= Helper::url('panel', 'notificaciones') ?>">
            Marcar todas como leidas
        </a>
    </div>
    <div class="tarjeta__cuerpo">
        <ul class="lista-notificaciones">
            <?php foreach ($notificaciones as $notif): ?>
                <li class="lista-notificaciones__item lista-notificaciones__item--<?= strtolower($notif['tipo']) ?>">
                    <strong class="lista-notificaciones__titulo"><?= Helper::e($notif['titulo']) ?></strong>
                    <p class="lista-notificaciones__mensaje"><?= Helper::e($notif['mensaje']) ?></p>
                    <span class="texto-mini"><?= Helper::e(Helper::hace($notif['fecha_creacion'])) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
