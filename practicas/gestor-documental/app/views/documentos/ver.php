<?php
/**
 * Vista: app/views/documentos/ver.php
 * Ficha completa del documento: metadatos, versiones, integridad,
 * permisos y descargas (RF-013, RF-016, RF-021, RNF-009).
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<section class="encabezado-seccion">
    <div>
        <p class="encabezado-seccion__codigo">
            <span class="texto-mono"><?= Helper::e($documento['codigo']) ?></span>
            <span class="<?= Helper::claseConfidencialidad($documento['nivel_confidencialidad']) ?>">
                <?= Helper::e($documento['nivel_confidencialidad']) ?>
            </span>
            <span class="<?= Helper::claseEstado($documento['estado']) ?>">
                <?= Helper::e($documento['estado']) ?>
            </span>
        </p>
        <h2 class="encabezado-seccion__titulo"><?= Helper::e($documento['titulo']) ?></h2>
        <p class="encabezado-seccion__meta">
            <?= Helper::e($documento['categoria']) ?>
            <?php if (!empty($documento['area'])): ?> · <?= Helper::e($documento['area']) ?><?php endif; ?>
            <?php if (!empty($documento['caso'])): ?>
                · Caso <?= Helper::e($documento['codigo_caso']) ?>
            <?php endif; ?>
            · Version <?= (int) $documento['version_actual'] ?> de <?= (int) $documento['total_versiones'] ?>
            · <?= (int) $documento['total_consultas'] ?> consulta(s)
        </p>
    </div>

    <div class="encabezado-seccion__acciones">
        <?php if ($puedeDescargar && !empty($versionActual)): ?>
            <a class="boton boton--primario"
               href="<?= Helper::url('documentos', 'descargar', ['id' => $documento['id_documento']]) ?>">
                Descargar
            </a>
        <?php endif; ?>

        <?php if (Sesion::puede('documentos', 'editar')): ?>
            <a class="boton boton--secundario"
               href="<?= Helper::url('documentos', 'editar', ['id' => $documento['id_documento']]) ?>">
                Editar metadatos
            </a>
        <?php endif; ?>

        <a class="boton boton--fantasma" href="<?= Helper::url('documentos') ?>">Volver</a>
    </div>
</section>

<!-- =================================================================
     ESTADO DE INTEGRIDAD
     ================================================================= -->
<?php if ($versionActual): ?>
    <div class="alerta <?= $integridadOk ? 'alerta--exito' : 'alerta--error' ?>">
        <span class="alerta__texto">
            <?php if ($integridadOk): ?>
                Verificacion de integridad correcta: el hash SHA-256 del archivo
                coincide con el registrado al momento de la carga.
            <?php else: ?>
                <strong>Alerta de integridad:</strong> el hash del archivo no coincide
                con el registrado. El archivo pudo haber sido modificado. Informe
                al administrador antes de utilizarlo.
            <?php endif; ?>
        </span>
    </div>
<?php endif; ?>

<div class="rejilla rejilla--2">

    <!-- =============================================================
         METADATOS
         ============================================================= -->
    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Metadatos del documento</h3>
        </div>
        <div class="tarjeta__cuerpo">
            <dl class="lista-datos">
                <div class="lista-datos__fila">
                    <dt>Codigo interno</dt>
                    <dd><span class="texto-mono"><?= Helper::e($documento['codigo']) ?></span></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Titulo</dt>
                    <dd><?= Helper::e($documento['titulo']) ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Descripcion</dt>
                    <dd><?= Helper::eo($documento['descripcion'], 'Sin descripcion registrada') ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Categoria</dt>
                    <dd><?= Helper::e($documento['categoria']) ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Area</dt>
                    <dd><?= Helper::eo($documento['area'], 'Sin area asignada') ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Caso asociado</dt>
                    <dd>
                        <?php if (!empty($documento['id_caso'])): ?>
                            <?= Helper::e($documento['codigo_caso']) ?> —
                            <?= Helper::e($documento['caso']) ?>
                        <?php else: ?>
                            Sin caso asociado
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Autor o entidad emisora</dt>
                    <dd><?= Helper::eo($documento['autor'], 'No registrado') ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Fecha del documento</dt>
                    <dd><?= Helper::e(Helper::fecha($documento['fecha_documento'])) ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Nivel de confidencialidad</dt>
                    <dd>
                        <span class="<?= Helper::claseConfidencialidad($documento['nivel_confidencialidad']) ?>">
                            <?= Helper::e($documento['nivel_confidencialidad']) ?>
                        </span>
                    </dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Responsable</dt>
                    <dd><?= Helper::e($documento['responsable']) ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Palabras clave</dt>
                    <dd><?= Helper::eo($documento['palabra_clave'], 'Sin palabras clave') ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Registrado</dt>
                    <dd><?= Helper::e(Helper::fechaHora($documento['fecha_creacion'])) ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Ultima actualizacion</dt>
                    <dd><?= Helper::e(Helper::fechaHora($documento['fecha_actualizacion'])) ?></dd>
                </div>
            </dl>

            <?php if (!empty($etiquetas)): ?>
                <div class="resultado__palabras">
                    <?php foreach ($etiquetas as $etq): ?>
                        <a class="etiqueta"
                           href="<?= Helper::url('documentos', '', ['etiqueta' => $etq['nombre']]) ?>">
                            <?= Helper::e($etq['nombre']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="columna">

        <!-- =========================================================
             ARCHIVO ACTUAL
             ========================================================= -->
        <section class="tarjeta">
            <div class="tarjeta__cabecera">
                <h3 class="tarjeta__titulo">Archivo vigente</h3>
            </div>
            <div class="tarjeta__cuerpo">
                <?php if (!$versionActual): ?>
                    <p class="vacio">
                        Este documento no tiene ninguna version cargada.
                    </p>
                <?php else: ?>
                    <div class="archivo-tarjeta">
                        <div class="archivo-tarjeta__icono">
                            <?= Helper::e(Helper::iconoArchivo((string) $versionActual['extension'])) ?>
                        </div>
                        <div class="archivo-tarjeta__datos">
                            <strong class="archivo-tarjeta__nombre">
                                <?= Helper::e($versionActual['nombre_archivo']) ?>
                            </strong>
                            <span class="archivo-tarjeta__meta">
                                <?= Helper::e(strtoupper((string) $versionActual['extension'])) ?>
                                · <?= Helper::e(Helper::pesoLegible((int) $versionActual['peso_bytes'])) ?>
                                · Version <?= (int) $versionActual['numero_version'] ?>
                                · <?= Helper::e(Helper::fechaHora($versionActual['fecha_carga'])) ?>
                            </span>
                            <span class="texto-mini texto-mono">
                                SHA-256: <?= Helper::e(mb_substr((string) $versionActual['hash_archivo'], 0, 32)) ?>...
                            </span>
                        </div>
                    </div>

                    <div class="formulario__acciones">
                        <?php if ($puedeDescargar): ?>
                            <a class="boton boton--primario"
                               href="<?= Helper::url('documentos', 'descargar', ['id' => $documento['id_documento']]) ?>">
                                Descargar archivo
                            </a>
                        <?php else: ?>
                            <span class="aviso aviso--advertencia aviso--compacto">
                                Su rol puede consultar la ficha pero no descargar el archivo.
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- =========================================================
             NUEVA VERSION
             ========================================================= -->
        <?php if (Sesion::puede('documentos', 'editar')): ?>
            <section class="tarjeta">
                <div class="tarjeta__cabecera">
                    <h3 class="tarjeta__titulo">Cargar una nueva version</h3>
                    <p class="tarjeta__subtitulo">
                        Las versiones anteriores se conservan y pueden consultarse.
                    </p>
                </div>
                <div class="tarjeta__cuerpo">
                    <form method="post" enctype="multipart/form-data"
                          action="<?= Helper::url('documentos', 'nueva-version', ['id' => $documento['id_documento']]) ?>"
                          class="formulario">
                        <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">

                        <div class="campo">
                            <label class="campo__etiqueta" for="archivo_version">Archivo</label>
                            <input type="file" class="campo__control campo__control--archivo"
                                   id="archivo_version" name="archivo"
                                   accept=".pdf,.docx,.doc,.xlsx,.xls,.txt"
                                   data-peso-maximo="<?= (int) PESO_MAXIMO_MB * 1024 * 1024 ?>"
                                   required>
                            <span class="campo__ayuda">
                                Formatos permitidos: PDF, DOCX, DOC, XLSX, XLS y TXT
                                · Maximo <?= (int) PESO_MAXIMO_MB ?> MB
                            </span>
                        </div>

                        <div class="campo">
                            <label class="campo__etiqueta" for="observacion">
                                Motivo del cambio
                            </label>
                            <input type="text" class="campo__control" id="observacion"
                                   name="observacion" maxlength="300"
                                   placeholder="Ejemplo: correccion de la clausula quinta">
                        </div>

                        <button type="submit" class="boton boton--secundario boton--bloque">
                            Registrar nueva version
                        </button>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <!-- =========================================================
             PERMISOS DE ACCESO
             ========================================================= -->
        <?php if (!empty($permisos)): ?>
            <section class="tarjeta">
                <div class="tarjeta__cabecera">
                    <h3 class="tarjeta__titulo">Permisos de acceso</h3>
                    <p class="tarjeta__subtitulo">
                        Documento con acceso restringido.
                    </p>
                </div>
                <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
                    <table class="tabla tabla--compacta">
                        <thead>
                            <tr>
                                <th>Autorizado</th>
                                <th>Tipo</th>
                                <th>Descarga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($permisos as $permiso): ?>
                                <tr>
                                    <td>
                                        <?= Helper::e($permiso['usuario'] ?? $permiso['area'] ?? 'Sin asignar') ?>
                                    </td>
                                    <td class="texto-mini">
                                        <?= $permiso['id_usuario'] ? 'Usuario' : 'Area' ?>
                                    </td>
                                    <td>
                                        <span class="<?= $permiso['puede_descargar']
                                            ? 'badge badge--exito' : 'badge badge--neutro' ?>">
                                            <?= $permiso['puede_descargar'] ? 'Permitida' : 'No permitida' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>

<!-- =================================================================
     HISTORIAL DE VERSIONES
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cabecera">
        <h3 class="tarjeta__titulo">Historial de versiones</h3>
        <p class="tarjeta__subtitulo">
            <?= count($versiones) ?> version(es) registrada(s) para este documento.
        </p>
    </div>
    <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Archivo</th>
                    <th>Peso</th>
                    <th>Cargado por</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Observacion</th>
                    <th class="tabla__celda-acciones">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($versiones as $version): ?>
                    <tr>
                        <td>
                            <strong>v<?= (int) $version['numero_version'] ?></strong>
                            <?php if ($version['es_version_actual']): ?>
                                <span class="badge badge--exito">Vigente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="chip-archivo">
                                <?= Helper::e(Helper::iconoArchivo((string) $version['extension'])) ?>
                            </span>
                            <?= Helper::e(mb_strimwidth($version['nombre_archivo'], 0, 40, '...')) ?>
                        </td>
                        <td class="texto-mini">
                            <?= Helper::e(Helper::pesoLegible((int) $version['peso_bytes'])) ?>
                        </td>
                        <td class="texto-mini"><?= Helper::e($version['usuario_carga']) ?></td>
                        <td class="texto-mini"><?= Helper::e(Helper::fechaHora($version['fecha_carga'])) ?></td>
                        <td>
                            <span class="<?= Helper::claseEstado($version['estado']) ?>">
                                <?= Helper::e($version['estado']) ?>
                            </span>
                        </td>
                        <td class="texto-mini">
                            <?= Helper::eo($version['observacion'], '—') ?>
                        </td>
                        <td class="tabla__celda-acciones">
                            <?php if ($puedeDescargar): ?>
                                <a class="boton boton--fantasma boton--mini"
                                   href="<?= Helper::url('documentos', 'descargar', [
                                       'id'      => $documento['id_documento'],
                                       'version' => $version['id_version'],
                                   ]) ?>">
                                    Descargar
                                </a>
                            <?php else: ?>
                                <span class="texto-mini">Sin permiso</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- =================================================================
     HISTORIAL DE DESCARGAS
     ================================================================= -->
<?php if (!empty($descargas)): ?>
    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Ultimas descargas</h3>
            <p class="tarjeta__subtitulo">
                Todo acceso al archivo queda registrado en la bitacora.
            </p>
        </div>
        <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
            <table class="tabla tabla--compacta">
                <thead>
                    <tr><th>Usuario</th><th>Fecha</th><th>Direccion IP</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($descargas as $descarga): ?>
                        <tr>
                            <td><?= Helper::e($descarga['usuario']) ?></td>
                            <td class="texto-mini"><?= Helper::e(Helper::fechaHora($descarga['fecha_descarga'])) ?></td>
                            <td class="texto-mini"><?= Helper::e((string) $descarga['direccion_ip']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<!-- =================================================================
     ACCIONES SOBRE EL DOCUMENTO
     ================================================================= -->
<?php if (Sesion::puede('documentos', 'eliminar')): ?>
    <section class="tarjeta tarjeta--peligro">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Acciones sobre el documento</h3>
            <p class="tarjeta__subtitulo">
                La eliminacion definitiva solo esta disponible para el
                administrador y queda registrada en la auditoria.
            </p>
        </div>
        <div class="tarjeta__cuerpo">
            <div class="rejilla rejilla--2">

                <form method="post" action="<?= Helper::url('documentos', 'eliminar', ['id' => $documento['id_documento']]) ?>"
                      class="formulario formulario--en-linea"
                      data-confirmar="¿Confirma que desea archivar el documento <?= Helper::e($documento['codigo']) ?>?">
                    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                    <input type="hidden" name="modo" value="archivar">
                    <button type="submit" class="boton boton--aviso boton--bloque">
                        Archivar documento
                    </button>
                    <span class="campo__ayuda">
                        El documento deja de estar disponible pero se conserva
                        para consulta historica y auditoria.
                    </span>
                </form>

                <form method="post" action="<?= Helper::url('documentos', 'eliminar', ['id' => $documento['id_documento']]) ?>"
                      class="formulario formulario--en-linea"
                      data-confirmar="¿Confirma que desea marcar el documento como eliminado?">
                    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                    <input type="hidden" name="modo" value="eliminar">
                    <button type="submit" class="boton boton--peligro boton--bloque">
                        Marcar como eliminado
                    </button>
                    <span class="campo__ayuda">
                        Se conserva toda la trazabilidad y los archivos en el servidor.
                    </span>
                </form>
            </div>

            <?php if (Sesion::nivel() >= 3): ?>
                <hr class="separador">

                <form method="post" action="<?= Helper::url('documentos', 'eliminar', ['id' => $documento['id_documento']]) ?>"
                      class="formulario"
                      data-confirmar="ATENCION: esta accion borra el documento y todos sus archivos de forma permanente. ¿Desea continuar?">
                    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                    <input type="hidden" name="modo" value="definitivo">

                    <div class="aviso aviso--error">
                        <strong class="aviso__titulo">Eliminacion definitiva</strong>
                        <p class="aviso__texto">
                            Esta accion elimina el documento, sus
                            <?= count($versiones) ?> version(es) y los archivos del
                            servidor. No se puede deshacer. Para confirmar, escriba
                            el codigo <strong><?= Helper::e($documento['codigo']) ?></strong>.
                        </p>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="confirmacion">
                            Codigo de confirmacion
                        </label>
                        <input type="text" class="campo__control" id="confirmacion"
                               name="confirmacion" autocomplete="off"
                               placeholder="<?= Helper::e($documento['codigo']) ?>">
                    </div>

                    <button type="submit" class="boton boton--peligro">
                        Eliminar definitivamente
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
