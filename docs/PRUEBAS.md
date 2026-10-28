# Pruebas del backend

En las pruebas automáticas encontré un error concreto: `PDOException: could not find driver`. No se debía al CRUD; PHP estaba intentando abrir SQLite sin el controlador instalado.

Para no tocar la base principal ni depender de SQLite, añadí un script que utiliza una **segunda base de MySQL**, llamada `overskull_productos_test`.

## Ejecutar los tests con MySQL

Desde la carpeta `laravel-api`:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\run-tests-mysql.ps1
```

O solamente la prueba de la actividad reciente:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\run-tests-mysql.ps1 -Filtro DashboardActivityTest
```

El script:

1. Localiza PHP y MySQL dentro de Laragon.
2. Comprueba que `pdo_mysql` está activo.
3. Crea `overskull_productos_test` si no existe.
4. Establece variables de entorno únicamente durante esa ejecución.
5. Aplica las migraciones en la base de pruebas.
6. Ejecuta los tests, que usan transacciones para no conservar sus datos temporales.

**No usa `migrate:fresh` ni modifica `overskull_productos`.**

> El script asume el MySQL local de Laragon con usuario `root` y contraseña vacía, igual que el entorno de desarrollo mostrado. Si cambiaste el usuario o contraseña, ajusta las variables del script.

## Qué revisan las pruebas

- Respuestas del listado de productos y categorías.
- Creación, edición y eliminación.
- Datos inválidos: por ejemplo, precios negativos.
- Bloqueo de eliminación de una categoría con productos (`409`).
- Respuesta `404` al consultar un producto inexistente.
- Registro de creaciones en `activity_logs`.
- Nombre real en los movimientos recientes del dashboard.

## Revisión manual recomendada

Con Laravel y Vue abiertos, crea una categoría, añade un producto, actualiza su stock, verifica el cambio en el dashboard y elimina los registros en el orden correcto. La prueba manual complementa los tests porque permite revisar los mensajes y la navegación.

## Resultados

Hasta la revisión del **28/10/2026**, se comprobó manualmente que la API respondía con productos, categorías y métricas, y que Vue podía crear y editar registros. También se observó una compilación exitosa de Vite. La primera ejecución de `DashboardActivityTest` **falló por falta de controlador de base de datos**, sin ejecutar aserciones. El resultado del script nuevo queda pendiente de verificar en el entorno Windows.

No se debe marcar una prueba como aprobada hasta que termine correctamente.
