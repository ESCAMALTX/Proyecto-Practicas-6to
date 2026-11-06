<?php
/**
 * Vista: app/views/usuarios/roles.php
 * Matriz de roles y permisos (RF-003).
 */
require RUTA_VISTAS . '/partials/header.php';

// Nombres legibles de los modulos
$nombresModulo = [
    'usuarios'      => 'Usuarios',
    'documentos'    => 'Documentos',
    'categorias'    => 'Categorias',
    'casos'         => 'Casos',
    'auditoria'     => 'Auditoria',
    'respaldo'      => 'Respaldos',
    'configuracion' => 'Configuracion',
    'reportes'      => 'Reportes',
];

// Se indexan los permisos por rol y modulo para consultarlos rapido
$matriz = [];
foreach ($detalle as $idRol => $permisos) {
    foreach ($permisos as $permiso) {
        $matriz[$idRol][$permiso['modulo']] = $permiso;
    }
}
?>

<section class="encabezado-seccion">
    <div>
        <h2 class="encabezado-seccion__titulo">Roles y permisos</h2>
        <p class="encabezado-seccion__meta">
            El nivel de acceso define el alcance general del rol; la matriz
            detalla que puede hacer en cada modulo del sistema.
        </p>
    </div>
</section>

<!-- =================================================================
     TARJETAS DE ROLES
     ================================================================= -->
<section class="metricas">
    <?php foreach ($roles as $rol): ?>
        <article class="metrica metrica--rol">
            <div class="metrica__datos">
                <span class="metrica__valor">Nivel <?= (int) $rol['nivel_acceso'] ?></span>
                <span class="metrica__etiqueta"><?= Helper::e($rol['nombre']) ?></span>
                <span class="texto-mini">
                    <?= (int) ($usuariosPorRol[$rol['id_rol']] ?? 0) ?> usuario(s) asignado(s)
                </span>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<!-- =================================================================
     MATRIZ DE PERMISOS POR ROL
     ================================================================= -->
<?php foreach ($roles as $rol): ?>
    <?php $idRolActual = (int) $rol['id_rol']; ?>

    <section class="tarjeta">
        <div class="tarjeta__cabecera">
            <h3 class="tarjeta__titulo">
                <?= Helper::e($rol['nombre']) ?>
                <span class="badge badge--neutro">Nivel <?= (int) $rol['nivel_acceso'] ?></span>
            </h3>
            <p class="tarjeta__subtitulo"><?= Helper::e((string) $rol['descripcion']) ?></p>
        </div>

        <div class="tarjeta__cuerpo tarjeta__cuerpo--sin-relleno">
            <form method="post" action="<?= Helper::url('roles', 'guardar') ?>">
                <input type="hidden" name="csrf_token" value="<?= Helper::e($csrfToken) ?>">
                <input type="hidden" name="id_rol" value="<?= $idRolActual ?>">

                <div class="tabla-contenedor">
                    <table class="tabla tabla--matriz">
                        <thead>
                            <tr>
                                <th>Modulo</th>
                                <th class="tabla__celda-centrada">Ver</th>
                                <th class="tabla__celda-centrada">Crear</th>
                                <th class="tabla__celda-centrada">Editar</th>
                                <th class="tabla__celda-centrada">Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modulos as $filaModulo): ?>
                                <?php
                                $modulo  = $filaModulo['modulo'];
                                $permiso = $matriz[$idRolActual][$modulo] ?? null;
                                $etiquetaModulo = $nombresModulo[$modulo] ?? ucfirst($modulo);
                                ?>
                                <tr>
                                    <td>
                                        <strong class="texto-fuerte"><?= Helper::e($etiquetaModulo) ?></strong>
                                        <span class="texto-bloque texto-mini texto-mono">
                                            <?= Helper::e($modulo) ?>
                                        </span>
                                    </td>

                                    <?php foreach (['ver', 'crear', 'editar', 'eliminar'] as $accion): ?>
                                        <?php
                                        $columna = 'puede_' . $accion;
                                        $marcado = $permiso && (int) $permiso[$columna] === 1;
                                        ?>
                                        <td class="tabla__celda-centrada">
                                            <label class="interruptor">
                                                <input type="checkbox"
                                                       name="permisos[<?= Helper::e($modulo) ?>][<?= $accion ?>]"
                                                       value="1"
                                                       <?= $marcado ? 'checked' : '' ?>>
                                                <span class="interruptor__marca"></span>
                                            </label>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="tarjeta__pie">
                    <button type="submit" class="boton boton--primario">
                        Guardar permisos de <?= Helper::e($rol['nombre']) ?>
                    </button>
                    <span class="texto-mini">
                        Los cambios se aplican de inmediato y quedan en la auditoria.
                    </span>
                </div>
            </form>
        </div>
    </section>
<?php endforeach; ?>

<!-- =================================================================
     EXPLICACION DE NIVELES
     ================================================================= -->
<section class="tarjeta">
    <div class="tarjeta__cabecera">
        <h3 class="tarjeta__titulo">Como funcionan los niveles de acceso</h3>
    </div>
    <div class="tarjeta__cuerpo">
        <div class="rejilla rejilla--3">
            <div class="nivel">
                <span class="nivel__numero">1</span>
                <h4 class="nivel__titulo">Consulta</h4>
                <ul class="nivel__lista">
                    <li>Ve documentos de su area con nivel publico o interno.</li>
                    <li>Usa el buscador y los filtros avanzados.</li>
                    <li>Consulta su propio historial de busquedas.</li>
                    <li>No carga, edita ni elimina documentos.</li>
                </ul>
            </div>

            <div class="nivel">
                <span class="nivel__numero">2</span>
                <h4 class="nivel__titulo">Gestion</h4>
                <ul class="nivel__lista">
                    <li>Carga documentos y registra nuevas versiones.</li>
                    <li>Ve su area, los documentos internos y los propios.</li>
                    <li>Administra categorias y casos.</li>
                    <li>Consulta la auditoria sin modificarla.</li>
                </ul>
            </div>

            <div class="nivel">
                <span class="nivel__numero">3</span>
                <h4 class="nivel__titulo">Administracion</h4>
                <ul class="nivel__lista">
                    <li>Acceso total a documentos, usuarios y areas.</li>
                    <li>Gestiona la matriz de permisos de cada rol.</li>
                    <li>Genera y restaura respaldos.</li>
                    <li>Unica figura que puede eliminar definitivamente.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php require RUTA_VISTAS . '/partials/footer.php'; ?>
