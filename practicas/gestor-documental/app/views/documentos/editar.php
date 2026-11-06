<?php
/**
 * Vista: app/views/documentos/editar.php
 * Edicion de metadatos del documento (RF-016).
 */
require RUTA_VISTAS . '/partials/header.php';

$etiquetasSeleccionadas = implode(', ', $etiquetasDoc);
$idsUsuariosPermitidos   = array_column($permisosUsuario, 'id_usuario');
$idsAreasPermitidas      = array_column($permisosArea, 'id_area');
?>

<section class="encabezado-seccion">
    <div>
        <p class="encabezado-seccion__codigo">
            <span class="texto-mono"><?= Helper::e($documento['codigo']) ?></span>
        </p>
        <h2 class="encabezado-seccion__titulo">Editar metadatos</h2>
        <p class="encabezado-seccion__meta">
            Los cambios quedan registrados en la bitacora de auditoria con
            el estado anterior y el nuevo.
        </p>
    </div>

    <div class="encabezado-seccion__acciones">
        <a class="boton boton--fantasma"
           href="<?= Helper::url('documentos', 'ver', ['id' => $documento['id_documento']]) ?>">
            Volver a la ficha
        </a>
    </div>
</section>

<?php if (!empty($errores['general'])): ?>
    <div class="alerta alerta--error" role="alert">
        <span class="alerta__texto"><?= Helper::e($errores['general']) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($errores) && empty($errores['general'])): ?>
    <div class="alerta alerta--error" role="alert">
        <strong class="alerta__titulo">Revise los siguientes campos:</strong>
        <ul class="alerta__lista">
            <?php foreach ($errores as $error): ?>
                <li><?= Helper::e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= Helper::url('documentos', 'editar', ['id' => $documento['id_documento']]) ?>"
      class="formulario" novalidate>
    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">

    <div class="rejilla rejilla--2">

        <section class="tarjeta">
            <div class="tarjeta__cabecera">
                <h3 class="tarjeta__titulo">Identificacion</h3>
            </div>

            <div class="tarjeta__cuerpo">
                <div class="campo">
                    <label class="campo__etiqueta" for="titulo">
                        Titulo <span class="campo__obligatorio">*</span>
                    </label>
                    <input type="text"
                           class="campo__control <?= isset($errores['titulo']) ? 'campo__control--error' : '' ?>"
                           id="titulo" name="titulo" maxlength="250"
                           value="<?= Helper::e($datos['titulo'] ?? $documento['titulo']) ?>"
                           required>
                    <?php if (isset($errores['titulo'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['titulo']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="descripcion">Descripcion</label>
                    <textarea class="campo__control" id="descripcion" name="descripcion"
                              rows="4" maxlength="2000"><?= Helper::e($datos['descripcion'] ?? $documento['descripcion']) ?></textarea>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="palabra_clave">Palabras clave</label>
                    <input type="text" class="campo__control" id="palabra_clave"
                           name="palabra_clave" maxlength="300"
                           value="<?= Helper::e($datos['palabra_clave'] ?? $documento['palabra_clave']) ?>">
                    <span class="campo__ayuda">Separe cada palabra con una coma.</span>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="autor">Autor o entidad emisora</label>
                    <input type="text" class="campo__control" id="autor" name="autor"
                           maxlength="150"
                           value="<?= Helper::e($datos['autor'] ?? $documento['autor']) ?>">
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="fecha_documento">
                        Fecha del documento <span class="campo__obligatorio">*</span>
                    </label>
                    <input type="date"
                           class="campo__control <?= isset($errores['fecha_documento']) ? 'campo__control--error' : '' ?>"
                           id="fecha_documento" name="fecha_documento"
                           value="<?= Helper::e($datos['fecha_documento'] ?? $documento['fecha_documento']) ?>"
                           required>
                    <?php if (isset($errores['fecha_documento'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['fecha_documento']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="etiquetas">Etiquetas</label>
                    <input type="text" class="campo__control" id="etiquetas" name="etiquetas"
                           maxlength="300"
                           value="<?= Helper::e($datos['etiquetas'] ?? $etiquetasSeleccionadas) ?>">
                    <span class="campo__ayuda">Separe cada etiqueta con una coma.</span>
                </div>
            </div>
        </section>

        <div class="columna">
            <section class="tarjeta">
                <div class="tarjeta__cabecera">
                    <h3 class="tarjeta__titulo">Clasificacion y estado</h3>
                </div>

                <div class="tarjeta__cuerpo">
                    <div class="campo">
                        <label class="campo__etiqueta" for="id_categoria">
                            Categoria <span class="campo__obligatorio">*</span>
                        </label>
                        <select class="campo__control <?= isset($errores['id_categoria']) ? 'campo__control--error' : '' ?>"
                                id="id_categoria" name="id_categoria" required>
                            <option value="">Seleccione una categoria...</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= (int) $cat['id_categoria'] ?>"
                                    <?= (int) ($datos['id_categoria'] ?? $documento['id_categoria']) === (int) $cat['id_categoria'] ? 'selected' : '' ?>>
                                    <?= Helper::e($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errores['id_categoria'])): ?>
                            <span class="campo__error"><?= Helper::e($errores['id_categoria']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="id_area">Area</label>
                        <select class="campo__control" id="id_area" name="id_area">
                            <option value="">Sin area especifica</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= (int) $area['id_area'] ?>"
                                    <?= (int) ($datos['id_area'] ?? $documento['id_area']) === (int) $area['id_area'] ? 'selected' : '' ?>>
                                    <?= Helper::e($area['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="id_caso">Caso asociado</label>
                        <select class="campo__control" id="id_caso" name="id_caso">
                            <option value="">Sin caso asociado</option>
                            <?php foreach ($casos as $caso): ?>
                                <option value="<?= (int) $caso['id_caso'] ?>"
                                    <?= (int) ($datos['id_caso'] ?? $documento['id_caso']) === (int) $caso['id_caso'] ? 'selected' : '' ?>>
                                    <?= Helper::e($caso['codigo'] . ' — ' . mb_strimwidth($caso['titulo'], 0, 40, '...')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="nivel_confidencialidad">
                            Nivel de confidencialidad <span class="campo__obligatorio">*</span>
                        </label>
                        <select class="campo__control" id="nivel_confidencialidad"
                                name="nivel_confidencialidad"
                                data-confidencialidad="1" required>
                            <?php
                            $niveles = [
                                'PUBLICO'      => 'Publico — cualquier usuario autenticado',
                                'INTERNO'      => 'Interno — usuarios del area',
                                'RESERVADO'    => 'Reservado — requiere permiso explicito',
                                'CONFIDENCIAL' => 'Confidencial — acceso restringido',
                            ];
                            $nivelActual = $datos['nivel_confidencialidad'] ?? $documento['nivel_confidencialidad'];
                            foreach ($niveles as $valor => $etiqueta):
                            ?>
                                <option value="<?= $valor ?>" <?= $nivelActual === $valor ? 'selected' : '' ?>>
                                    <?= Helper::e($etiqueta) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (in_array($documento['nivel_confidencialidad'], ['RESERVADO', 'CONFIDENCIAL'], true)): ?>
                        <div class="campo" id="panelPermisos">
                            <div class="aviso aviso--info">
                                <strong class="aviso__titulo">Permisos vigentes</strong>
                                <p class="aviso__texto">
                                    Al guardar se reemplazan los permisos actuales por
                                    los que marque en esta seccion.
                                </p>
                            </div>

                            <label class="campo__etiqueta">Areas autorizadas</label>
                            <div class="lista-checkbox">
                                <?php foreach ($areas as $area): ?>
                                    <label class="checkbox">
                                        <input type="checkbox" name="areas_permitidas[]"
                                               value="<?= (int) $area['id_area'] ?>"
                                            <?= in_array((int) $area['id_area'], array_map('intval', $idsAreasPermitidas), true) ? 'checked' : '' ?>>
                                        <span><?= Helper::e($area['nombre']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <label class="checkbox checkbox--destacado">
                                <input type="checkbox" name="permiso_descargar" value="1">
                                <span>Permitir tambien la descarga del archivo</span>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="campo">
                        <label class="campo__etiqueta" for="estado">
                            Estado <span class="campo__obligatorio">*</span>
                        </label>
                        <select class="campo__control" id="estado" name="estado" required>
                            <?php
                            $estados = [
                                'BORRADOR'   => 'Borrador — solo visible para el responsable',
                                'EN_PROCESO' => 'En proceso — pendiente de revision',
                                'PUBLICADO'  => 'Publicado — disponible para consulta',
                                'ARCHIVADO'  => 'Archivado — conservado sin consulta',
                            ];
                            $estadoActual = $datos['estado'] ?? $documento['estado'];
                            foreach ($estados as $valor => $etiqueta):
                            ?>
                                <option value="<?= $valor ?>" <?= $estadoActual === $valor ? 'selected' : '' ?>>
                                    <?= Helper::e($etiqueta) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </section>

            <section class="tarjeta">
                <div class="tarjeta__cabecera">
                    <h3 class="tarjeta__titulo">Archivo</h3>
                    <p class="tarjeta__subtitulo">
                        Los metadatos se editan aqui. Para reemplazar el archivo,
                        use la opcion "Cargar una nueva version" en la ficha.
                    </p>
                </div>
                <div class="tarjeta__cuerpo">
                    <?php $archivoVigente = Documento::versionActual((int) $documento['id_documento']); ?>
                    <?php if ($archivoVigente): ?>
                        <div class="archivo-tarjeta">
                            <div class="archivo-tarjeta__icono">
                                <?= Helper::e(Helper::iconoArchivo((string) $archivoVigente['extension'])) ?>
                            </div>
                            <div class="archivo-tarjeta__datos">
                                <strong class="archivo-tarjeta__nombre">
                                    <?= Helper::e($archivoVigente['nombre_archivo']) ?>
                                </strong>
                                <span class="archivo-tarjeta__meta">
                                    v<?= (int) $archivoVigente['numero_version'] ?>
                                    · <?= Helper::e(Helper::pesoLegible((int) $archivoVigente['peso_bytes'])) ?>
                                    · <?= Helper::e(Helper::fechaHora($archivoVigente['fecha_carga'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="vacio">Este documento no tiene archivo cargado.</p>
                    <?php endif; ?>

                    <a class="boton boton--secundario boton--bloque"
                       href="<?= Helper::url('documentos', 'ver', ['id' => $documento['id_documento']]) ?>">
                        Ir a la ficha para cargar una version
                    </a>
                </div>
            </section>

            <div class="formulario__acciones formulario__acciones--destacadas">
                <button type="submit" class="boton boton--primario boton--grande">
                    Guardar cambios
                </button>
                <a class="boton boton--fantasma boton--grande"
                   href="<?= Helper::url('documentos', 'ver', ['id' => $documento['id_documento']]) ?>">
                    Cancelar
                </a>
            </div>
        </div>
    </div>
</form>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
