<?php
/**
 * Vista: app/views/documentos/crear.php
 * Formulario de carga documental (RF-004, RF-005, RF-006, RF-029).
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Cargar un documento</h2>
        <p class="encabezado-seccion__meta">
            Complete los metadatos y adjunte el archivo. El sistema validara
            el formato, el peso y la integridad antes de registrarlo.
        </p>
    </div>
    <a class="boton boton--fantasma" href="<?= Helper::url('documentos') ?>">Cancelar</a>
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

<form method="post" action="<?= Helper::url('documentos', 'crear') ?>"
      enctype="multipart/form-data" class="formulario formulario--tarjeta" novalidate>
    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">

    <div class="rejilla rejilla--2">

        <!-- =========================================================
             COLUMNA 1: METADATOS
             ========================================================= -->
        <section class="tarjeta">
            <div class="tarjeta__cabecera">
                <h3 class="tarjeta__titulo">Identificacion del documento</h3>
                <p class="tarjeta__subtitulo">
                    El codigo interno se genera automaticamente al guardar.
                </p>
            </div>

            <div class="tarjeta__cuerpo">
                <div class="campo">
                    <label class="campo__etiqueta" for="titulo">
                        Titulo del documento <span class="campo__obligatorio">*</span>
                    </label>
                    <input type="text"
                           class="campo__control <?= isset($errores['titulo']) ? 'campo__control--error' : '' ?>"
                           id="titulo" name="titulo" maxlength="250"
                           value="<?= Helper::e($datos['titulo'] ?? '') ?>"
                           placeholder="Ejemplo: Contrato de prestacion de servicios 2026"
                           required>
                    <?php if (isset($errores['titulo'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['titulo']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="descripcion">Descripcion o resumen</label>
                    <textarea class="campo__control" id="descripcion" name="descripcion"
                              rows="4" maxlength="2000"
                              placeholder="Breve resumen del contenido del documento"><?= Helper::e($datos['descripcion'] ?? '') ?></textarea>
                    <span class="campo__ayuda">
                        Este texto alimenta el buscador y ayuda a otros usuarios a
                        identificar el documento.
                    </span>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="palabra_clave">
                        Palabras clave
                    </label>
                    <input type="text" class="campo__control" id="palabra_clave"
                           name="palabra_clave" maxlength="300"
                           value="<?= Helper::e($datos['palabra_clave'] ?? '') ?>"
                           placeholder="contrato, aseo, servicios, 2026">
                    <span class="campo__ayuda">Separe cada palabra con una coma.</span>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="autor">Autor o entidad emisora</label>
                    <input type="text" class="campo__control" id="autor" name="autor"
                           maxlength="150"
                           value="<?= Helper::e($datos['autor'] ?? '') ?>"
                           placeholder="Ejemplo: Oficina Juridica">
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="fecha_documento">
                        Fecha del documento <span class="campo__obligatorio">*</span>
                    </label>
                    <input type="date"
                           class="campo__control <?= isset($errores['fecha_documento']) ? 'campo__control--error' : '' ?>"
                           id="fecha_documento" name="fecha_documento"
                           max="<?= date('Y-m-d') ?>"
                           value="<?= Helper::e($datos['fecha_documento'] ?? date('Y-m-d')) ?>"
                           required>
                    <?php if (isset($errores['fecha_documento'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['fecha_documento']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- =========================================================
             COLUMNA 2: CLASIFICACION Y ARCHIVO
             ========================================================= -->
        <div class="columna">

            <section class="tarjeta">
                <div class="tarjeta__cabecera">
                    <h3 class="tarjeta__titulo">Clasificacion y acceso</h3>
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
                                    <?= (int) ($datos['id_categoria'] ?? 0) === (int) $cat['id_categoria'] ? 'selected' : '' ?>>
                                    <?= Helper::e($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errores['id_categoria'])): ?>
                            <span class="campo__error"><?= Helper::e($errores['id_categoria']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="id_area">Area responsable</label>
                        <select class="campo__control" id="id_area" name="id_area">
                            <option value="">Sin area especifica</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= (int) $area['id_area'] ?>"
                                    <?= (int) ($datos['id_area'] ?? Sesion::dato('id_area')) === (int) $area['id_area'] ? 'selected' : '' ?>>
                                    <?= Helper::e($area['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="id_caso">Caso o expediente asociado</label>
                        <select class="campo__control" id="id_caso" name="id_caso">
                            <option value="">Sin caso asociado</option>
                            <?php foreach ($casos as $caso): ?>
                                <option value="<?= (int) $caso['id_caso'] ?>"
                                    <?= (int) ($datos['id_caso'] ?? 0) === (int) $caso['id_caso'] ? 'selected' : '' ?>>
                                    <?= Helper::e($caso['codigo'] . ' — ' . mb_strimwidth($caso['titulo'], 0, 42, '...')) ?>
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
                            foreach ($niveles as $valor => $etiqueta):
                            ?>
                                <option value="<?= $valor ?>"
                                    <?= ($datos['nivel_confidencialidad'] ?? 'INTERNO') === $valor ? 'selected' : '' ?>>
                                    <?= Helper::e($etiqueta) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Permisos: solo se muestran para documentos reservados -->
                    <div class="campo campo--oculto" id="panelPermisos">
                        <div class="aviso aviso--info">
                            <strong class="aviso__titulo">Permisos de acceso</strong>
                            <p class="aviso__texto">
                                Este documento tendra acceso restringido. Indique
                                que areas o usuarios podran consultarlo.
                            </p>
                        </div>

                        <label class="campo__etiqueta">Areas autorizadas</label>
                        <div class="lista-checkbox">
                            <?php foreach ($areas as $area): ?>
                                <label class="checkbox">
                                    <input type="checkbox" name="areas_permitidas[]"
                                           value="<?= (int) $area['id_area'] ?>">
                                    <span><?= Helper::e($area['nombre']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <label class="checkbox checkbox--destacado">
                            <input type="checkbox" name="permiso_descargar" value="1">
                            <span>Permitir tambien la descarga del archivo</span>
                        </label>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="estado">
                            Estado inicial <span class="campo__obligatorio">*</span>
                        </label>
                        <select class="campo__control" id="estado" name="estado" required>
                            <option value="BORRADOR"
                                <?= ($datos['estado'] ?? 'BORRADOR') === 'BORRADOR' ? 'selected' : '' ?>>
                                Borrador — solo visible para el responsable
                            </option>
                            <option value="EN_PROCESO"
                                <?= ($datos['estado'] ?? '') === 'EN_PROCESO' ? 'selected' : '' ?>>
                                En proceso — pendiente de revision
                            </option>
                            <option value="PUBLICADO"
                                <?= ($datos['estado'] ?? '') === 'PUBLICADO' ? 'selected' : '' ?>>
                                Publicado — disponible para consulta
                            </option>
                        </select>
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="etiquetas">Etiquetas</label>
                        <input type="text" class="campo__control" id="etiquetas"
                               name="etiquetas" maxlength="300"
                               value="<?= Helper::e($datos['etiquetas'] ?? '') ?>"
                               placeholder="urgente, vigente, revision-2026">
                        <span class="campo__ayuda">
                            Separe cada etiqueta con una coma. Si no existe, se creara.
                        </span>
                    </div>
                </div>
            </section>

            <section class="tarjeta">
                <div class="tarjeta__cabecera">
                    <h3 class="tarjeta__titulo">
                        Archivo <span class="campo__obligatorio">*</span>
                    </h3>
                </div>

                <div class="tarjeta__cuerpo">
                    <div class="zona-archivo <?= isset($errores['archivo']) ? 'zona-archivo--error' : '' ?>"
                         id="zonaArchivo">
                        <input type="file" id="archivo" name="archivo"
                               class="zona-archivo__entrada"
                               accept=".pdf,.docx,.doc,.xlsx,.xls,.txt"
                               data-peso-maximo="<?= (int) PESO_MAXIMO_MB * 1024 * 1024 ?>"
                               required>

                        <label for="archivo" class="zona-archivo__etiqueta">
                            <span class="zona-archivo__icono" aria-hidden="true">&#128206;</span>
                            <span class="zona-archivo__texto">
                                Haga clic o arrastre el archivo aqui
                            </span>
                            <span class="zona-archivo__ayuda">
                                Formatos permitidos: PDF, DOCX, DOC, XLSX, XLS y TXT
                                · Peso maximo: <?= (int) PESO_MAXIMO_MB ?> MB
                            </span>
                        </label>

                        <div class="zona-archivo__seleccion" id="archivoSeleccionado"></div>
                    </div>

                    <?php if (isset($errores['archivo'])): ?>
                        <span class="campo__error"><?= Helper::e($errores['archivo']) ?></span>
                    <?php endif; ?>

                    <div class="aviso aviso--info">
                        <strong class="aviso__titulo">Procesamiento automatico</strong>
                        <ul class="aviso__lista">
                            <li>Se verifica el tipo real del archivo, no solo su extension.</li>
                            <li>Se calcula un hash SHA-256 para comprobar su integridad.</li>
                            <li>Se extrae el texto de PDF, DOCX, XLSX y TXT para el buscador.</li>
                            <li>Se rechaza el archivo si ya existe otro identico en el sistema.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <div class="formulario__acciones formulario__acciones--destacadas">
                <button type="submit" class="boton boton--primario boton--grande">
                    Cargar documento
                </button>
                <a class="boton boton--fantasma boton--grande"
                   href="<?= Helper::url('documentos') ?>">Cancelar</a>
            </div>
        </div>
    </div>
</form>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
