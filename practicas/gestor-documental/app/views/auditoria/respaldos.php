<?php
/**
 * Vista: app/views/auditoria/respaldos.php
 * Copias de seguridad de la base de datos (RF-019, RNF-011, RNF-016).
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Copias de seguridad</h2>
        <p class="encabezado-seccion__meta">
            <?= count($respaldos) ?> respaldo(s) registrado(s)
            · <?= Helper::e(Helper::pesoLegible((int) $pesoTotal)) ?> en disco
            · Politica: RPO 24 h / RTO 4 h
        </p>
    </div>
</section>

<div class="rejilla rejilla--2">

    <!-- =============================================================
         GENERAR RESPALDO
         ============================================================= -->
    <?php if (Sesion::puede('respaldo', 'crear')): ?>
        <section class="tarjeta">
            <div class="tarjeta__cabecera">
                <h3 class="tarjeta__titulo">Generar un respaldo</h3>
                <p class="tarjeta__subtitulo">
                    Se exporta la base de datos a un archivo SQL descargable.
                </p>
            </div>

            <div class="tarjeta__cuerpo">
                <form method="post" action="<?= Helper::url('respaldos', 'generar') ?>"
                      class="formulario"
                      data-confirmar="La generacion del respaldo puede tardar unos segundos. ¿Desea continuar?">
                    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">

                    <div class="campo">
                        <label class="campo__etiqueta" for="tipo">Tipo de respaldo</label>
                        <select class="campo__control" id="tipo" name="tipo">
                            <option value="COMPLETO">
                                Completo — estructura de tablas y datos
                            </option>
                            <option value="SOLO_DATOS">
                                Solo datos — sin sentencias CREATE TABLE
                            </option>
                        </select>
                    </div>

                    <div class="aviso aviso--info">
                        <strong class="aviso__titulo">Que incluye el respaldo</strong>
                        <ul class="aviso__lista">
                            <li>Usuarios, roles y permisos.</li>
                            <li>Documentos, versiones y metadatos.</li>
                            <li>Categorias, casos y etiquetas.</li>
                            <li>Consultas, auditoria y configuracion.</li>
                        </ul>
                        <p class="aviso__texto">
                            Los archivos cargados no se incluyen: residen en la
                            carpeta <code>public/uploads</code> y deben respaldarse
                            aparte copiando esa carpeta.
                        </p>
                    </div>

                    <button type="submit" class="boton boton--primario boton--bloque">
                        Generar respaldo ahora
                    </button>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <!-- =============================================================
         ARCHIVOS EN DISCO
         ============================================================= -->
    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Archivos en el servidor</h3>
            <p class="tarjeta__subtitulo">
                Carpeta <code>respaldos/</code> del proyecto.
            </p>
        </div>

        <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
            <?php if (empty($enDisco)): ?>
                <p class="vacio">No hay archivos de respaldo en el servidor.</p>
            <?php else: ?>
                <table class="tabla tabla--compacta">
                    <thead>
                        <tr><th>Archivo</th><th>Peso</th><th>Fecha</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enDisco as $archivo): ?>
                            <tr>
                                <td class="texto-mono texto-mini"><?= Helper::e($archivo['nombre']) ?></td>
                                <td class="texto-mini"><?= Helper::e(Helper::pesoLegible((int) $archivo['peso'])) ?></td>
                                <td class="texto-mini"><?= Helper::e(Helper::fechaHora($archivo['fecha'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- =================================================================
     HISTORIAL DE RESPALDOS
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cabecera">
        <h3 class="tarjeta__titulo">Historial de respaldos</h3>
    </div>

    <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
        <?php if (empty($respaldos)): ?>
            <div class="estado-vacio estado-vacio--compacto">
                <div class="estado-vacio__icono" aria-hidden="true">&#128190;</div>
                <h3 class="estado-vacio__titulo">Todavia no se han generado respaldos</h3>
                <p class="estado-vacio__texto">
                    Se recomienda generar un respaldo antes de cada cambio importante
                    y mantener copias fuera del servidor.
                </p>
            </div>
        <?php else: ?>
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Archivo</th>
                            <th>Tipo</th>
                            <th>Peso</th>
                            <th>Generado por</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="tabla__celda-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($respaldos as $respaldo): ?>
                            <tr>
                                <td class="texto-mono texto-mini">
                                    <?= Helper::e($respaldo['nombre_archivo']) ?>
                                </td>
                                <td>
                                    <span class="badge badge--neutro">
                                        <?= Helper::e($respaldo['tipo']) ?>
                                    </span>
                                </td>
                                <td class="texto-mini">
                                    <?= Helper::e(Helper::pesoLegible((int) $respaldo['peso_bytes'])) ?>
                                </td>
                                <td class="texto-mini"><?= Helper::e($respaldo['usuario']) ?></td>
                                <td class="texto-mini">
                                    <?= Helper::e(Helper::fechaHora($respaldo['fecha_respaldo'])) ?>
                                </td>
                                <td>
                                    <span class="<?= Helper::claseEstado($respaldo['estado']) ?>">
                                        <?= Helper::e($respaldo['estado']) ?>
                                    </span>
                                    <?php if (!empty($respaldo['observacion'])): ?>
                                        <span class="texto-bloque texto-mini">
                                            <?= Helper::e(mb_strimwidth($respaldo['observacion'], 0, 46, '...')) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="tabla__celda-acciones">
                                    <div class="acciones-fila">
                                        <?php if ($respaldo['estado'] === 'COMPLETADO'): ?>
                                            <a class="boton boton--secundario boton--mini"
                                               href="<?= Helper::url('respaldos', 'descargar', ['id' => $respaldo['id_respaldo']]) ?>">
                                                Descargar
                                            </a>
                                        <?php endif; ?>

                                        <?php if (Sesion::nivel() >= 3): ?>
                                            <form method="post" action="<?= Helper::url('respaldos', 'eliminar') ?>"
                                                  class="formulario--en-linea"
                                                  data-confirmar="¿Eliminar el respaldo <?= Helper::e($respaldo['nombre_archivo']) ?> de forma permanente?">
                                                <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $respaldo['id_respaldo'] ?>">
                                                <button type="submit" class="boton boton--peligro boton--mini">
                                                    Eliminar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- =================================================================
     COMO RESTAURAR
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cabecera">
        <h3 class="tarjeta__titulo">Como restaurar un respaldo</h3>
    </div>
    <div class="tarjeta__cuerpo">
        <ol class="lista-numerada">
            <li>Descargue el archivo <code>.sql</code> desde esta pantalla.</li>
            <li>
                Abra <strong>MySQL Workbench</strong> y conectese al servidor local.
            </li>
            <li>
                En el menu superior elija <em>Server → Data Import</em>.
            </li>
            <li>
                Seleccione <em>Import from Self-Contained File</em> y ubique el
                archivo descargado.
            </li>
            <li>
                En <em>Default Target Schema</em> escriba
                <code><?= Helper::e(DB_NOMBRE) ?></code>.
            </li>
            <li>Presione <em>Start Import</em> y espere a que finalice.</li>
            <li>
                Verifique la restauracion ingresando al sistema y revisando el
                inventario documental.
            </li>
        </ol>

        <div class="aviso aviso--advertencia">
            <strong class="aviso__titulo">Antes de restaurar</strong>
            <p class="aviso__texto">
                La importacion de un respaldo reemplaza el contenido actual de la
                base de datos. Genere un respaldo del estado actual antes de
                restaurar uno antiguo. Los archivos de la carpeta
                <code>public/uploads</code> no se reemplazan automaticamente.
            </p>
        </div>

        <div class="aviso aviso--info">
            <strong class="aviso__titulo">Respaldo desde linea de comandos</strong>
            <p class="aviso__texto">
                Tambien puede generar respaldos desde la terminal con
                <code>mysqldump</code>, lo que resulta util para programarlos
                como tarea automatica:
            </p>
            <pre class="bloque-codigo">mysqldump -u root -p --routines --triggers --events <?= Helper::e(DB_NOMBRE) ?> &gt; respaldo_completo.sql</pre>
            <p class="aviso__texto">
                Para restaurarlo:
            </p>
            <pre class="bloque-codigo">mysql -u root -p <?= Helper::e(DB_NOMBRE) ?> &lt; respaldo_completo.sql</pre>
        </div>
    </div>
</section>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
