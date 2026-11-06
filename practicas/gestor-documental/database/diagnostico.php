<?php
/**
 * Diagnostico: por que el Asesor ve 0 documentos?
 * Uso: php database/diagnostico.php
 */

$base = dirname(__DIR__);

require $base . '/config/config.php';
require RUTA_APP . '/core/Database.php';
require RUTA_APP . '/core/Helper.php';
require RUTA_APP . '/models/Documento.php';

echo "DIAGNOSTICO DE VISIBILIDAD DE DOCUMENTOS\n";
echo str_repeat('=', 70) . "\n\n";

// 1. Que hay realmente en la vista?
$todos = Database::todos(
    'SELECT id_documento, codigo, titulo, estado, nivel_confidencialidad,
            id_area, id_responsable
       FROM v_documentos_completo
      ORDER BY id_documento'
);
printf("1. Total de filas en v_documentos_completo: %d\n\n", count($todos));
printf("   %-4s %-14s %-30s %-12s %-12s %-6s %-6s\n",
       'ID', 'CODIGO', 'TITULO', 'ESTADO', 'NIVEL', 'AREA', 'RESP');
foreach ($todos as $d) {
    printf("   %-4s %-14s %-30s %-12s %-12s %-6s %-6s\n",
        $d['id_documento'],
        mb_substr($d['codigo'], 0, 14),
        mb_substr($d['titulo'], 0, 30),
        $d['estado'],
        $d['nivel_confidencialidad'],
        $d['id_area'] ?? 'NULL',
        $d['id_responsable'] ?? 'NULL'
    );
}
echo "\n";

// 2. Definicion de la vista
echo "2. Definicion de la vista\n";
$def = Database::uno(
    "SELECT VIEW_DEFINITION AS d
       FROM information_schema.VIEWS
      WHERE TABLE_SCHEMA = 'gestor_documental'
        AND TABLE_NAME = 'v_documentos_completo'"
);
echo "   " . preg_replace('/\s+/', ' ', $def['d']) . "\n\n";

// 3. Que usuario asesor existe realmente?
echo "3. Usuarios del sistema\n";
$usuarios = Database::todos(
    'SELECT u.id_usuario, u.usuario, u.estado, u.id_area,
            r.nombre AS rol, r.nivel_acceso
       FROM usuarios u
       INNER JOIN roles r ON r.id_rol = u.id_rol
      ORDER BY u.id_usuario'
);
printf("   %-4s %-12s %-10s %-6s %-14s %-6s\n",
       'ID', 'USUARIO', 'ESTADO', 'AREA', 'ROL', 'NIVEL');
foreach ($usuarios as $u) {
    printf("   %-4s %-12s %-10s %-6s %-14s %-6s\n",
        $u['id_usuario'], $u['usuario'], $u['estado'],
        $u['id_area'] ?? 'NULL', $u['rol'], $u['nivel_acceso']);
}
echo "\n";

// 4. Probar cada usuario contra contar()
echo "4. Documentos visibles por usuario (metodo contar)\n";
foreach ($usuarios as $u) {
    $perfil = [
        'id_usuario'   => (int) $u['id_usuario'],
        'nivel_acceso' => (int) $u['nivel_acceso'],
        'id_area'      => $u['id_area'] !== null ? (int) $u['id_area'] : null,
    ];
    $n = Documento::contar([], $perfil);
    $l = Documento::listar([], $perfil, 50, 0);
    printf("   %-12s (nivel %s, area %-5s) -> %d documentos | listar devuelve %d\n",
        $u['usuario'], $u['nivel_acceso'], $u['id_area'] ?? 'NULL', $n, count($l));
}
echo "\n";

// 5. Que documentos ve cada perfil exactamente
echo "5. Detalle de documentos visibles\n";
foreach ($usuarios as $u) {
    $perfil = [
        'id_usuario'   => (int) $u['id_usuario'],
        'nivel_acceso' => (int) $u['nivel_acceso'],
        'id_area'      => $u['id_area'] !== null ? (int) $u['id_area'] : null,
    ];
    $docs = Documento::listar([], $perfil, 50, 0);
    printf("   %s -> [%s]\n", $u['usuario'],
        implode(', ', array_column($docs, 'codigo')));
}
echo "\n";

// 5. Simular el WHERE del nivel 3 (admin) manualmente
echo "5. Verificacion manual del WHERE para nivel 3\n";
$sql = "SELECT COUNT(*) AS n
          FROM v_documentos_completo d
         WHERE d.estado <> 'ELIMINADO'
           AND (d.id_area = 1 OR d.id_area IS NULL)";
$r = Database::uno($sql);
printf("   Documentos con area=1 o NULL : %d\n", $r['n']);
echo "\n";

echo str_repeat('=', 70) . "\n";
