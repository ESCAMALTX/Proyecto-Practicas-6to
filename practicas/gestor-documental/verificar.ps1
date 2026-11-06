$ErrorActionPreference = 'Continue'

# ---------------------------------------------------------------
# 1. Comprobar sintaxis PHP de todos los archivos del proyecto
# ---------------------------------------------------------------
$php  = 'C:\xampp\php\php.exe'
$base = 'c:\Users\PC\Desktop\6TO\practicas\gestor-documental'

Write-Host '============================================================'
Write-Host ' 1. SINTAXIS PHP'
Write-Host '============================================================'

$errores = 0
$total   = 0
$archivos = Get-ChildItem -Path $base -Recurse -Filter '*.php' -File |
            Where-Object { $_.FullName -notmatch '\\logs\\' }

foreach ($f in $archivos) {
    $total++
    $r = & $php -l $f.FullName 2>&1
    if ($r -notmatch 'No syntax errors') {
        $errores++
        Write-Host ("  ERROR  -> " + $f.FullName) -ForegroundColor Red
        $r | ForEach-Object { Write-Host "          $_" }
    }
}

Write-Host ''
Write-Host ("  Archivos revisados : " + $total)
if ($errores -eq 0) {
    Write-Host '  Resultado          : TODOS SIN ERRORES DE SINTAXIS' -ForegroundColor Green
} else {
    Write-Host ("  Resultado          : " + $errores + " CON ERRORES") -ForegroundColor Red
}
Write-Host ''

# ---------------------------------------------------------------
# 2. Comprobar la carga de la clase Documento y sus metodos
# ---------------------------------------------------------------
Write-Host '============================================================'
Write-Host ' 2. PRUEBA DE LOS METODOS CORREGIDOS (Documento / Catalogo)'
Write-Host '============================================================'

$rutaBase = $base.Replace('\', '/')
& $php (Join-Path $base 'database\prueba_modelos.php') 2>&1
Write-Host ''
