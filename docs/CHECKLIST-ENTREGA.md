# Lista de entrega

## Confirmado durante el desarrollo local

- [x] Laravel 11 conectado a MySQL.
- [x] Tablas de categorías, productos y actividad creadas.
- [x] API de productos, categorías y dashboard funcionando.
- [x] Swagger accesible en `/api/docs`.
- [x] Vista de dashboard, productos y categorías disponible en Vue.
- [x] Registro y edición desde Vue observados.
- [x] Compilación Vite completada sin errores.

## Revisar antes de enviar

- [ ] Ejecutar los tests en MySQL de pruebas con `scripts/run-tests-mysql.ps1`.
- [ ] Verificar que el dashboard muestra los nombres de los productos en la actividad.
- [ ] Comprobar eliminación y validaciones desde Vue.
- [ ] Instalar y comprobar Telescope si sigue siendo un requisito sin cubrir.
- [ ] Ejecutar `composer audit` y revisar las vulnerabilidades reportadas.
- [ ] Revisar el código, README y ejemplos de Swagger para que coincidan.
- [ ] Confirmar que `.env`, claves, contraseñas y `node_modules` no aparecen en Git.
- [ ] Publicar `laravel-api` y `vue-frontend` en repositorios públicos distintos.
- [ ] Enviar ambos enlaces a `pierog@overskull.pe`.

No marcar como completado algo que todavía no se ejecutó.
