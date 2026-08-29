# Actualización a CodeIgniter 4.7.4

Fecha: 2026-08-27. Versión anterior: 4.4.8.

## Cambios

- PHP mínimo declarado: 8.2. PHP CLI y la configuración de Apache local usan 8.3.28.
- Composer actualizó CodeIgniter, laminas/laminas-escaper (2.18.0) y psr/log (3.0.2).
- Se adaptaron `public/index.php` y `spark` al arranque mediante Boot.
- Se actualizaron las configuraciones que coincidían con los valores originales de 4.4.8 y se agregaron Cors, Hostnames, Optimize y WorkerMode.
- Se conservaron las rutas, baseURL, conexión por .env, helper de precios y paginador Tailwind. Se integraron las nuevas propiedades de Paths, Toolbar y Filters sin eliminar los filtros auth/adminauth.
- Las tres comprobaciones POST de ProductoController usan Request::is(), compatible con los métodos HTTP en mayúsculas.
- La política CSRF y los permisos propios de la aplicación siguen pendientes de revisión; no se consideran corregidos por actualizar el framework.

## Verificación

- Antes: 5 pruebas y 6 aserciones correctas.
- Después: 6 pruebas y 9 aserciones correctas, incluyendo regresión de validación POST al crear productos.
- Sintaxis PHP de todos los archivos de app y tests: correcta.
- composer validate y composer check-platform-reqs: correctos.
- spark routes y migrate:status funcionan con 4.7.4.
- Base distribuidora: 12 tablas, 15 migraciones registradas, sin datos comerciales ni usuarios.
- No se ejecutaron migraciones destructivas ni seeders.
- Revisión visual pendiente: el navegador bloqueó el acceso local por su política. No se verificó el flujo completo de ventas.

## Dependencias pendientes

composer audit reportó 10 avisos en dos paquetes: PhpSpreadsheet (9) y PHPUnit (1). Ninguno corresponde al framework instalado. No se actualizaron estos paquetes en esta intervención; deben revisarse antes de producción. No se encontraron usos de PhpSpreadsheet en app/tests; la importación actual utiliza CSV nativo.

## Respaldo local

`build/backups/ci448-20260827-060931/` contiene app, composer.json, composer.lock, spark, phpunit.xml.dist, public-index.php y la configuración original del framework. No contiene .env, vendor ni una copia de la base de datos.

Para volver a 4.4.8 habría que restaurar conjuntamente código, configuración, arranque y archivos Composer del respaldo, retirar únicamente las configuraciones nuevas de esta actualización y reinstalar las dependencias desde el lock anterior. No basta con bajar la versión en composer.json. El respaldo incluye las correcciones de migraciones realizadas antes de actualizar.

## Referencias oficiales

- https://github.com/codeigniter4/CodeIgniter4/releases/tag/v4.7.4
- https://codeigniter4.github.io/userguide/installation/upgrade_450.html
- https://codeigniter4.github.io/userguide/installation/upgrade_460.html
- https://codeigniter4.github.io/userguide/installation/upgrade_470.html
