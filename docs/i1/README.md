# I1 - Seguridad y accesos

**Aceptación posterior del usuario:** I1 queda aceptado funcionalmente para continuar con I2. La prueba SMTP se difiere al hosting, antes de habilitar recuperación pública. El PR y su integración siguen pendientes; las pruebas diferidas no se consideran ejecutadas. Ver la sección de aceptación en [cierre-y-pr.md](cierre-y-pr.md), que prevalece sobre los estados históricos siguientes.

**Estado vigente:** ver [cierre-y-pr.md](cierre-y-pr.md). La revisión MySQL detectó MyISAM por defecto y añadió corrección InnoDB; la migración correctiva todavía no se aplicó a distribuidora. Las notas siguientes conservan el historial de entregas.

## Revisión de navegación y etiquetas del panel

Corregidos los enlaces legacy de productos y pedidos: /productos, /pedidos y /pedidos/{id}. La acción inexistente «Nuevo Pedido» se sustituye por «Ver pedidos», sin habilitar el envío suspendido. El panel explica que la creación llegará con reservas/pagos. Los totales de cabeceras se presentan como importes de pedidos por fecha, no como ventas cobradas ni facturación. El contador de pendientes aún no implementado y las consultas no disponibles se muestran con indicadores explícitos, sin convertir ausencia de cálculo en cero real. El resto de métricas legacy requiere revisión en los incrementos operativos.

Verificación: npm run build correcto; 42 pruebas PHP / 151 aserciones. Prueba de vista comprueba destinos de enlaces y etiquetas de datos no disponibles. No se validó en este paso una navegación autenticada completa ni se cambiaron datos locales. I1 continúa abierto por los pendientes detallados abajo.

## Compilación CSS y revisión pública — 28 de agosto de 2026

Resuelto el pendiente de compilación descrito en la actualización anterior. Tailwind y @tailwindcss/cli quedaron fijados en 4.3.3. Se adaptó input.css al formato v4, se declararon fuentes de vistas/JS propios y se mantuvieron los colores del proyecto en la configuración. El layout carga public/css/app.css con versión por fecha de archivo; se retiró el ejecutable CDN de Tailwind. Los estilos específicos de módulos y componentes existentes se conservan. Font Awesome sigue siendo externo: no se afirma funcionamiento totalmente sin conexión.

- npm run build: correcto, CSS generado de aproximadamente 27 KB. Incluir public/css/app.css en el despliegue y ejecutar el build después de cambiar clases de las vistas. En desarrollo npm run dev observa cambios. Node se usa para construir, no en HostGator.
- Se inspeccionó registro en escritorio y a 390 px en navegador: sin desbordamiento horizontal. Se inspeccionó login con CSS local. El flotante WhatsApp se oculta por defecto en layouts sin navbar (autenticación) para no cubrir formularios; showWhatsApp permite indicarlo explícitamente.
- 41 pruebas PHP / 142 aserciones correctas; se comprueba que el registro enlaza el CSS local y no el CDN de Tailwind. Instalación npm: cero avisos conocidos.
- La visita a /admin/accesos redirigió a login: no había sesión administrativa en el navegador de pruebas. Pendiente validar esa interfaz y sus tablas/confirmaciones con sesión autorizada; no se crearon usuarios ni se cambiaron permisos para probar.
- No requiere migraciones. El build y el CSS generado están preparados localmente, sin despliegue a producción. Compatibilidad visual de todas las pantallas legacy, SMTP real y MySQL/respaldo siguen pendientes; I1 no se declara cerrado.

Referencia técnica: https://tailwindcss.com/docs/installation/tailwind-cli y https://tailwindcss.com/docs/upgrade-guide.

## Actualización de dependencias — 28 de agosto de 2026

Este seguimiento sustituye los pendientes históricos de auditoría mencionados más abajo y en librerias-ui.md.

- npm: nanoid actualizado a 3.3.18 y postcss a 8.5.26 dentro de los rangos existentes. La auditoría en línea realizada durante la actualización informó cero vulnerabilidades conocidas.
- Composer: PhpSpreadsheet 1.30.0 → 1.30.6 y PHPUnit 9.6.29 → 9.6.36, con sus dependencias necesarias. No se cambió de versión principal de estas bibliotecas ni se deshabilitó el bloqueo de seguridad.
- Composer platform fija PHP y PHP de 64 bits en 8.2.0 para resolver conforme al mínimo declarado. ZipStream quedó en 3.1.2; 3.2.2 exigía PHP 8.3. Esto no sustituye comprobar la plataforma real del hosting ni prueba ejecución en PHP 8.2.
- composer audit --locked: sin avisos conocidos. composer validate: válido. composer check-platform-reqs: correcto en PHP 8.3.28 local.
- Verificación posterior: 41 pruebas PHP / 140 aserciones y 4 pruebas JavaScript correctas. npm run assets:ui correcto; librerías públicas regeneradas.
- Detectado pendiente previo: npm run build falla porque el proyecto declara Tailwind 4 pero conserva entrada/configuración de Tailwind 3 y no tiene su CLI disponible. No se modificó ese pipeline ni se afirma haber compilado el CSS global. Los estilos locales de accesos/registro/403 y los assets UI no dependen de ese comando.
- No se modificó la base distribuidora, ni cuentas, permisos o inventario. No requiere migraciones; no hubo despliegue a HostGator.

I1 sigue abierto por pruebas de navegador, recuperación con SMTP real, concurrencia MySQL, respaldo/restauración y revisión del pipeline CSS. Cero avisos conocidos no equivale a ausencia de riesgos. Antes del despliegue usar el lock verificado y comprobar los requisitos del servidor real.

## Seguimiento posterior: permisos y respuesta de acceso denegado

El usuario confirmó la inicialización local y mostró los permisos globales del usuario #1, excepto costs.view. Las notas de «no ejecutado» más abajo describen la entrega original, no este estado posterior. No se ha desplegado a producción.

- Corregida la consulta inicial de permisos para usar el usuario autenticado cuando no se especifica otro; se rechazan usuarios inexistentes.
- Registro y administración de accesos cuentan con estilos locales. El usuario aprobó la presentación del panel como referencia para siguientes entregables.
- Respuesta 403 diseñada con logo, explicación y enlaces a panel/catálogo, sin revelar reglas internas ni ofrecer elevación automática. Se aplica en PermissionFilter, AuthFilter y la comprobación de sesión de AccessController. No reemplaza todos los errores de los controladores legacy.
- AJAX y solicitudes que aceptan application/json reciben un error JSON con código access_denied. Se mantiene HTTP 403 y no-store; visitantes sin sesión siguen siendo redirigidos a login por los filtros.
- Verificación actual: **41 pruebas, 140 aserciones correctas**. Nuevos escenarios cubren concesión/retiro efectivos desde el filtro sin nuevo login, auditoría del cambio, persistencia de concesión individual al retirar la del rol, precedencia de denegación y respuesta JSON/AJAX.
- Pruebas de datos ejecutadas con fixtures SQLite en memoria. No se cambiaron permisos, cuentas ni inventario de distribuidora durante este entregable; no requiere migraciones.

### Validación manual pendiente de la nueva pantalla 403

En un entorno de pruebas, usar una cuenta sin access.manage y abrir /admin/accesos: debe mostrar la pantalla de acceso restringido, conservar HTTP 403 y permitir volver al panel. Revisar escritorio/móvil y navegación por teclado. No retirar permisos al único administrador para probarlo. No se añadió una ruta pública de demostración ni se creó una cuenta real de prueba.

I1 sigue abierto: revisión visual de la nueva pantalla 403, recuperación con SMTP real, auditoría de dependencias, concurrencia en MySQL y respaldo/restauración continúan pendientes. La suite SQLite no sustituye estas validaciones. Este entregable no inicia I2 ni autoriza producción.

28 de agosto de 2026. **Implementación inicial verificada en pruebas aisladas; I1 sigue abierto y no está desplegado.**

El usuario aprobó continuar con implementación y la precedencia de denegación explícita. Esta entrega no modifica inventario comercial ni implementa los flujos I2-I6.

## Implementado

- Catálogo de permisos por acción; reglas para rol/usuario con ámbito global o sucursal:N.
- Denegación prevalece, permisos efectivos con origen, concesión/retiro auditados y revocación leída sin caché de rol de sesión.
- Delegación exige poseer access.manage y el permiso objetivo en el ámbito. No se permite modificar permisos propios ni del rol propio. Las mutaciones se serializan mediante una fila de bloqueo.
- Pantalla /admin/accesos, protegida por permiso global; muestra usuarios/roles, efectos y últimos 50 cambios. Su acceso no concede costos implícitamente.
- Comando CLI access:bootstrap para un usuario activo existente, confirmación interactiva, sin crear contraseña. Solo permite inicialización cuando no hay reglas ni auditoría, excluye costs.view. No ejecutado.
- Migración 2026-08-28-000001_CreateAccessControl: users.auth_version, access_grants, access_audit, access_lock. Sin concesiones automáticas por nombre de rol.
- Autenticación revalida usuario activo/no eliminado y versión de sesión. Reset cambia versión e invalida sesiones anteriores.
- Registro público fija rol cliente del servidor e ignora role_id/auth_version enviados. Contraseña de registro mínimo12; no asigna privilegios ni verifica contacto de pedido (I5).
- Reset usa selector ID + secreto aleatorio, hash SHA-256 del secreto, expiración y consumo condicional transaccional; no depende del último token global. Se invalidan otros tokens del usuario tras cambiar contraseña.
- Vista de correo de recuperación; limitación de solicitudes y mensajes sin secretos. No se ha verificado envío SMTP real.
- CSRF de sesión global, token estable durante sesión por compatibilidad con formularios AJAX existentes. Page cache requerido retirado para no cachear respuestas autenticadas/tokens.
- Rutas de demo, migración y seeder HTTP eliminadas; auto-routing deshabilitado. Logout, borrado de productos y cambios de carretilla usan POST.
- Rutas de imágenes/importación/precios/ajustes/reportes protegidas. Crear/editar producto exige también prices.manage e inventory.adjust porque las pantallas actuales incluyen esos campos.
- Lectura de pedido ajeno denegada antes de consultar líneas; vendedor sin permiso global solo lista pedidos asociados a su usuario en el esquema antiguo.
- Carga de imagen valida MIME detectado y archivo de imagen; extensión determinada por MIME, nunca por nombre remitido.
- Cabecera de PedidoModel corregida a pedidos; carretilla normalizada a la estructura usada por add.

## Cambios funcionales deliberados

El envío legacy de pedido y cambiarEstado responden 409, temporalmente suspendidos: el código anterior descontaba inventario al confirmar sin verificación de pago/reserva. No se presenta ese flujo como compatible con el alcance aprobado. Se reemplaza en I5-I6, no se habilita otra vía de demo.

Las pantallas legacy carecen de sucursal en sus consultas: exigen permiso global. Un permiso sucursal:1 no permite abrir una pantalla global; una denegación de sucursal también bloquea el acceso global para evitar evasión. La aplicación real de ámbito por registro se completará con el modelo de sedes de I2; no se simula aislamiento inexistente.

El ámbito sucursal:N se valida en formato, pero todavía no existe un maestro de sucursales que valide N; no conceder ámbitos reales hasta integrarlo. La pantalla administrativa inicial está limitada a administradores globales. Los permisos de módulos futuros son catálogo, no funciones ya implementadas.

Los controles de campos se aplican por separación de rutas actuales: no hay vistas de bodega nuevas ni costos implementados. El catálogo público sigue mostrando precios públicos; RBAC no pretende impedir que una persona consulte información pública fuera de su sesión.

## Verificación realizada

- php vendor/bin/phpunit --no-coverage: **35 pruebas, 98 aserciones correctas**.
- php -l sobre app y tests: **149 archivos, cero errores** en el barrido; las modificaciones posteriores se cargaron en PHPUnit.
- Migración nueva aplicada y rollback probado en SQLite :memory: con fixtures; se comprueba aislamiento antes de crear tablas.
- Pruebas de denegación frente a rol, ámbito sin fuga, denegación de sucursal frente a pantalla global, concesión/revocación, auditoría, límite de delegación, propia elevación, sesión alterada/inválida, reset de dos usuarios, uso único/expiración, registro sin elevación, escape de auditoría, propiedad de pedido, filtros HTTP, rutas retiradas y CSRF válido/inválido.
- Regresiones anteriores de SEO/POST siguen pasando.
- Advertencia ambiental: Xdebug no puede abrir c:/wamp64/logs/xdebug.log; no afectó las aserciones.

No se probaron concurrencia real MySQL, restauración de backup, SMTP, interfaz visual en navegador ni permisos por sedes sobre módulos aún inexistentes. Las pruebas de autenticación de controlador no equivalen a validación completa de todos los recorridos UI.

## Dependencias: bloqueo externo

composer audit --locked se intentó en sandbox y con autorización. El primer acceso elevado agotó la revisión de autorización; el segundo llegó a ejecutar pero falló DNS para repo.packagist.org/packagist.org. No hay resultado vigente de auditoría. El aviso de cache no se considera verificación actual.

Los avisos previamente detectados en PhpSpreadsheet/PHPUnit siguen pendientes de comprobar/resolver. No se actualizaron ni retiraron dependencias en esta entrega. No afirmar que el proyecto está listo para producción.

## Antes de aplicar a la base local

1. Revisar cambios y obtener respaldo de esquema/datos actual; verificar restauración en entorno separado.
2. Probar migración/rollback y concesiones simultáneas en una base MySQL de pruebas, no distribuidora.
3. Confirmar usuarios/roles reales y que exista rol cliente para registro; no inventar un admin ni ejecutar seeders comerciales.
4. Autorizar ventana de aplicación, ejecutar migración nueva y designar usuario activo para bootstrap CLI. El código de acceso requiere este esquema; no desplegar archivos nuevos sin la migración.
5. Ejecutar bootstrap solo con ID confirmado y sesión local autorizada. Concede administración global excepto costos; tratarlo como operación sensible. Usuarios existentes deben iniciar sesión otra vez.
6. Validar login, recuperación con correo real, permisos y formularios desde navegador; revisar navegación, campos y archivos por rol.
7. Repetir auditoría de dependencias con red disponible y resolver avisos antes de cerrar I1.

No se aplicó la migración a distribuidora ni se ejecutó bootstrap. No se publicaron cambios ni se creó un respaldo de base de datos en esta entrega. La inicialización de permisos financieros se debe definir explícitamente al implementar costos; no se puede autoconceder desde UI.

## Pendientes para cerrar I1

Auditoría/dependencias, prueba MySQL y restauración, aprobación/aplicación controlada del esquema, inicialización de accesos, prueba UI/SMTP y revisión de autorización en todos los flujos legacy. La pantalla actual permite administrar reglas, no editar usuarios/roles completos ni producir informes de auditoría avanzados.
