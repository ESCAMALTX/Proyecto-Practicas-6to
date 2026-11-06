# =====================================================================
#  PRUEBA DE EXTREMO A EXTREMO (E2E)
#  Sistema Privado de Gestion y Consulta Documental
#
#  Uso:
#     powershell -NoProfile -ExecutionPolicy Bypass -File pruebas\e2e.ps1
#
#  El script:
#    1. Arranca el servidor PHP integrado en un puerto libre
#    2. Recorre las rutas publicas y privadas con sesion real
#    3. Comprueba codigos HTTP, contenido y control de acceso
#    4. Revisa el registro de errores PHP
#    5. Detiene el servidor al terminar
# =====================================================================

$ErrorActionPreference = 'Continue'

# ---------------------------------------------------------------------
# Configuracion
# ---------------------------------------------------------------------
$raiz      = Split-Path -Parent $PSScriptRoot
$docRaiz   = Join-Path $raiz 'public'
$php       = 'C:\xampp\php\php.exe'
$mysql     = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe'
$logErrores = Join-Path $raiz 'logs\errores.log'

# Puerto libre
$puerto = 8899
while (Get-NetTCPConnection -LocalPort $puerto -ErrorAction SilentlyContinue) {
    $puerto++
}
$baseUrl = "http://127.0.0.1:$puerto"

# Credenciales de prueba (las de la carga inicial)
$credenciales = @{
    admin   = @{ identificador = 'admin';   clave = 'Admin123*' }
    abogado = @{ identificador = 'abogado'; clave = 'Abogado123*' }
    asesor  = @{ identificador = 'asesor';  clave = 'Asesor123*' }
}

# ---------------------------------------------------------------------
# Contadores
# ---------------------------------------------------------------------
$script:total  = 0
$script:ok     = 0
$script:fallos = New-Object System.Collections.ArrayList

function Probar {
    param(
        [string]   $Nombre,
        [scriptblock] $Cuerpo
    )
    $script:total++
    try {
        $resultado = & $Cuerpo
        if ($resultado -eq $true) {
            $script:ok++
            Write-Host ("  [OK]    " + $Nombre) -ForegroundColor Green
        } else {
            [void]$script:fallos.Add($Nombre)
            Write-Host ("  [FALLA] " + $Nombre + "  -> " + $resultado) -ForegroundColor Red
        }
    } catch {
        [void]$script:fallos.Add($Nombre)
        Write-Host ("  [ERROR] " + $Nombre + "  -> " + $_.Exception.Message) -ForegroundColor Red
    }
}

# ---------------------------------------------------------------------
# Cliente HTTP: usa WebSession para que PowerShell gestione las cookies.
# La aplicacion regenera el id de sesion al autenticar, por lo que un
# almacen manual de cookies no es fiable.
# ---------------------------------------------------------------------
$script:sesion = New-Object Microsoft.PowerShell.Commands.WebRequestSession

function Pedir {
    param(
        [string] $Ruta,
        [string] $Metodo = 'GET',
        [hashtable] $Datos = $null
    )
    $params = @{
        Uri                = $baseUrl + $Ruta
        Method             = $Metodo
        WebSession         = $script:sesion
        MaximumRedirection = 0
        UseBasicParsing    = $true
        TimeoutSec         = 30
    }
    if ($Metodo -eq 'POST') {
        if ($Datos) { $params['Body'] = $Datos } else { $params['Body'] = @{} }
        $params['ContentType'] = 'application/x-www-form-urlencoded'
    }

    try {
        $resp = Invoke-WebRequest @params
        return [pscustomobject]@{
            StatusCode = $resp.StatusCode
            Content    = $resp.Content
            Location   = $resp.Headers['Location']
        }
    } catch {
        $wr = $_.Exception.Response
        if ($null -eq $wr) { throw }
        $loc = $null
        try { $loc = $wr.Headers['Location'] } catch { }
        return [pscustomobject]@{
            StatusCode = [int] $wr.StatusCode
            Content    = ''
            Location   = $loc
        }
    }
}

function ObtenerTokenCsrf {
    param([string] $Html)
    if ($Html -match 'name="csrf_token"\s+value="([^"]+)"') { return $Matches[1] }
    if ($Html -match 'value="([a-f0-9]{64})"')              { return $Matches[1] }
    return ''
}

function IniciarSesion {
    param([string] $Identificador, [string] $Clave)
    $script:sesion = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $login = Pedir -Ruta '/index.php?modulo=auth&accion=login'
    $token = ObtenerTokenCsrf $login.Content
    return Pedir -Ruta '/index.php?modulo=auth&accion=login' -Metodo 'POST' -Datos @{
        identificador = $Identificador
        clave         = $Clave
        csrf_token    = $token
    }
}

# =====================================================================
Write-Host ''
Write-Host '============================================================'
Write-Host ' PRUEBA E2E - GESTOR DOCUMENTAL'
Write-Host '============================================================'
Write-Host (" Raiz   : " + $raiz)
Write-Host (" Puerto : " + $puerto)
Write-Host ''

# ---------------------------------------------------------------------
# 0. Comprobaciones previas
# ---------------------------------------------------------------------
Write-Host '-- 0. Comprobaciones previas' -ForegroundColor Cyan

Probar 'El interprete PHP existe' {
    if (Test-Path $php) { $true } else { "no se encontro $php" }
}
Probar 'El cliente de MySQL existe' {
    if (Test-Path $mysql) { $true } else { "no se encontro $mysql" }
}
Probar 'La base de datos responde' {
    $r = "SELECT COUNT(*) FROM gestor_documental.documentos;" |
         & $mysql -u root -p12345 -h 127.0.0.1 -P 3306 -N -B 2>&1
    $n = ($r | Where-Object { $_ -match '^\d+$' } | Select-Object -First 1)
    if ($n -and [int]$n -ge 0) { $true } else { "sin respuesta: $r" }
}

# ---------------------------------------------------------------------
# 1. Arrancar el servidor
# ---------------------------------------------------------------------
Write-Host ''
Write-Host '-- 1. Arrancando servidor PHP' -ForegroundColor Cyan

$logServidor  = Join-Path $env:TEMP ("srv_gestor_{0}_out.log" -f $puerto)
$logServidorE = Join-Path $env:TEMP ("srv_gestor_{0}_err.log" -f $puerto)
$proc = Start-Process -FilePath $php `
                      -ArgumentList @('-S', "127.0.0.1:$puerto", '-t', $docRaiz) `
                      -WorkingDirectory $docRaiz `
                      -RedirectStandardOutput $logServidor `
                      -RedirectStandardError  $logServidorE `
                      -PassThru -WindowStyle Hidden

# Esperar a que escuche
$listo = $false
for ($i = 0; $i -lt 40; $i++) {
    Start-Sleep -Milliseconds 250
    if (Get-NetTCPConnection -LocalPort $puerto -State Listen -ErrorAction SilentlyContinue) {
        $listo = $true
        break
    }
}
Probar 'El servidor PHP acepta conexiones' {
    if ($listo) { $true } else { 'el puerto no escucha' }
}

if (-not $listo) {
    Write-Host ''
    Write-Host 'No se pudo arrancar el servidor. Se detiene la prueba.' -ForegroundColor Red
    if ($proc -and -not $proc.HasExited) { Stop-Process -Id $proc.Id -Force }
    exit 1
}

# Marcar de donde partir en el log de errores
$marcaLog = 0
if (Test-Path $logErrores) { $marcaLog = (Get-Item $logErrores).Length }

try {
    # -----------------------------------------------------------------
    # 2. Rutas publicas
    # -----------------------------------------------------------------
    Write-Host ''
    Write-Host '-- 2. Rutas publicas' -ForegroundColor Cyan

    Probar '/ responde 200 o 302' {
        $r = Pedir -Ruta '/'
        if ($r.StatusCode -in 200, 302) { $true } else { "HTTP $($r.StatusCode)" }
    }
    Probar 'El login responde 200' {
        $r = Pedir -Ruta '/index.php?modulo=auth&accion=login'
        if ($r.StatusCode -eq 200) { $true } else { "HTTP $($r.StatusCode)" }
    }
    Probar 'El login contiene el formulario' {
        $r = Pedir -Ruta '/index.php?modulo=auth&accion=login'
        if ($r.Content -match 'identificador' -and $r.Content -match 'clave') { $true }
        else { 'no se encontro el formulario' }
    }
    Probar 'El login incluye token CSRF' {
        $r = Pedir -Ruta '/index.php?modulo=auth&accion=login'
        if (ObtenerTokenCsrf $r.Content) { $true } else { 'sin token CSRF' }
    }
    Probar 'Modulo inexistente devuelve 404' {
        $r = Pedir -Ruta '/index.php?modulo=noexiste-xyz'
        if ($r.StatusCode -eq 404) { $true } else { "HTTP $($r.StatusCode)" }
    }
    Probar 'Modulo privado sin sesion no da acceso' {
        $script:sesion = New-Object Microsoft.PowerShell.Commands.WebRequestSession
        $r = Pedir -Ruta '/index.php?modulo=documentos'
        if ($r.StatusCode -in 302, 401, 403) { $true } else { "HTTP $($r.StatusCode)" }
    }

    # -----------------------------------------------------------------
    # 3. Inicio de sesion por rol
    # -----------------------------------------------------------------
    Write-Host ''
    Write-Host '-- 3. Inicio de sesion' -ForegroundColor Cyan

    Probar 'Credenciales incorrectas son rechazadas' {
        $r = IniciarSesion -Identificador 'admin' -Clave 'clave-mala-xyz'
        $r2 = Pedir -Ruta '/index.php?modulo=panel'
        if ($r2.StatusCode -in 302, 401, 403) { $true }
        else { "acceso concedido con clave incorrecta (HTTP $($r2.StatusCode))" }
    }
    Probar 'El administrador inicia sesion' {
        $r = IniciarSesion -Identificador $credenciales.admin.identificador -Clave $credenciales.admin.clave
        $p = Pedir -Ruta '/index.php?modulo=panel'
        if ($p.StatusCode -eq 200) { $true }
        else { "HTTP $($p.StatusCode) | login=$($r.StatusCode)" }
    }

    # -----------------------------------------------------------------
    # 4. Modulos del administrador
    # -----------------------------------------------------------------
    Write-Host ''
    Write-Host '-- 4. Modulos del administrador' -ForegroundColor Cyan

    $rutasAdmin = @(
        @{ r = '/index.php?modulo=panel';                     n = 'Panel de indicadores' },
        @{ r = '/index.php?modulo=documentos';                n = 'Listado de documentos' },
        @{ r = '/index.php?modulo=documentos&accion=crear';   n = 'Formulario de carga' },
        @{ r = '/index.php?modulo=panel&accion=buscar';       n = 'Buscador documental' },
        @{ r = '/index.php?modulo=casos';                     n = 'Modulo de casos' },
        @{ r = '/index.php?modulo=usuarios';                  n = 'Gestion de usuarios' },
        @{ r = '/index.php?modulo=usuarios&accion=crear';     n = 'Formulario de usuario' },
        @{ r = '/index.php?modulo=roles';                     n = 'Modulo de roles' },
        @{ r = '/index.php?modulo=areas';                     n = 'Modulo de areas' },
        @{ r = '/index.php?modulo=categorias';                n = 'Modulo de categorias' },
        @{ r = '/index.php?modulo=auditoria';                 n = 'Bitacora de auditoria' },
        @{ r = '/index.php?modulo=respaldos';                 n = 'Copias de seguridad' },
        @{ r = '/index.php?modulo=configuracion';             n = 'Configuracion' },
        @{ r = '/index.php?modulo=reportes';                  n = 'Reportes' }
    )

    foreach ($x in $rutasAdmin) {
        Probar ("GET " + $x.r) {
            $r = Pedir -Ruta $x.r
            if ($r.StatusCode -eq 200) { $true } else { "HTTP $($r.StatusCode)" }
        }
    }

    Probar 'La pagina de usuarios muestra intentos fallidos' {
        # La vista lee $u['intentos_fallidos']: si la vista SQL no expusiera
        # esa columna, PHP emitiria "Undefined array key" y saldria aqui.
        $r = Pedir -Ruta '/index.php?modulo=usuarios'
        if ($r.StatusCode -ne 200) { return "HTTP $($r.StatusCode)" }
        if ($r.Content -match 'Undefined array key') { return 'hay un aviso de clave indefinida' }
        if ($r.Content -notmatch 'Usuarios') { return 'no se renderizo la pagina de usuarios' }
        $true
    }
    Probar 'El aviso de intentos fallidos aparece cuando corresponde' {
        # Se fuerza un intento fallido y se comprueba que la interfaz lo refleja.
        $sql = "UPDATE gestor_documental.usuarios SET intentos_fallidos = 2 WHERE id_usuario = 3;"
        $sql | & $mysql -u root -p12345 -h 127.0.0.1 -P 3306 -N -B *> $null
        $r = Pedir -Ruta '/index.php?modulo=usuarios'
        $refleja = ($r.Content -match '2 intento')
        $sql2 = "UPDATE gestor_documental.usuarios SET intentos_fallidos = 0 WHERE id_usuario = 3;"
        $sql2 | & $mysql -u root -p12345 -h 127.0.0.1 -P 3306 -N -B *> $null
        if ($refleja) { $true } else { 'la interfaz no mostro los intentos fallidos' }
    }
    Probar 'El formulario de usuario no produce avisos' {
        $r = Pedir -Ruta '/index.php?modulo=usuarios&accion=crear'
        if ($r.Content -match 'Undefined array key') { 'hay un aviso de clave indefinida' }
        else { $true }
    }
    Probar 'El formulario de carga no produce avisos' {
        $r = Pedir -Ruta '/index.php?modulo=documentos&accion=crear'
        if ($r.Content -match 'Undefined array key') { 'hay un aviso de clave indefinida' }
        else { $true }
    }
    Probar 'El reporte imprimible responde 200' {
        $r = Pedir -Ruta '/index.php?modulo=reportes&accion=imprimir'
        if ($r.StatusCode -eq 200) { $true } else { "HTTP $($r.StatusCode)" }
    }
    Probar 'El reporte imprimible no produce avisos' {
        $r = Pedir -Ruta '/index.php?modulo=reportes&accion=imprimir'
        if ($r.Content -match 'Undefined array key') { 'hay un aviso de clave indefinida' }
        else { $true }
    }
    Probar 'Ninguna pagina devuelve error 500' {
        $malas = @()
        foreach ($x in $rutasAdmin) {
            $r = Pedir -Ruta $x.r
            if ($r.StatusCode -ge 500) { $malas += "$($x.n)=$($r.StatusCode)" }
        }
        if ($malas.Count -eq 0) { $true } else { ($malas -join ', ') }
    }

    # -----------------------------------------------------------------
    # 5. Busqueda
    # -----------------------------------------------------------------
    Write-Host ''
    Write-Host '-- 5. Busqueda documental' -ForegroundColor Cyan

    Probar 'La busqueda con resultados responde 200' {
        $r = Pedir -Ruta '/index.php?modulo=panel&accion=buscar&q=contrato'
        if ($r.StatusCode -eq 200) { $true } else { "HTTP $($r.StatusCode)" }
    }
    Probar 'La busqueda encuentra el contrato' {
        $r = Pedir -Ruta '/index.php?modulo=panel&accion=buscar&q=contrato'
        if ($r.StatusCode -ne 200) { return "HTTP $($r.StatusCode)" }
        if ($r.Content -match 'DOC-2026-0001' -or $r.Content -match 'Contrato') { $true }
        else { 'no aparece el documento' }
    }
    Probar 'La busqueda sin resultados responde 200' {
        $r = Pedir -Ruta '/index.php?modulo=panel&accion=buscar&q=zzzznoexiste'
        if ($r.StatusCode -eq 200) { $true } else { "HTTP $($r.StatusCode)" }
    }
    Probar 'La busqueda con termino corto responde 200' {
        $r = Pedir -Ruta '/index.php?modulo=panel&accion=buscar&q=a'
        if ($r.StatusCode -eq 200) { $true } else { "HTTP $($r.StatusCode)" }
    }
    Probar 'La busqueda no genera error 500' {
        $r = Pedir -Ruta '/index.php?modulo=panel&accion=buscar&q=%25%27%22'
        if ($r.StatusCode -lt 500) { $true } else { "HTTP $($r.StatusCode)" }
    }

    # -----------------------------------------------------------------
    # 6. Control de acceso (rol Asesor)
    # -----------------------------------------------------------------
    Write-Host ''
    Write-Host '-- 6. Control de acceso - rol Asesor' -ForegroundColor Cyan

    Probar 'El asesor inicia sesion' {
        $r = IniciarSesion -Identificador $credenciales.asesor.identificador -Clave $credenciales.asesor.clave
        $p = Pedir -Ruta '/index.php?modulo=panel'
        if ($p.StatusCode -eq 200) { $true } else { "HTTP $($p.StatusCode)" }
    }
    Probar 'El asesor ve el listado de documentos' {
        $r = Pedir -Ruta '/index.php?modulo=documentos'
        if ($r.StatusCode -eq 200) { $true } else { "HTTP $($r.StatusCode)" }
    }
    Probar 'El asesor NO ve el documento reservado' {
        $r = Pedir -Ruta '/index.php?modulo=documentos'
        if ($r.Content -match 'DOC-2026-0002') { 'aparece el documento reservado' }
        else { $true }
    }
    Probar 'El asesor NO ve el borrador' {
        $r = Pedir -Ruta '/index.php?modulo=documentos'
        if ($r.Content -match 'DOC-2026-0005') { 'aparece el borrador' }
        else { $true }
    }
    Probar 'El asesor ve los documentos de su area' {
        $r = Pedir -Ruta '/index.php?modulo=documentos'
        if ($r.Content -match 'DOC-2026-0001') { $true } else { 'no ve los documentos de su area' }
    }

    $prohibidas = @(
        @{ r = '/index.php?modulo=usuarios';      n = 'usuarios' },
        @{ r = '/index.php?modulo=roles';         n = 'roles' },
        @{ r = '/index.php?modulo=areas';         n = 'areas' },
        @{ r = '/index.php?modulo=respaldos';     n = 'respaldos' },
        @{ r = '/index.php?modulo=configuracion'; n = 'configuracion' },
        @{ r = '/index.php?modulo=auditoria';     n = 'auditoria' }
    )
    foreach ($x in $prohibidas) {
        Probar ("El asesor NO accede a " + $x.n) {
            $r = Pedir -Ruta $x.r
            if ($r.StatusCode -in 302, 401, 403) { $true }
            else { "acceso concedido (HTTP $($r.StatusCode))" }
        }
    }

    # -----------------------------------------------------------------
    # 7. Rol intermedio (Responsable Documental)
    # -----------------------------------------------------------------
    Write-Host ''
    Write-Host '-- 7. Control de acceso - rol Responsable' -ForegroundColor Cyan

    Probar 'El responsable inicia sesion' {
        $r = IniciarSesion -Identificador $credenciales.abogado.identificador -Clave $credenciales.abogado.clave
        $p = Pedir -Ruta '/index.php?modulo=panel'
        if ($p.StatusCode -eq 200) { $true } else { "HTTP $($p.StatusCode)" }
    }
    Probar 'El responsable accede al listado de documentos' {
        $r = Pedir -Ruta '/index.php?modulo=documentos'
        if ($r.StatusCode -eq 200) { $true } else { "HTTP $($r.StatusCode)" }
    }
    Probar 'El responsable NO administra usuarios' {
        $r = Pedir -Ruta '/index.php?modulo=usuarios'
        if ($r.StatusCode -in 302, 401, 403) { $true }
        else { "acceso concedido (HTTP $($r.StatusCode))" }
    }

    # -----------------------------------------------------------------
    # 8. Cierre de sesion
    # -----------------------------------------------------------------
    Write-Host ''
    Write-Host '-- 8. Cierre de sesion' -ForegroundColor Cyan

    Probar 'El cierre de sesion funciona' {
        [void](IniciarSesion -Identificador $credenciales.admin.identificador -Clave $credenciales.admin.clave)
        $r = Pedir -Ruta '/index.php?modulo=auth&accion=logout'
        $p = Pedir -Ruta '/index.php?modulo=panel'
        if ($p.StatusCode -in 302, 401, 403) { $true } else { "sigue autenticado (HTTP $($p.StatusCode))" }
    }

    # -----------------------------------------------------------------
    # 9. Registro de errores
    # -----------------------------------------------------------------
    Write-Host ''
    Write-Host '-- 9. Registro de errores PHP' -ForegroundColor Cyan

    Probar 'No hay errores nuevos en logs/errores.log' {
        if (-not (Test-Path $logErrores)) { return $true }
        $contenido = Get-Content -LiteralPath $logErrores -Raw
        if (-not $contenido) { return $true }
        $nuevo = ''
        if ($contenido.Length -gt $marcaLog) {
            $nuevo = $contenido.Substring($marcaLog)
        }
        $lineas = ($nuevo -split "`r?`n") |
                  Where-Object { $_ -match 'Undefined array key|Fatal error|SQLSTATE|ambiguous|Uncaught' }
        if ($lineas.Count -eq 0) { $true } else { ($lineas | Select-Object -First 3) -join ' | ' }
    }
}
finally {
    # -----------------------------------------------------------------
    # Detener el servidor
    # -----------------------------------------------------------------
    if ($proc -and -not $proc.HasExited) {
        Stop-Process -Id $proc.Id -Force -ErrorAction SilentlyContinue
    }
    Get-Process -Name php -ErrorAction SilentlyContinue |
        Where-Object { $_.StartTime -gt (Get-Date).AddMinutes(-5) } |
        ForEach-Object { Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue }
}

# ---------------------------------------------------------------------
# Resumen
# ---------------------------------------------------------------------
Write-Host ''
Write-Host '============================================================'
Write-Host ' RESULTADO'
Write-Host '============================================================'
Write-Host (" Pruebas ejecutadas : " + $script:total)
Write-Host (" Correctas          : " + $script:ok) -ForegroundColor Green
$nFallos = $script:fallos.Count
if ($nFallos -eq 0) {
    Write-Host ' Fallidas           : 0' -ForegroundColor Green
    Write-Host ''
    Write-Host ' TODAS LAS PRUEBAS PASARON' -ForegroundColor Green
    exit 0
} else {
    Write-Host (" Fallidas           : " + $nFallos) -ForegroundColor Red
    Write-Host ''
    Write-Host ' Pruebas fallidas:' -ForegroundColor Red
    foreach ($f in $script:fallos) { Write-Host ("   - " + $f) -ForegroundColor Red }
    exit 1
}
