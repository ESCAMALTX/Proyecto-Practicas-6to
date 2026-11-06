# Diagnostico del flujo de login: muestra cabeceras y cookies paso a paso
$ErrorActionPreference = 'Continue'

$php    = 'C:\xampp\php\php.exe'
$docRaiz = 'c:\Users\PC\Desktop\6TO\practicas\gestor-documental\public'
$puerto = 8910
while (Get-NetTCPConnection -LocalPort $puerto -ErrorAction SilentlyContinue) { $puerto++ }
$baseUrl = "http://127.0.0.1:$puerto"

$o = Join-Path $env:TEMP "diag_$puerto`_out.log"
$e = Join-Path $env:TEMP "diag_$puerto`_err.log"
$proc = Start-Process -FilePath $php -ArgumentList @('-S', "127.0.0.1:$puerto", '-t', $docRaiz) `
        -WorkingDirectory $docRaiz -RedirectStandardOutput $o -RedirectStandardError $e `
        -PassThru -WindowStyle Hidden

for ($i = 0; $i -lt 40; $i++) {
    Start-Sleep -Milliseconds 250
    if (Get-NetTCPConnection -LocalPort $puerto -State Listen -ErrorAction SilentlyContinue) { break }
}

Write-Host "`n===== 1. GET login =====" -ForegroundColor Cyan
$r1 = Invoke-WebRequest -Uri "$baseUrl/index.php?modulo=auth&accion=login" -SessionVariable ses -UseBasicParsing
Write-Host ("HTTP " + $r1.StatusCode)
Write-Host ("Set-Cookie: " + ($r1.Headers['Set-Cookie'] -join ' | '))

# Extraer token
$token = ''
if ($r1.Content -match 'name="csrf_token"\s+value="([^"]+)"') {
    $token = $Matches[1]
    Write-Host ("Token CSRF: " + $token) -ForegroundColor Green
} else {
    Write-Host "NO SE ENCONTRO EL TOKEN CSRF" -ForegroundColor Red
    # Mostrar la zona del formulario
    $idx = $r1.Content.IndexOf('csrf_token')
    if ($idx -ge 0) { Write-Host ("Contexto: " + $r1.Content.Substring([Math]::Max(0,$idx-60), 160)) }
}

Write-Host "`n===== 2. POST login (admin / Admin123*) =====" -ForegroundColor Cyan
try {
    $r2 = Invoke-WebRequest -Uri "$baseUrl/index.php?modulo=auth&accion=login" -Method POST `
        -Body @{ identificador = 'admin'; clave = 'Admin123*'; csrf_token = $token } `
        -WebSession $ses -MaximumRedirection 0 -UseBasicParsing -ErrorAction Stop
    Write-Host ("HTTP " + $r2.StatusCode)
} catch {
    $wr = $_.Exception.Response
    Write-Host ("HTTP " + [int]$wr.StatusCode)
    Write-Host ("Location: " + $wr.Headers['Location'])
    Write-Host ("Set-Cookie: " + ($wr.Headers['Set-Cookie'] -join ' | '))
}
Write-Host ("Cookies en la sesion: " + (($ses.Cookies.GetCookies($baseUrl) | ForEach-Object { $_.Name + '=' + $_.Value }) -join ' ; '))

Write-Host "`n===== 3. GET panel =====" -ForegroundColor Cyan
try {
    $r3 = Invoke-WebRequest -Uri "$baseUrl/index.php?modulo=panel" -WebSession $ses `
        -MaximumRedirection 0 -UseBasicParsing -ErrorAction Stop
    Write-Host ("HTTP " + $r3.StatusCode + "  (longitud " + $r3.Content.Length + ")")
    if ($r3.Content -match '<title>([^<]+)</title>') { Write-Host ("Titulo: " + $Matches[1]) }
} catch {
    $wr = $_.Exception.Response
    Write-Host ("HTTP " + [int]$wr.StatusCode) -ForegroundColor Red
    Write-Host ("Location: " + $wr.Headers['Location'])
    Write-Host "=> El login NO persistio la sesion"
}

if ($proc -and -not $proc.HasExited) { Stop-Process -Id $proc.Id -Force -ErrorAction SilentlyContinue }
Write-Host "`n===== Log de errores PHP del servidor =====" -ForegroundColor Cyan
if (Test-Path $e) { Get-Content -LiteralPath $e | Select-Object -Last 25 }
Write-Host ''
