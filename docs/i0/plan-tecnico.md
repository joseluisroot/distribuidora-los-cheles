# Plan técnico I0 → I1 y siguientes

Inspección estática local del 28 de agosto de 2026. No auditoría exhaustiva ni ejecución de endpoints. No se consultaron secretos ni se modificó la base.

## 1. Inventario de puntos actuales

| Área/ruta | Evidencia en repositorio | Acción planificada |
|---|---|---|
| /, /catalogo, /catalogo/{slug}, JSON | Routes.php y CatalogoController; SEO implementado previamente | Conservar presentación útil y SEO; limitar datos públicos y añadir contexto de sucursal |
| /login, /forgot, /reset | Auth.php y modelos de autenticación | I1: pruebas de sesión, token, expiración, uso único y errores; no cambiar contrato sin revisar vistas |
| /register | Ruta Auth::register; no función register en Auth.php inspeccionado | I1: resolver ruta/flujo; invitado no depende de cuenta obligatoria |
| /dashboard | Ruta Cliente\Dashboard::index | I1: contrastar clase/nombre y alcance antes de habilitar |
| /demo/* | Rutas GET ejecutan crear/agregar/confirmar/procesar sin filtro | I1: retirar de acceso normal; nunca ejecutarlas como prueba sobre base comercial |
| /carretilla y /carretilla/agregar | Rutas duplicadas; agregar frente a add; parte bajo auth | I1 ordenar superficie; I5 habilitar invitado con controles adecuados |
| /carretilla/remove y clear | Mutaciones por GET | I1: métodos de mutación protegidos y formularios compatibles |
| /productos/* | Grupo auth:admin; filtros basados en rol de sesión | I1: permisos de acción/datos, sin conceder administración a todos los vendedores |
| /productos/{id}/imagenes/* | Rutas fuera del grupo autenticado en Routes.php | I1: proteger endpoints/archivos y probar acceso directo; verificar controles internos también |
| /pedidos/{id} | PedidoController::ver consulta por ID sin propiedad/sucursal | I1: autorización por registro antes de devolver detalles/historial |
| /pedidos/cambiar-estado | Ruta bajo auth general | I1/I6: no permitir transición por simple texto enviado por usuario; permiso y guardas |
| /productos/importar/* | CSV sku,nombre,descripcion,precio_base,stock,is_activo | I2: conservar idea de previsualización; reemplazar stock absoluto por importación trazable |
| /migracion y /seed/run | Controladores usan token en query; Seed permite entorno production | I1: migrar administración a CLI/despliegue controlado; no describirlos como totalmente sin protección |
| Filters.php | CSRF comentado en before global | I1: inventariar formularios/AJAX y activar con pruebas, no romper envío de evidencias |
| PedidoModel | table=pedido_detalle y campos de detalle | I1/I2: separar cabecera/detalle/reportes y contratos de servicios antes de corregir aisladamente |
| CarretillaController | index usa cart.items; add usa cart[producto] | I5: contrato único por variante/presentación y pruebas de ida/vuelta |
| PedidoService::confirmar | lockForUpdate invocado; salida al confirmar y estado preparando | I1 evaluar bloqueo real; I5/I6 sustituir por reserva/verificación/despacho separados |
| InventarioService::ajustar | Stock leído/modificado y movimiento separado; sin control concurrente propio ni cantidad positiva explícita | I2: servicio transaccional compartido, validaciones e idempotencia |
| PedidoModel reportes | Pendientes retorna0; totaliza detalle como ventas | No mostrar indicadores ficticios; I4/I6 definir métricas por estado real |
| composer.json | CI ^4.7.4, PHP ^8.2, PhpSpreadsheet ^1.30, PHPUnit ^9.1 | I1: volver a auditar dependencias; no usar el informe anterior como resultado vigente |

Archivos citados están en app/Config, app/Controllers, app/Models y app/Services. Esta tabla es inventario de familias relevantes, no exportación exhaustiva de todas las rutas ni prueba de explotación. No se volvió a ejecutar la suite en I0: solo documentación.

## 2. Arquitectura propuesta

Monolito modular sobre CodeIgniter/MySQL. Controladores validan entrada y autorización; servicios de aplicación ejecutan casos de uso; modelos/repositorios encapsulan persistencia. Precios, reservas, inventario, pagos, preparación y traslados tienen contratos separados con una misma transacción cuando corresponde.

No introducir microservicios ni infraestructura distribuida por defecto. Notificaciones/alertas posteriores al commit con reintentos; no hacer depender consistencia de un SMS. La elección de consulta periódica/eventos para refrescar vistas es posterior; toda confirmación lee estado actual de servidor.

Proponer bloqueo de filas/actualización condicional y versiones sobre recursos compartidos, con orden consistente para varios productos. Idempotencia por operación; prueba en MySQL de concurrencia, no confiar solo en SQLite. Definir timeout/reintento y evitar que un reintento duplique la venta.

Conservar historial por eventos/movimientos operativos y versiones, sin exigir reconstruir toda la aplicación desde eventos. No usar floats para dinero nuevo; diseñar migración de campos existentes.

## 3. Secuencia de migraciones propuestas

No generar ni ejecutar SQL todavía. Hay 15 archivos de migración existentes; la cantidad de registros/datos debe consultarse de nuevo antes de cualquier migración. La base vacía reportada anteriormente no se presume vacía hoy.

| Lote | Incremento | Nuevas estructuras o adaptación |
|---|---|---|
| M1 | I1 | Permisos, relaciones rol/usuario, excepciones y alcance por sede, auditoría; revisar usuarios/sesión |
| M2 | I2 | Sedes/ubicaciones, variantes/presentaciones/precios versionados, lotes, bultos/componentes |
| M3 | I2 | Operaciones/movimientos/saldos e importaciones, condiciones y restricciones |
| M4 | I3 | Traslados, líneas, despachos, recepciones y diferencias |
| M5 | I4 | Órdenes de venta, referencias externas, conteos/recuentos/ajustes, devoluciones y registros financieros mínimos |
| M6 | I5 | Cliente independiente de cuenta; pedido/versiones, reserva/asignación, envío/cotización, acceso invitado |
| M7 | I6 | Evidencias/asignaciones de pago, revisión, extracción/reempaque, despacho/entrega y resolución de incidencias |

Las entidades base referenciadas en M5 deben crearse allí aunque se amplíen en M6/M7. La separación es conceptual; revisar claves/orden de creación en diseño SQL. No crear FK a tablas inexistentes ni construir una venta presencial dependiente de un checkout web aún no entregado.

Estrategia: ampliar esquema → migrar datos con mapeo explícito → verificar totales/relaciones → cambiar lectores/escritores → retirar caminos antiguos solo tras validación. No inventar lotes, ubicaciones o pagos para datos históricos sin aprobación. Si se confirma base vacía, usar migraciones nuevas reproducibles igualmente; no reescribir migraciones aplicadas para ocultar historia.

Prohibir doble escritura independiente de stock viejo/nuevo. En corte, detener las operaciones afectadas y conciliar. Respaldo y restauración probados antes de cambios; no asumir que down() revierte ventas posteriores. Corrección hacia adelante o restauración con conciliación según incidente.

## 4. Plan de pruebas

Existentes inspeccionadas: tests/unit/ProductoControllerTest.php, SeoMetadataTest.php, HealthTest.php y ejemplos de sesión/base. Conservar regresiones útiles; no hay cobertura suficiente de nuevo negocio por existir esos archivos.

- I1: extender pruebas de autenticación/permisos; endpoints de mutación, CSRF, propiedad y filtrado de campos/documentos. Probar concesión, revocación y excepciones D6.
- I2: precios 2/3 por presentación, equivalencias, precisión, carga inválida/duplicada, integridad de saldos/lotes.
- I3/I4: recepciones parciales/idempotentes, daños, salida presencial única, corte de conteo y ajuste autorizado.
- I5/I6: reloj controlado, dos conexiones MySQL por último stock, carreras de reporte/vencimiento/verificación, copia de vencido, asignación de pagos no duplicada, cambio fallido y despacho único.
- UI: pruebas de recorrido para tareas críticas, ausencia de información sensible en respuesta y adaptación a dispositivos reales. Las verificaciones visuales no sustituyen prueba de autorización.
- I7: restaurar respaldo aislado y conciliar cantidades/pagos/documentos; aceptación con personal.

Entorno separado y fixtures explícitos. Nunca pruebas destructivas contra distribuidora sin aprobación y aislamiento. Sin llamadas reales a proveedores en unitarias; una integración simulada se etiqueta, y se valida por separado antes de producción.

## 5. Preparación de I1

1. Aprobar D6 y mapa de acciones inicial.
2. Capturar estado de trabajo y respaldo sin borrar cambios existentes.
3. Revisar esquema/usuarios reales de forma controlada y definir migraciones M1.
4. Corregir superficie de rutas/autenticación y CSRF con tests de regresión.
5. Implementar permisos efectivos con ámbito, auditoría y revocación; demostrar accesos positivos y negativos.

I1 no comienza automáticamente por redactar este plan. No se han concedido permisos a usuarios reales, creado cuentas administrativas ni publicado cambios.
