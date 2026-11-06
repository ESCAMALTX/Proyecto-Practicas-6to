<?php
/**
 * Prueba directa de los modelos corregidos.
 * Uso: php database/prueba_modelos.php
 */

$base = dirname(__DIR__);

require $base . '/config/config.php';
require RUTA_APP . '/core/Database.php';
require RUTA_APP . '/core/Helper.php';
require RUTA_APP . '/models/Documento.php';
require RUTA_APP . '/models/Catalogo.php';
require RUTA_APP . '/models/Usuario.php';

$asesor = ['id_usuario' => 3, 'nivel_acceso' => 1, 'id_area' => 4];

echo "PRUEBA DE LOS METODOS CORREGIDOS\n";
echo str_repeat('-', 60) . "\n";

// ---------------------------------------------------------------
// 1. Documento::porCategoria()  -> debe incluir borradores
// ---------------------------------------------------------------
$r1      = Documento::porCategoria();
$claves  = $r1 ? implode(', ', array_keys($r1[0])) : '(sin filas)';
$esperado = ['id_categoria', 'categoria', 'total_documentos', 'publicados',
             'borradores', 'archivados', 'total_consultas'];
$faltan  = array_diff($esperado, $r1 ? array_keys($r1[0]) : []);
printf("1. porCategoria      : %d filas\n", count($r1));
printf("   claves            : %s\n", $claves);
if ($faltan) {
    printf("   FALLA             : faltan %s\n", implode(', ', $faltan));
} else {
    echo "   OK                : incluye borradores y archivados\n";
}
echo "\n";

// ---------------------------------------------------------------
// 2. Documento::buscar() con nivel 1 (Asesor)
// ---------------------------------------------------------------
$r2 = Documento::buscar('', [], $asesor, 20);
printf("2. buscar (asesor)   : %d documentos visibles\n", count($r2));
echo "   OK                : sin error de columna ambigua\n\n";

// ---------------------------------------------------------------
// 3. Documento::contarBusqueda() con nivel 1
// ---------------------------------------------------------------
$r3 = Documento::contarBusqueda('', [], $asesor);
printf("3. contarBusqueda    : %d documentos\n", $r3);
echo "   OK                : sin error de columna ambigua\n\n";

// ---------------------------------------------------------------
// 4. Catalogo::casos() con string vacio
// ---------------------------------------------------------------
$r4 = Catalogo::casos('', 'ABIERTO');
printf("4. Catalogo::casos() : %d casos abiertos\n", count($r4));
echo "   OK                : acepta string vacio\n\n";

// ---------------------------------------------------------------
// 5. Usuario::listar() -> intentos_fallidos presente
// ---------------------------------------------------------------
$r5 = Usuario::listar('', 0, '', 50, 0);
printf("5. Usuario::listar   : %d usuarios\n", count($r5));
$u = Usuario::porId(1);
if (isset($u['intentos_fallidos'])) {
    printf("   intentos_fallidos : PRESENTE (valor = %s)\n", $u['intentos_fallidos']);
    echo "   OK                : la vista expone la columna\n";
} else {
    echo "   FALLA             : intentos_fallidos AUSENTE de la vista\n";
}
echo "\n";

// ---------------------------------------------------------------
// 6. Documentos accesibles segun nivel (control de acceso)
// ---------------------------------------------------------------
$comoAdmin   = ['id_usuario' => 1, 'nivel_acceso' => 3, 'id_area' => 1];
$totalAdmin  = Documento::contarBusqueda('', [], $comoAdmin);
$totalAsesor = Documento::contarBusqueda('', [], $asesor);
printf("6. Control de acceso : admin ve %d, asesor ve %d\n", $totalAdmin, $totalAsesor);
if ($totalAsesor <= $totalAdmin) {
    echo "   OK                : el asesor ve igual o menos documentos\n";
} else {
    echo "   FALLA             : el asesor ve mas documentos que el admin\n";
}
echo "\n";

echo str_repeat('=', 60) . "\n";
echo "PRUEBA TERMINADA SIN ERRORES FATALES\n";
echo str_repeat('=', 60) . "\n";
