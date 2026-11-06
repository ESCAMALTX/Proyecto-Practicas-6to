<?php
/**
 * Vista: app/views/auditoria/detalle.php
 * Detalle de un evento de auditoria con los cambios registrados.
 */
require RUTA_VISTAS . '/partials/header.php';

// Se intentan decodificar los JSON de cambio para mostrarlos en tabla
$antes   = !empty($evento['datos_antes'])
    ? json_decode($evento['datos_antes'], true) : null;
$despues = !empty($evento['datos_despues'])
    ? json_decode($evento['datos_despues'], true) : null;

/**
 * Renderiza un arreglo de cambios como lista de definiciones.
 */
$renderCambios = function (?array $datos) {
    if (empty($datos) || !is_array($datos)) {
        echo '<p class="vacio">Sin datos registrados.</p>';
        return;
    }

    echo '<dl class="lista-datos lista-datos--compacta">';
    foreach ($datos as $clave => $valor) {
        $texto = is_array($valor)
            ? implode(', ', array_map(fn($v) => is_scalar($v) ? (string) $v : json_encode($v), $valor))
            : (string) $valor;
        echo '<div class="lista-datos__fila">';
        echo '<dt>' . Helper::e(str_replace('_', ' ', ucfirst((string) $clave))) . '</dt>';
        echo '<dd>' . Helper::eo($texto, '—') . '</dd>';
        echo '</div>';
    }
    echo '</dl>';
};
?>

<section class="encabezado-seccion">
    <div>
        <p class="encabezado-seccion__codigo">
            <span class="badge badge--accion"><?= Helper::e((string) $evento['accion']) ?></span>
            <span class="<?= Helper::claseEstado((string) $evento['resultado']) ?>">
                <?= Helper::e((string) $evento['resultado']) ?>
            </span>
        </p>
        <h2 class="encabezado-seccion__titulo">Evento No. <?= (int) $evento['id_auditoria'] ?></h2>
        <p class="encabezado-seccion__meta">
            <?= Helper::e(Helper::fechaHora($evento['fecha_evento'])) ?>
            · Modulo <?= Helper::e((string) $evento['modulo']) ?>
            <?php if (!empty($evento['entidad'])): ?>
                · <?= Helper::e((string) $evento['entidad']) ?>
                <?php if (!empty($evento['id_entidad'])): ?> #<?= (int) $evento['id_entidad'] ?><?php endif; ?>
            <?php endif; ?>
        </p>
    </div>

    <a class="boton boton--fantasma" href="<?= Helper::url('auditoria') ?>">Volver a la bitacora</a>
</section>

<div class="rejilla rejilla--2">

    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">Informacion del evento</h3>
        </div>
        <div class="tarjeta__cuerpo">
            <dl class="lista-datos">
                <div class="lista-datos__fila">
                    <dt>Fecha y hora</dt>
                    <dd><?= Helper::e(Helper::fechaHora($evento['fecha_evento'])) ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Usuario</dt>
                    <dd>
                        <?= Helper::e((string) ($evento['usuario_nombre'] ?? $evento['usuario_texto'] ?? 'Sistema')) ?>
                        <?php if (!empty($evento['usuario_texto'])): ?>
                            <span class="texto-mini texto-mono">
                                (<?= Helper::e((string) $evento['usuario_texto']) ?>)
                            </span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Correo</dt>
                    <dd><?= Helper::eo($evento['correo'], '—') ?></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Modulo</dt>
                    <dd><span class="badge badge--neutro"><?= Helper::e((string) $evento['modulo']) ?></span></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Accion</dt>
                    <dd><span class="badge badge--accion"><?= Helper::e((string) $evento['accion']) ?></span></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Entidad afectada</dt>
                    <dd>
                        <?= Helper::eo($evento['entidad'], 'No aplica') ?>
                        <?php if (!empty($evento['id_entidad'])): ?>
                            <span class="texto-mini">#<?= (int) $evento['id_entidad'] ?></span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Direccion IP</dt>
                    <dd><span class="texto-mono"><?= Helper::e((string) $evento['direccion_ip']) ?></span></dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Resultado</dt>
                    <dd>
                        <span class="<?= Helper::claseEstado((string) $evento['resultado']) ?>">
                            <?= Helper::e((string) $evento['resultado']) ?>
                        </span>
                    </dd>
                </div>
                <div class="lista-datos__fila">
                    <dt>Descripcion</dt>
                    <dd><?= Helper::e((string) $evento['descripcion']) ?></dd>
                </div>
            </dl>

            <?php if (!empty($evento['agente_usuario'])): ?>
                <div class="campo">
                    <label class="campo__etiqueta">Navegador del cliente</label>
                    <p class="texto-mini texto-mono">
                        <?= Helper::e((string) $evento['agente_usuario']) ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="columna">
        <section class="tarjeta">
            <div class="tarjeta__cabecera">
                <h3 class="tarjeta__titulo">Estado anterior</h3>
            </div>
            <div class="tarjeta__cuerpo">
                <?php $renderCambios($antes); ?>
            </div>
        </section>

        <section class="tarjeta">
            <div class="tarjeta__cabecera">
                <h3 class="tarjeta__titulo">Estado nuevo</h3>
            </div>
            <div class="tarjeta__cuerpo">
                <?php $renderCambios($despues); ?>
            </div>
        </section>
    </div>
</div>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
