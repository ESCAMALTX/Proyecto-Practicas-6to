$ErrorActionPreference = 'Continue'

$mysql = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe'
$base  = 'c:\Users\PC\Desktop\6TO\practicas\gestor-documental'

Write-Host '============================================================'
Write-Host ' APLICANDO CORRECCIONES A LA BASE DE DATOS gestor_documental'
Write-Host '============================================================'
Write-Host ''

$archivo = Join-Path $base 'database\04_correccion_vistas.sql'
Write-Host "-- Ejecutando: 04_correccion_vistas.sql"
$sql = Get-Content -LiteralPath $archivo -Raw -Encoding UTF8
$salida = $sql | & $mysql -u root -p12345 -h 127.0.0.1 -P 3306 --default-character-set=utf8mb4 2>&1
$salida | Where-Object { $_ -notmatch 'Using a password on the command line' } | ForEach-Object { Write-Host "   $_" }
Write-Host ''

Write-Host '-- Verificando que la columna intentos_fallidos este en la vista'
$consulta = "USE gestor_documental; SELECT COUNT(*) AS tiene_columna FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='gestor_documental' AND TABLE_NAME='v_usuarios_completo' AND COLUMN_NAME='intentos_fallidos';"
$salida = $consulta | & $mysql -u root -p12345 -h 127.0.0.1 -P 3306 --default-character-set=utf8mb4 -N -B 2>&1
$salida | Where-Object { $_ -notmatch 'Using a password on the command line' } | ForEach-Object { Write-Host "   $_" }
Write-Host ''
