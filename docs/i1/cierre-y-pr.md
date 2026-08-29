# I1 — Evidencia de cierre y preparación de PR

## Aceptación del usuario y cierre funcional

El usuario acepta cerrar funcionalmente I1 y continuar con I2, aplazando expresamente la prueba de correo al servidor de producción. Esta decisión sustituye la exigencia de recepción SMTP local como condición para avanzar. No convierte la prueba diferida en una prueba ejecutada.

Estado: **I1 aceptado funcionalmente; integración mediante PR pendiente**. Las comprobaciones visuales autenticadas que no se ejecutaron siguen declaradas como pendientes, no como aprobadas por pruebas. Antes de habilitar recuperación al público en el hosting: configurar SMTP sin exponer secretos, probar con destinatario autorizado, verificar recepción, enlace HTTPS/dominio correcto, expiración/uso único y acceso posterior. No se autoriza envío de correos ni despliegue por esta aceptación.

Siguiente paso de Git: separar el árbol acumulado para que el PR I1 excluya I2, revisar y crear commits, publicar la rama y revisar el PR antes del merge. No se hizo commit, push ni merge en esta aceptación. I2 continúa con administración auditada de sedes, almacenes y ubicaciones; su base inicial no se considera completada por el cierre funcional de I1.

Las expresiones de «no aprobado para merge» y listas de bloqueo de secciones anteriores describen el estado previo; los requisitos de revisión del PR y los riesgos no verificados permanecen vigentes.

## Verificación posterior del estado local y respaldo

La inspección posterior encontró la migración 2026-08-28-000003 ya registrada y roles, users, password_reset_tokens, access_lock, access_grants y access_audit en InnoDB. No se reaplicó ninguna migración durante esta verificación. También estaban presentes las tablas I2; la observación no supone aprobación de I2.

Se generó un respaldo SQL de las 18 tablas de distribuidora mediante mysqldump con bloqueo global de lectura (compatible con tablas MyISAM). Antes se verificó que no existieran vistas, rutinas, eventos ni triggers que exigieran otro tratamiento. Se restauró en un esquema local aleatorio, se compararon cantidades y hashes de registros de cada tabla con el origen y se eliminó exclusivamente el esquema temporal creado. El origen se volvió a comparar y sus registros se conservaron. No se imprimieron datos de usuarios ni credenciales.

Respaldo local excluido de Git: `writable/backups/i1-20260828-162153-6dafb357/distribuidora.sql`. SHA-256: `9a1a4631f2f5a5cbe4699f60a3d56b7553539309e23370bf38c9193dd1df55df`. Contiene datos sensibles; no adjuntar al PR. Credenciales temporales del cliente MySQL eliminadas al terminar. Directorio fuera de public, con denegación Apache y regla de ignore. Este respaldo no incluye archivos subidos ni sustituye una política de copias externas/cifradas.

**Resuelto el punto 1 de los pendientes de aplicación local de I1.** Siguen pendientes revisión autenticada, SMTP real y separación Git del PR. Las tablas operativas de inventario/I2 observadas aún usan MyISAM: revisar y corregir en I2 antes de habilitar escrituras de inventario; no extrapolar la garantía transaccional de I1 a todo el sistema.

## Resultado de la revisión del 28 de agosto de 2026

**No aprobado para merge todavía.** Esta lista reemplaza las afirmaciones históricas de que MySQL no se había probado, pero no equivale a autorización de producción.

Se ejecutó I1MySQLTest contra el servidor local, creando dos esquemas aleatorios `i1_verify_*` exclusivamente de prueba. Se eliminaron al finalizar. No se consultaron usuarios comerciales ni se modificó distribuidora.

### Hallazgo y corrección

El servidor usa MyISAM por defecto: access_lock creado sin motor no bloqueaba transaccionalmente la segunda conexión. Es un defecto real, no una diferencia cosmética entre SQLite y MySQL.

- La migración original de accesos ahora especifica InnoDB para instalaciones nuevas.
- La migración correctiva **2026-08-28-000003_EnsureTransactionalSecurityTables** convierte las tablas de identidad y accesos existentes a InnoDB. No depende de la migración 000002 de I2. Su rollback conserva InnoDB deliberadamente.
- AccessService y bootstrap rechazan mutaciones si las tablas de accesos no son InnoDB. La lectura no concede permisos adicionales. En bases aún sin corregir, la administración puede mostrar el aviso de migración y no permitirá guardar cambios.
- La conversión no restaura claves foráneas que MyISAM haya ignorado anteriormente; revisar integridad/restricciones existentes antes de producción. ALTER TABLE no es transaccional y puede bloquear tablas; requiere respaldo y ventana sin operaciones.
- No se ejecutó la migración correctiva en la base comercial/local del usuario. No ejecutar migrate indiscriminadamente desde la rama mezclada: también está pendiente la migración I2 000002.

### Evidencia ejecutada

- Dos conexiones MySQL distintas; motor InnoDB comprobado; bloqueo retenido por la primera y timeout explícito de la segunda. No queda cambio auditado del intento fallido.
- Concesión y revocación posteriores al bloqueo correctas; prueba de rechazo explícito de MyISAM y conversión correctiva.
- Copia lógica en memoria de DDL y filas de roles, users, access_lock, access_grants y access_audit; restauración en el segundo esquema y comparación exacta de filas. Solo fixtures: no prueba un respaldo completo del negocio, archivos subidos, rutina operativa de backup ni recuperación ante desastre.
- Rollback/reaplicación de migración de accesos conservando los dos usuarios ficticios.
- Correo de recuperación: prueba con mailer simulado que devuelve false, mantiene respuesta genérica. Se registra el fallo sin datos sensibles. No se envió correo real.
- Comando PowerShell opt-in: `$env:I1_MYSQL_TESTS = '1'; php vendor/bin/phpunit --no-coverage`. Resultado del árbol actual: **50 pruebas / 203 aserciones correctas**, incluyendo las pruebas de I2 que aún conviven sin commit. Sin la variable, la prueba MySQL se omite; no interpretar ese skip como éxito de MySQL.

## Pendientes para aprobar I1

1. Respaldar la base local real y revisar/aplicar la corrección InnoDB en un checkout que excluya I2; comprobar motores y administración después de migrar. No prometer cero interrupciones.
2. Revisión autenticada de accesos, filtros de tabla, cancelación de confirmación y pantalla 403. No cambiar permisos del único administrador para probar.
3. Configurar/verificar SMTP con un destinatario autorizado y comprobar recepción/recuperación real. No publicar credenciales en chat, Git ni PR.
4. Separar cambios acumulados de I1 e I2, revisar archivos y secretos, ejecutar pruebas sobre el árbol exacto del PR. El árbol actual sigue en feature/i2-inventario-trazable, sin commits nuevos; no crear un PR con todo ese contenido como si fuera solo I1.

## Frontera propuesta del PR I1

Incluye actualización CI/dependencias, autenticación, RBAC/auditoría/403, navegación y presentación aprobada, build local, favicon, corrección InnoDB y pruebas I1. Excluye WarehouseController, WarehouseStructure, CreateWarehouseStructure (000002), vista inventario/ubicaciones, WarehouseStructureTest, docs/i2 y los hunks de rutas, Dashboard y pruebas que introducen ubicaciones. Conservar las decisiones de negocio como contexto, sin presentar funciones I2 como listas.

No se creó PR, no se hizo commit/push ni merge. La integración será mediante PR a main después de revisar evidencia y cumplir los pendientes. No ignorar advertencias ni omitir pruebas para obtener un estado verde.
