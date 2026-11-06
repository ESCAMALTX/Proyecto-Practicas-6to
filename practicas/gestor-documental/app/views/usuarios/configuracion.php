<?php
/**
 * Vista: app/views/usuarios/configuracion.php
 * Parametros generales del sistema (solo administracion).
 */
require RUTA_VISTAS . '/partials/header.php';
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Configuracion del sistema</h2>
        <p class="encabezado-seccion__meta">
            Parametros generales de operacion. Los cambios se registran en la auditoria.
        </p>
    </div>
</section>

<div class="aviso aviso--info">
    <strong class="aviso__titulo">Importante</strong>
    <p class="aviso__texto">
        Algunos valores, como el peso maximo de archivo y los intentos de inicio
        de sesion, tambien deben ajustarse en la configuracion del servidor
        (<code>php.ini</code>: <code>upload_max_filesize</code>, <code>post_max_size</code>
        y <code>max_execution_time</code>) para que surtan efecto completo.
    </p>
</div>

<form method="post" action="<?= Helper::url('configuracion', 'guardar') ?>" class="formulario">
    <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">

    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Parametros del sistema</h3>
            <p class="tarjeta__subtitulo">
                <?= count($parametros) ?> parametro(s) configurables.
            </p>
        </div>

        <div class="tarjeta__cuerpo">
            <div class="rejilla rejilla--2">
                <?php foreach ($parametros as $parametro): ?>
                    <?php
                    $clave = $parametro['clave'];
                    $valor = $parametro['valor'];

                    // Se elige el tipo de control segun el contenido del parametro
                    $esNumerico  = is_numeric($valor);
                    $esBooleano  = in_array(strtoupper($valor), ['SI', 'NO'], true);
                    $esSensible  = in_array($clave, ['extensiones_permitidas'], true);
                    ?>
                    <div class="campo">
                        <label class="campo__etiqueta" for="cfg_<?= Helper::e($clave) ?>">
                            <?= Helper::e(str_replace('_', ' ', ucfirst($clave))) ?>
                        </label>

                        <?php if ($esBooleano): ?>
                            <select class="campo__control" id="cfg_<?= Helper::e($clave) ?>"
                                    name="config[<?= Helper::e($clave) ?>]">
                                <option value="SI" <?= strtoupper($valor) === 'SI' ? 'selected' : '' ?>>SI</option>
                                <option value="NO" <?= strtoupper($valor) === 'NO' ? 'selected' : '' ?>>NO</option>
                            </select>

                        <?php elseif ($esNumerico): ?>
                            <input type="number" class="campo__control"
                                   id="cfg_<?= Helper::e($clave) ?>"
                                   name="config[<?= Helper::e($clave) ?>]"
                                   value="<?= Helper::e($valor) ?>" min="1" required>

                        <?php else: ?>
                            <input type="text" class="campo__control"
                                   id="cfg_<?= Helper::e($clave) ?>"
                                   name="config[<?= Helper::e($clave) ?>]"
                                   maxlength="300" value="<?= Helper::e($valor) ?>" required>
                        <?php endif; ?>

                        <span class="campo__ayuda">
                            <?= Helper::e((string) $parametro['descripcion']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="formulario__acciones formulario__acciones--destacadas">
                <button type="submit" class="boton boton--primario boton--grande">
                    Guardar configuracion
                </button>
                <a class="boton boton--fantasma boton--grande"
                   href="<?= Helper::url('panel') ?>">Cancelar</a>
            </div>
        </div>
    </section>
</form>

<!-- =================================================================
     INFORMACION DEL ENTORNO
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cabecera">
        <h3 class="tarjeta__titulo">Informacion del entorno</h3>
        <p class="tarjeta__subtitulo">
            Datos tecnicos utiles para diagnostico y soporte.
        </p>
    </div>
    <div class="tarjeta__cuerpo">
        <dl class="lista-datos lista-datos--columnas">
            <div class="lista-datos__fila">
                <dt>Version de PHP</dt>
                <dd><?= Helper::e(PHP_VERSION) ?></dd>
            </div>
            <div class="lista-datos__fila">
                <dt>Servidor web</dt>
                <dd><?= Helper::e($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido') ?></dd>
            </div>
            <div class="lista-datos__fila">
                <dt>Version de MySQL</dt>
                <dd><?= Helper::e((string) Database::valor('SELECT VERSION()')) ?></dd>
            </div>
            <div class="lista-datos__fila">
                <dt>Base de datos</dt>
                <dd><?= Helper::e(DB_NOMBRE) ?></dd>
            </div>
            <div class="lista-datos__fila">
                <dt>Peso maximo de subida (PHP)</dt>
                <dd><?= Helper::e(ini_get('upload_max_filesize')) ?></dd>
            </div>
            <div class="lista-datos__fila">
                <dt>Tamano maximo de POST (PHP)</dt>
                <dd><?= Helper::e(ini_get('post_max_size')) ?></dd>
            </div>
            <div class="lista-datos__fila">
                <dt>Tiempo maximo de ejecucion</dt>
                <dd><?= Helper::e(ini_get('max_execution_time')) ?> s</dd>
            </div>
            <div class="lista-datos__fila">
                <dt>Zona horaria</dt>
                <dd><?= Helper::e(date_default_timezone_get()) ?></dd>
            </div>
            <div class="lista-datos__fila">
                <dt>Extension ZipArchive</dt>
                <dd>
                    <span class="<?= class_exists('ZipArchive') ? 'badge badge--exito' : 'badge badge--error' ?>">
                        <?= class_exists('ZipArchive') ? 'Disponible' : 'No disponible' ?>
                    </span>
                    <span class="texto-mini">Necesaria para la extraccion de DOCX y XLSX</span>
                </dd>
            </div>
            <div class="lista-datos__fila">
                <dt>Extension Fileinfo</dt>
                <dd>
                    <span class="<?= class_exists('finfo') ? 'badge badge--exito' : 'badge badge--error' ?>">
                        <?= class_exists('finfo') ? 'Disponible' : 'No disponible' ?>
                    </span>
                    <span class="texto-mini">Necesaria para validar el tipo real de archivo</span>
                </dd>
            </div>
            <div class="lista-datos__fila">
                <dt>Carpeta de archivos</dt>
                <dd>
                    <span class="<?= is_writable(RUTA_UPLOADS) ? 'badge badge--exito' : 'badge badge--error' ?>">
                        <?= is_writable(RUTA_UPLOADS) ? 'Con permiso de escritura' : 'Sin permiso de escritura' ?>
                    </span>
                </dd>
            </div>
            <div class="lista-datos__fila">
                <dt>Carpeta de respaldos</dt>
                <dd>
                    <span class="<?= is_writable(RUTA_RESPALDOS) ? 'badge badge--exito' : 'badge badge--advertencia' ?>">
                        <?= is_writable(RUTA_RESPALDOS) ? 'Con permiso de escritura' : 'Se creara al generar el primer respaldo' ?>
                    </span>
                </dd>
            </div>
        </dl>
    </div>
</section>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
