<?php
/**
 * Vista: app/views/auditoria/index.php
 * Bitacora de auditoria (RF-017, RNF-007, RNF-020).
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Bitacora de auditoria</h2>
        <p class="encabezado-seccion__meta">
            <?= number_format((int) $paginacion['total'], 0, ',', '.') ?> evento(s) registrado(s).
            Los registros se conservan minimo 12 meses.
        </p>
    </div>

    <div class="encabezado-seccion__acciones">
        <a class="boton boton--secundario"
           href="<?= Helper::url('auditoria', 'exportar', array_filter([
               'usuario'     => $filtros['usuario'] ?? '',
               'modulo'      => $filtros['modulo'] ?? '',
               'accion'      => $filtros['accion'] ?? '',
               'resultado'   => $filtros['resultado'] ?? '',
               'fecha_desde' => $filtros['fecha_desde'] ?? '',
               'fecha_hasta' => $filtros['fecha_hasta'] ?? '',
               'q'           => $filtros['busqueda'] ?? '',
           ])) ?>">
            Exportar CSV
        </a>
    </div>
</section>

<!-- =================================================================
     RESUMEN POR MODULO
     ================================================================= -->
<?php if (!empty($resumenModulo)): ?>
    <section class="metricas metricas--compactas">
        <?php foreach ($resumenModulo as $fila): ?>
            <article class="metrica">
                <div class="metrica__datos">
                    <span class="metrica__valor"><?= number_format((int) $fila['total'], 0, ',', '.') ?></span>
                    <span class="metrica__etiqueta"><?= Helper::e(ucfirst($fila['modulo'])) ?></span>
                    <?php if ((int) $fila['fallos'] > 0 || (int) $fila['advertencias'] > 0): ?>
                        <span class="texto-mini">
                            <?= (int) $fila['fallos'] ?> fallo(s) ·
                            <?= (int) $fila['advertencias'] ?> advertencia(s)
                        </span>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<!-- =================================================================
     FILTROS
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cuerpo">
        <form method="get" action="index.php" class="formulario">
            <input type="hidden" name="modulo" value="auditoria">

            <div class="formulario__fila formulario__fila--4">
                <div class="campo">
                    <label class="campo__etiqueta" for="usuario">Usuario</label>
                    <input type="text" class="campo__control" id="usuario" name="usuario"
                           value="<?= Helper::e($filtros['usuario'] ?? '') ?>"
                           placeholder="Nombre de usuario">
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="modulo">Modulo</label>
                    <select class="campo__control" id="modulo" name="modulo">
                        <option value="">Todos</option>
                        <?php foreach ($modulos as $fila): ?>
                            <option value="<?= Helper::e($fila['modulo']) ?>"
                                <?= ($filtros['modulo'] ?? '') === $fila['modulo'] ? 'selected' : '' ?>>
                                <?= Helper::e(ucfirst($fila['modulo'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="accion">Accion</label>
                    <select class="campo__control" id="accion" name="accion">
                        <option value="">Todas</option>
                        <?php foreach ($acciones as $fila): ?>
                            <option value="<?= Helper::e($fila['accion']) ?>"
                                <?= ($filtros['accion'] ?? '') === $fila['accion'] ? 'selected' : '' ?>>
                                <?= Helper::e($fila['accion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="resultado">Resultado</label>
                    <select class="campo__control" id="resultado" name="resultado">
                        <option value="">Todos</option>
                        <?php foreach (['EXITO', 'FALLO', 'ADVERTENCIA'] as $opcion): ?>
                            <option value="<?= $opcion ?>"
                                <?= ($filtros['resultado'] ?? '') === $opcion ? 'selected' : '' ?>>
                                <?= Helper::e($opcion) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="formulario__fila formulario__fila--4">
                <div class="campo">
                    <label class="campo__etiqueta" for="fecha_desde">Desde</label>
                    <input type="date" class="campo__control" id="fecha_desde"
                           name="fecha_desde" value="<?= Helper::e($filtros['fecha_desde'] ?? '') ?>">
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="fecha_hasta">Hasta</label>
                    <input type="date" class="campo__control" id="fecha_hasta"
                           name="fecha_hasta" value="<?= Helper::e($filtros['fecha_hasta'] ?? '') ?>">
                </div>

                <div class="campo campo--amplio">
                    <label class="campo__etiqueta" for="q">Descripcion</label>
                    <input type="search" class="campo__control" id="q" name="q"
                           value="<?= Helper::e($filtros['busqueda'] ?? '') ?>"
                           placeholder="Texto contenido en la descripcion">
                </div>

                <div class="campo campo--acciones">
                    <button type="submit" class="boton boton--primario">Filtrar</button>
                    <a class="boton boton--fantasma" href="<?= Helper::url('auditoria') ?>">Limpiar</a>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- =================================================================
     TABLA DE EVENTOS
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">

        <?php if (empty($eventos)): ?>
            <div class="estado-vacio estado-vacio--compacto">
                <div class="estado-vacio__icono" aria-hidden="true">&#128220;</div>
                <h3 class="estado-vacio__titulo">Sin eventos registrados</h3>
                <p class="estado-vacio__texto">
                    No hay registros de auditoria que coincidan con los filtros.
                </p>
            </div>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table class="tabla tabla--interactiva">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Modulo</th>
                            <th>Accion</th>
                            <th>Descripcion</th>
                            <th>IP</th>
                            <th>Resultado</th>
                            <th class="tabla__celda-acciones"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventos as $evento): ?>
                            <tr>
                                <td class="texto-mini">
                                    <?= Helper::e(Helper::fechaHora($evento['fecha_evento'])) ?>
                                </td>
                                <td class="texto-mini"><?= Helper::e((string) $evento['usuario']) ?></td>
                                <td>
                                    <span class="badge badge--neutro">
                                        <?= Helper::e((string) $evento['modulo']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge--accion">
                                        <?= Helper::e((string) $evento['accion']) ?>
                                    </span>
                                </td>
                                <td class="texto-mini">
                                    <?= Helper::e(mb_strimwidth((string) $evento['descripcion'], 0, 78, '...')) ?>
                                </td>
                                <td class="texto-mini texto-mono">
                                    <?= Helper::e((string) $evento['direccion_ip']) ?>
                                </td>
                                <td>
                                    <span class="<?= Helper::claseEstado((string) $evento['resultado']) ?>">
                                        <?= Helper::e((string) $evento['resultado']) ?>
                                    </span>
                                </td>
                                <td class="tabla__celda-acciones">
                                    <a class="boton boton--fantasma boton--mini"
                                       href="<?= Helper::url('auditoria', 'detalle', ['id' => $evento['id_auditoria']]) ?>">
                                        Detalle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($paginacion['total_paginas'] > 1): ?>
                <?php $parametrosAuditoria = array_merge(
                    array_filter($filtros, fn($v) => $v !== ''),
                    ['modulo' => 'auditoria']
                ); ?>
                <nav class="paginacion">
                    <?php if ($paginacion['pagina_actual'] > 1): ?>
                        <a class="paginacion__enlace"
                           href="index.php?<?= http_build_query(array_merge($parametrosAuditoria, ['pagina' => $paginacion['pagina_actual'] - 1])) ?>">
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
                           href="index.php?<?= http_build_query(array_merge($parametrosAuditoria, ['pagina' => $paginacion['pagina_actual'] + 1])) ?>">
                            Siguiente &raquo;
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<div class="rejilla rejilla--2">

    <!-- Eventos de seguridad -->
    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Eventos de seguridad recientes</h3>
            <p class="tarjeta__subtitulo">
                Inicios de sesion, cierres y accesos denegados.
            </p>
        </div>
        <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
            <?php if (empty($eventosSeguridad)): ?>
                <p class="vacio">Sin eventos de seguridad registrados.</p>
            <?php else: ?>
                <ul class="lista-actividad">
                    <?php foreach ($eventosSeguridad as $evento): ?>
                        <li class="lista-actividad__item">
                            <div class="lista-actividad__contenido">
                                <span class="lista-actividad__texto">
                                    <?= Helper::e(mb_strimwidth((string) $evento['descripcion'], 0, 66, '...')) ?>
                                </span>
                                <span class="lista-actividad__meta">
                                    <?= Helper::e((string) $evento['usuario']) ?>
                                    · <?= Helper::e(Helper::hace($evento['fecha_evento'])) ?>
                                </span>
                            </div>
                            <span class="<?= Helper::claseEstado((string) $evento['resultado']) ?>">
                                <?= Helper::e((string) $evento['resultado']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>

    <!-- Usuarios mas activos -->
    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Usuarios con mas actividad</h3>
        </div>
        <div class="tarjeta__cuerpo">
            <?php if (empty($masActivos)): ?>
                <p class="vacio">Sin datos de actividad.</p>
            <?php else: ?>
                <ol class="ranking">
                    <?php foreach ($masActivos as $indice => $fila): ?>
                        <li class="ranking__item">
                            <span class="ranking__posicion"><?= $indice + 1 ?></span>
                            <div class="ranking__datos">
                                <strong class="ranking__titulo"><?= Helper::e((string) $fila['usuario']) ?></strong>
                                <span class="ranking__meta">
                                    Ultima actividad <?= Helper::e(Helper::hace($fila['ultima_actividad'])) ?>
                                </span>
                            </div>
                            <span class="ranking__valor"><?= (int) $fila['total_eventos'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- =================================================================
     POLITICA DE RETENCION
     ================================================================= -->
<?php if (Sesion::nivel() >= 3): ?>
    <section class="tarjeta tarjeta--peligro">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Politica de retencion</h3>
            <p class="tarjeta__subtitulo">
                La norma interna exige conservar los registros minimo 12 meses.
                Esta accion elimina los eventos anteriores al periodo indicado.
            </p>
        </div>
        <div class="tarjeta__cuerpo">
            <form method="post" action="<?= Helper::url('auditoria', 'depurar') ?>"
                  class="formulario formulario--en-linea-resumen"
                  data-confirmar="Esta accion elimina registros de auditoria de forma permanente. ¿Desea continuar?">
                <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">

                <div class="campo" style="max-width:240px">
                    <label class="campo__etiqueta" for="meses">Conservar los ultimos</label>
                    <select class="campo__control" id="meses" name="meses">
                        <option value="12">12 meses (minimo)</option>
                        <option value="24">24 meses</option>
                        <option value="36">36 meses</option>
                        <option value="60">60 meses</option>
                    </select>
                </div>

                <button type="submit" class="boton boton--peligro">
                    Depurar registros antiguos
                </button>
            </form>
        </div>
    </section>
<?php endif; ?>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
