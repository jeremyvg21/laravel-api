# API de inventario — Laravel 12

Backend de una aplicación de inventario para gestionar productos y categorías. Se comunica mediante una API REST con el panel desarrollado en Vue 3.

Desarrollé el flujo completo desde MySQL hasta los endpoints: CRUD, validaciones, indicadores y registro de actividad. La aplicación está pensada como una prueba funcional en entorno local, no como un servicio abierto en producción.

## Funcionalidades

- Crear, consultar, editar y eliminar productos.
- Administrar categorías y consultar cuántos productos tiene cada una.
- Buscar productos y filtrarlos por categoría.
- Consultar un resumen del inventario: total de productos, categorías, unidades disponibles y valor estimado del stock.
- Revisar los últimos movimientos, identificando los registros por su nombre cuando está disponible.
- Consultar las rutas y ejemplos de peticiones desde Swagger UI.

**Regla de negocio:** no se permite eliminar una categoría si todavía tiene productos asociados. La API responde con HTTP `409`.

## Tecnologías

- PHP 8.3 y **Laravel 12.69**.
- MySQL 8, Eloquent y migraciones.
- Laravel Sanctum (instalado; esta demo no utiliza autenticación en las rutas CRUD).
- Laravel Telescope para inspeccionar solicitudes y consultas en desarrollo.
- Swagger UI y especificación **OpenAPI 3 en YAML**, disponible en `/api/docs` y `/api/openapi.yaml`.
- Registro propio de auditoría mediante eventos de Eloquent y tabla `activity_logs` (alternativa a Spatie Activity Log).
- PHPUnit para las pruebas HTTP.

La definición OpenAPI está escrita en YAML y se muestra con Swagger UI. No se utilizó `l5-swagger` ni `swagger-php` para generarla automáticamente.

## Requisitos

- PHP 8.2 o superior (el proyecto se verificó con PHP **8.3.30**).
- Composer.
- MySQL 8 (se utilizó MySQL **8.4.3** con Laragon).
- Extensiones PHP `pdo_mysql` y `fileinfo`.

No se necesita Docker para esta instalación.

## Instalación

Desde una terminal de PowerShell o equivalente:

```powershell
cd laravel-api
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Si ya existe `.env`, **no lo sobrescribas** ni regeneres la clave sin necesidad. En Windows, asegúrate de que Composer y `php` estén usando la misma instalación de PHP.

Crea una base MySQL llamada `overskull_productos` y configura estas variables en `.env`:

```dotenv
APP_NAME="Overskull Inventario"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=overskull_productos
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

`root` sin contraseña se muestra solo como ejemplo del entorno local de Laragon. Para otro equipo utiliza las credenciales reales de MySQL. Nunca publiques `.env` ni uses esta configuración para desplegar un servicio público.

Prepara las tablas y agrega registros de ejemplo:

```powershell
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=ProductCategorySeeder
```

Inicia Laravel:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

## URLs útiles

| Dirección | Función |
| --- | --- |
| `http://127.0.0.1:8000/api/products` | Productos |
| `http://127.0.0.1:8000/api/categories` | Categorías |
| `http://127.0.0.1:8000/api/dashboard` | Indicadores y actividad reciente |
| `http://127.0.0.1:8000/api/docs` | Swagger UI |
| `http://127.0.0.1:8000/api/openapi.yaml` | Definición OpenAPI |
| `http://127.0.0.1:8000/telescope` | Telescope, en entorno local |

## Rutas de la API

| Método | Ruta | Operación |
| --- | --- | --- |
| GET | `/api/products` | Listar productos; admite búsqueda y filtro de categoría |
| POST | `/api/products` | Crear producto |
| GET | `/api/products/{product}` | Consultar producto |
| PUT/PATCH | `/api/products/{product}` | Actualizar producto |
| DELETE | `/api/products/{product}` | Eliminar producto |
| GET | `/api/categories` | Listar categorías y su cantidad de productos |
| POST | `/api/categories` | Crear categoría |
| GET | `/api/categories/{category}` | Consultar categoría |
| PUT/PATCH | `/api/categories/{category}` | Actualizar categoría |
| DELETE | `/api/categories/{category}` | Eliminar categoría si no tiene productos |
| GET | `/api/dashboard` | Resumen del inventario |

Ejemplo de petición `POST /api/products`:

```json
{
  "name": "Teclado compacto",
  "description": "Teclado USB",
  "price": 89.00,
  "stock": 9,
  "category_id": 1
}
```

Las respuestas de éxito siguen la forma `success`, `message`, `data`. Los errores de validación devuelven `success: false`, `message` y `errors` por campo (HTTP `422`). También se manejan recursos inexistentes (`404`) y categorías ocupadas (`409`).

## Organización del código

```text
app/
  Http/Controllers/Api/      Controladores de categorías y productos
  Http/Requests/             Reglas de validación
  Models/                    Modelos Eloquent y relaciones
  Models/Concerns/           Registro de actividad
bootstrap/app.php            Configuración de rutas y errores JSON
routes/api.php               Rutas REST y endpoint del dashboard
database/migrations/         Tablas y claves foráneas
database/seeders/            Datos iniciales
public/docs/openapi.yaml     Contrato de la API
resources/views/             Vista de Swagger UI
tests/Feature/               Pruebas HTTP
scripts/                     Scripts de comprobación
```

El recorrido principal es **ruta → controlador → FormRequest → modelo → MySQL → respuesta JSON**. Los movimientos de los modelos se guardan también en la tabla de auditoría.

## Pruebas

Las pruebas se ejecutaron con una base MySQL independiente (`overskull_productos_test`), para no alterar los datos del inventario local.

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\run-tests-mysql.ps1
```

**Último resultado comprobado el 28/10/2026:** 10 pruebas aprobadas y 41 aserciones, con Laravel 12.69. Los casos incluyen CRUD de categorías y productos, errores `422` y `404`, restricción `409`, actividad y dashboard. El resultado de seguridad de `composer audit` fue: **sin avisos de vulnerabilidad conocidos** en ese momento.

Para consultar rutas y migraciones:

```powershell
php artisan route:list --path=api
php artisan migrate:status
composer audit
```

## Telescope y auditoría

Telescope está instalado como dependencia de desarrollo y tiene sus migraciones aplicadas. Se puede revisar en `/telescope` durante la ejecución local. **La apertura y comprobación visual del panel debe realizarse en el entorno donde se levanta el proyecto.**

La auditoría de operaciones es independiente de Telescope: registra acciones `created`, `updated` y `deleted` junto con los valores anteriores y nuevos cuando corresponde. El dashboard utiliza esos registros para mostrar los movimientos recientes.

## Problemas habituales

- **Composer utiliza otro PHP:** comprueba `php -v`, `where.exe php` y las extensiones activas; en las pruebas locales se utilizó el PHP de Laragon.
- **Error de conexión con MySQL:** comprueba el servicio, puerto, nombre de la base y datos de `.env`; luego ejecuta `php artisan optimize:clear`.
- **`could not find driver` al ejecutar tests:** el entorno intentaba usar SQLite sin su extensión. El script `run-tests-mysql.ps1` utiliza la base MySQL de pruebas.
- **Errores `422`:** consulta el objeto `errors` de la respuesta, que indica qué campo necesita corrección.
- **Frontend sin datos:** comprueba que Laravel esté funcionando en el puerto `8000`.

## Alcance

Este ejercicio no tiene inicio de sesión ni permisos por usuario. Las rutas de escritura están abiertas para facilitar la evaluación local; **no deben desplegarse públicamente sin autenticación y controles de acceso**. Para un entorno real también agregaría paginación, configuración de despliegue y pruebas de carga.

El frontend está disponible en un repositorio independiente: [vue-frontend](https://github.com/jeremyvg21/vue-frontend).
