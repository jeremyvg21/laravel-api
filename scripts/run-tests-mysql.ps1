param(
    [string]$Filtro = "",
    [string]$MysqlUsuario = "root",
    [string]$MysqlPassword = ""
)

$ErrorActionPreference = 'Stop'
$base = Split-Path -Parent $PSScriptRoot
$nombrePruebas = 'overskull_productos_test'

$phpExe = Get-ChildItem 'C:\laragon\bin\php' -Recurse -Filter 'php.exe' |
    Where-Object { $_.FullName -match '8\.3' } | Select-Object -First 1 -ExpandProperty FullName
$mysqlExe = Get-ChildItem 'C:\laragon\bin\mysql' -Recurse -Filter 'mysql.exe' |
    Select-Object -First 1 -ExpandProperty FullName

if (-not $phpExe -or -not $mysqlExe) {
    throw 'No encontre PHP 8.3 o mysql.exe dentro de C:\laragon. Revisa la instalacion.'
}

$drivers = & $phpExe -m
if ($LASTEXITCODE -ne 0 -or -not ($drivers -contains 'pdo_mysql')) {
    throw 'PHP no tiene pdo_mysql habilitado.'
}

Write-Host 'Preparando base aislada:' $nombrePruebas -ForegroundColor Cyan
$mysqlArgs = @('-h','127.0.0.1','-P','3306','-u',$MysqlUsuario)
if ($MysqlPassword -ne '') { $mysqlArgs += "-p$MysqlPassword" }
$mysqlArgs += @('-e',"CREATE DATABASE IF NOT EXISTS $nombrePruebas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;")
& $mysqlExe @mysqlArgs
if ($LASTEXITCODE -ne 0) { throw 'No se pudo crear o abrir la base de pruebas.' }

# No cambiamos .env ni phpunit.xml: este proceso usa variables temporales.
$variables = @{
    APP_ENV='testing'
    DB_CONNECTION='mysql'
    DB_HOST='127.0.0.1'
    DB_PORT='3306'
    DB_DATABASE=$nombrePruebas
    DB_USERNAME=$MysqlUsuario
    DB_PASSWORD=$MysqlPassword
    CACHE_STORE='array'
    SESSION_DRIVER='array'
    QUEUE_CONNECTION='sync'
}
$originales = @{}
foreach ($clave in $variables.Keys) {
    $originales[$clave] = [Environment]::GetEnvironmentVariable($clave, 'Process')
    [Environment]::SetEnvironmentVariable($clave, $variables[$clave], 'Process')
}

Push-Location $base
try {
    Write-Host 'Conectando con MySQL de pruebas...' -ForegroundColor Cyan
    & $phpExe artisan config:clear
    if ($LASTEXITCODE -ne 0) { throw 'Fallo config:clear.' }

    Write-Host 'Aplicando migraciones en la base de pruebas...' -ForegroundColor Cyan
    & $phpExe artisan migrate --force
    if ($LASTEXITCODE -ne 0) { throw 'Fallo la migracion de la base de pruebas.' }

    Write-Host 'Ejecutando PHPUnit...' -ForegroundColor Cyan
    if ($Filtro -ne '') {
        & $phpExe artisan test "--filter=$Filtro"
    } else {
        & $phpExe artisan test
    }
    if ($LASTEXITCODE -ne 0) { throw 'Alguna prueba fallo. Revisa el resultado anterior.' }
    Write-Host 'Pruebas finalizadas correctamente.' -ForegroundColor Green
}
finally {
    Pop-Location
    foreach ($clave in $originales.Keys) {
        [Environment]::SetEnvironmentVariable($clave, $originales[$clave], 'Process')
    }
}
