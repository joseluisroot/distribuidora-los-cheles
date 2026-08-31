# I2.1 — Base de sedes, almacenes y ubicaciones

Primera entrega técnica de I2. No cierra I1, no completa I2 y no habilita producción.

## Implementado

- Migración 2026-08-28-000002_CreateWarehouseStructure con tablas sedes, almacenes y ubicaciones. No crea sedes ficticias ni convierte saldos del inventario antiguo.
- Las instalaciones nuevas crean estas tablas en InnoDB. La migración correctiva 2026-08-29-000001 convierte instalaciones locales previas y repone relaciones foráneas que MyISAM hubiera ignorado; no modifica cantidades ni crea datos comerciales.
- Jerarquía sede → almacén → ubicación. Tipo de sede: central/sucursal; tipo de almacén: bodega/sala. Cada entidad tiene estado activo y el servicio valida tipos, códigos, nombres y posiciones antes de escribir.
- Código de sede único; código de almacén único por sede; código y posición pasillo/fila/columna únicos por almacén. Relaciones sin eliminación en cascada. El modelo inicial no supone niveles adicionales de estantería.
- Servicio de lectura WarehouseStructure. canSupplyWeb solo evalúa elegibilidad estructural: exige bodega de sucursal con toda la jerarquía activa. No comprueba stock, asignación de sucursal del pedido, permiso del operador ni pago; no debe usarse solo para autorizar una reserva.
- Consulta /inventario/ubicaciones, protegida por inventory.view global. Sin migración muestra aviso y HTTP 503; instalada sin datos muestra estado vacío. No muestra costos, cantidades ni movimientos de inventario. Enlace desde Mi panel para usuarios autorizados.
- Administración inicial protegida por `inventory.adjust`: alta de sedes, almacenes y ubicaciones, además de activación/desactivación con motivo obligatorio. Cada escritura es transaccional y genera un registro en `warehouse_audit`. No permite eliminar físicamente registros ni cargar existencias.

## Verificación

Suite PHP y pruebas JavaScript cubren instalación vacía/rollback, exclusión central/sala, jerarquía inactiva, unicidad física, escrituras auditadas, rollback ante duplicados, visibilidad por permiso, CSRF, redirección de invitados y confirmaciones. Las migraciones 000001 y 000002 del 29 de agosto fueron aplicadas en la base local `distribuidora`; las tres tablas estructurales quedaron InnoDB con dos relaciones foráneas. No se cargaron sedes ni existencias ficticias. La pantalla requiere una última validación autenticada con nombres reales antes del cierre del PR.

## Instalación local

La instalación local está al día. En otros entornos se debe revisar primero `php spark migrate:status`, respaldar y luego aplicar migraciones pendientes. No ejecutar seeders comerciales ni rollback sobre información operativa. El `down` de la estructura elimina tablas y datos, por lo que solo debe usarse en fixtures aislados.

## Siguiente entrega

Completar la validación visual autenticada y definir los nombres/códigos reales antes de registrar datos comerciales. Después: variantes, presentaciones, lotes, carga inicial y movimientos. No cargar stock manualmente en estas tablas.

Faltan nombres reales de sedes y confirmación de cajas mixtas, cálculo independiente de precios por presentación y momento de fijación del precio. Esas decisiones no se consideran aprobadas por comenzar esta entrega. No se ha configurado una sucursal web predeterminada. Los permisos por sucursal existentes aún deben vincularse a sedes reales antes de habilitar administración por ámbito.
