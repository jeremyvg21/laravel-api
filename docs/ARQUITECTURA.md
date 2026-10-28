# Cómo está organizada la API

La API se dividió en piezas pequeñas para evitar meter todas las reglas dentro de un único archivo.

## Flujo de ejemplo: crear un producto

1. Vue envía `POST /api/products` con los datos del formulario.
2. Laravel dirige la solicitud a `ProductController@store`.
3. `ProductRequest` comprueba el nombre, precio, stock y categoría.
4. El controlador guarda el producto usando el modelo `Product`.
5. El evento `created` genera un registro en `activity_logs`.
6. Laravel responde con código `201` y un JSON con `success`, `message` y `data`.
7. Vue muestra una notificación y actualiza el listado.

## Tablas y relaciones

- `categories`: `id`, `name` único, `description`, fechas de creación y edición.
- `products`: `id`, `name`, `description`, `price DECIMAL(10,2)`, `stock`, `category_id`, fechas.
- `activity_logs`: tipo de registro, identificador, acción, usuario si existe, valores anteriores y nuevos.

Una categoría puede tener varios productos; cada producto pertenece a una categoría. Se usa una clave foránea y un bloqueo explícito para evitar eliminar una categoría que todavía se utiliza.

## Por qué usé estas piezas

**FormRequest:** mantengo las reglas de validación fuera de los controladores. Es más fácil ubicarlas y revisarlas.

**Eloquent:** permite manejar las relaciones entre productos y categorías con menos consultas escritas manualmente.

**Auditoría con eventos:** al guardar o modificar los modelos se registra el movimiento sin repetir el código en cada controlador. Es una alternativa sencilla a una librería de auditoría.

**OpenAPI:** deja documentados los endpoints y permite probarlos desde Swagger. El YAML se mantiene manualmente, así que hay que actualizarlo si la API cambia.

**Respuestas comunes:** los casos exitosos entregan `success`, `message` y `data`; los errores incluyen `errors`, con códigos HTTP adecuados.

## Sobre la actividad reciente

El dashboard consulta los últimos movimientos y obtiene el nombre de la entidad cuando está disponible. En una eliminación, ese nombre se puede recuperar de los valores históricos almacenados en la auditoría. Así se evita mostrar solo algo como `Product #3`.

## Qué mejoraría en una siguiente versión

Autenticación y permisos, paginación, filtros de fechas para auditoría, pruebas de integración en CI y despliegue seguro. Esas funciones no son necesarias para demostrar el CRUD local, pero sí serían importantes en producción.
