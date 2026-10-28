# Pruebas realizadas

Actualizado el 28 de octubre de 2026.

## Entorno

- PHP 8.3.30 y Laravel 12.69.0.
- MySQL de Laragon.
- Base de datos independiente para PHPUnit: `overskull_productos_test`.
- Script: `scripts/run-tests-mysql.ps1`.

## Resultado observado

**10 tests aprobados, 41 aserciones, 0 fallos.**

Casos incluidos:

- Rutas de listado y dashboard con respuesta JSON.
- CRUD de categorías.
- CRUD de productos.
- Validación de precio negativo (`422`).
- Bloqueo de eliminación de categorías con productos (`409`).
- Auditoría de creación y nombres de movimientos recientes.
- Recurso inexistente (`404`).
- Pruebas de ejemplo de Laravel.

La prueba se ejecutó después de actualizar a Laravel 12.69.0. `composer audit` no reportó avisos de vulnerabilidad conocidos en ese momento.

La compilación de Vue con `npm.cmd run build` también finalizó correctamente (100 módulos transformados).

## Repetir las comprobaciones

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\run-tests-mysql.ps1
composer audit
php artisan route:list --path=api
```

Telescope se instaló y sus migraciones se aplicaron. Su visualización en el navegador debe comprobarse aparte en `/telescope`.
