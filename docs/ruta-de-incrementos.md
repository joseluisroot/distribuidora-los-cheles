# Ruta de incrementos - Distribuidora Los Cheles

Fecha: 28 de agosto de 2026. Propuesta de ejecución basada en las decisiones confirmadas. No constituye aprobación de las políticas pendientes, estimación de fechas ni declaración de funcionalidad implementada.

Fuentes: contexto-del-negocio.md, decisiones-operativas-confirmadas.md y decisiones-2026-08-28.md. Esta ruta reemplaza la secuencia preliminar del diagnóstico para planificar entregas; conserva sus riesgos y condiciones de lanzamiento.

## Principios

- Conservar CodeIgniter y componentes útiles; corregir seguridad y rediseñar pedidos/inventario donde sea necesario.
- Entregar flujos demostrables con pruebas, permisos, manejo de errores y UX, no solo pantallas o tablas.
- Cada incremento se valida en entorno de pruebas. Terminar un incremento no autoriza producción, cargas reales ni integraciones de pago/mapas/mensajería.
- Inventario y traslados preceden a ventas: central abastece, sucursal prepara web, sala y bodega separadas.
- UX/UI operativo acompaña todas las entregas: navegación, estados, formularios, accesibilidad y adaptación a dispositivos. La decoración y detalles visuales finales quedan diferidos.
- Datos de prueba explícitos y aislados; no alterar inventario real para demostrar resultados.

## Decisiones que permanecen abiertas

| ID | Decisión | Propuesta para revisar | Cerrar antes de |
|---|---|---|---|
| D1 | Fardos y unidades del mismo producto en una compra | Calcular cada presentación por separado; mínimos confirmados de 3 unidades o 3 fardos, sin combinaciones automáticas | I2 |
| D2 | Momento de fijar precios y envío | Guardar importes al aceptar el pedido y no alterarlos por cambios posteriores de catálogo/tarifas | I2 diseño; I5 implementación |
| D3 | Verificación del contacto invitado | Elegir canal, mecanismo seguro, proveedor y costos si aplica | I5 |
| D4 | Transporte | Distancia por ruta desde sucursal; elegir proveedor, cobertura, rangos, importes, mínimo gratuito y alternativa ante fallo | I5 |
| D5 | Movimientos durante conteo | Bloqueo breve por zona o corte y conciliación; frecuencia por sede/riesgo | I4 |
| D6 | Permisos efectivos | Precedencia entre rol/excepción individual, alcance por sucursal y límites de delegación | I1 |
| D7 | Cambios posteriores al envío | Roles, límites por estado, autorización del cliente y actualización atómica de reserva/importes | I5 diseño; I6 completo |
| D8 | Conciliación y reembolsos | Tratamiento de dinero tardío, diferencias de importe y referencia del trámite externo; solo superiores | I4 básico; I6 web |
| D9 | Facturación externa | Identificador, campos, relación una/varias facturas, anulaciones y evidencia, sin integración automática asumida | I4 |
| D10 | Alertas y entrega | Umbrales/responsables de revisión y evidencia de entrega; no liberación automática por alerta | I6 |
| D11 | Carga inicial y bultos | Datos disponibles, fecha/costos desconocidos, equivalencias, códigos y posible contenido mixto de cajas | I2 |

No reabrir: invitados aprobados; mayoreo desde 3 por presentación; pago completo con varias transferencias posibles; reserva de una hora extendida por pago reportado; liberación manual autorizada; elección de sucursal; web sin stock de sala; reempaque con trazabilidad; salida física al despachar; permisos sensibles restringidos; factura externa vinculada a orden de venta.

## I0. Especificación y recorridos de trabajo

**Resultado:** acuerdo verificable sobre lo que se construye.

Entregables: glosario, reglas con identificadores, estados separados de pedido/pago/reserva/preparación/traslado, matriz inicial de roles, modelo de datos conceptual y bocetos de compra, venta presencial, pickeo y traslado. Inventario de rutas y fallos del código actual, plan de migraciones y pruebas.

Aceptación: recorrer escenarios normales y excepcionales con ejemplos de unidades/fardos, reservas, daños y devoluciones; cada duda tiene responsable y etapa de resolución. Propuestas no aprobadas quedan identificadas. No exigir cerrar todos los importes o proveedores para comenzar I1.

Dependencia: ninguna. No se considera completado por existir esta ruta.

## I1. Base segura y acceso por responsabilidades

**Resultado:** cada persona accede únicamente a operaciones y datos autorizados.

Entregables: corrección de autenticación, registro/recuperación y rutas inseguras; protección CSRF en flujos aplicables; autorización por acción/registro/sucursal; roles y excepciones por usuario; consulta de permisos efectivos; auditoría. Revisar dependencias con avisos, configuración por entorno y resguardo de secretos. Migraciones revisadas y respaldo/restauración de prueba.

Aceptación: vendedor no obtiene costos ni modifica precios/tarifas por URL/API; bodega no recibe importes en respuestas, documentos o exportaciones; usuarios no consultan pedidos ajenos ni se elevan permisos. Revocación efectiva probada. Pruebas existentes más pruebas de autorización; corregir dependencias sin declarar seguridad absoluta.

Dependencias: I0 y D6. El módulo de identidad puede preparar permisos futuros aunque aún no existan todas las pantallas.

## I2. Productos, presentaciones e inventario trazable

**Resultado:** conocer qué existe, de dónde proviene y dónde está.

Entregables: producto/variante, unidades base, fardos/equivalencias y cuatro precios; central/sucursales, sala/bodega, ubicaciones; lotes, antigüedad, costos restringidos, estados y cajas identificadas; movimientos auditables; carga inicial validada con prevención de duplicados. Separar catálogo, saldos iniciales y costos.

Aceptación: 3 unidades o 3 fardos activan su escala; 1 fardo de 10 no activa mayoreo de fardos por contener 10 unidades. Abrir/reempacar no aumenta stock. Una carga se explica por línea/lote/ubicación y reintentar no duplica cantidades. Reporte por sede y condición concilia con movimientos. Las fechas desconocidas no se inventan.

Dependencias: I1, D1, D11 y definición de precios de D2. Reserva comercial se completa en I5; sus saldos y restricciones deben estar previstos desde el modelo.

## I3. Abastecimiento y traslados completos

**Resultado:** enviar desde central y recibir en sucursal sin perder trazabilidad.

Entregables: hoja de traslado, bultos/contenido, fecha/hora de salida, tránsito, recepción real, diferencias, daños y cierre autorizado. Traslados internos sala/bodega con ubicaciones reales. Consulta por producto/sede/tránsito.

Aceptación: 100 enviadas y 98 recibidas dejan 2 pendientes, sin forzar recepción ni disponibilidad. Si llegan 2 dañadas, se distinguen de faltantes. Cajas y unidades no se suman dos veces; lote/costo/antigüedad se conservan. Reintentar recepción no duplica ingreso. Mercancía de sala solo abastece web después de traslado real y registrado.

Dependencia: I2. La resolución de diferencias requiere permisos de I1 y política documentada; no basta imprimir la hoja.

## I4. Operación presencial, conteos y correcciones

**Resultado:** completar una venta de tienda y mantener existencias conciliadas.

Entregables: selección/validación, referencia de cobro, entrega y salida única; orden de venta y vínculo a factura externa. Devoluciones recibidas en revisión, bajas por daño y reembolsos como registros separados; ajustes solo superiores. Conteos programables por zona, recuento y aprobación.

Aceptación: vender 3 descuenta 3 en la ubicación real, y vincular factura no vuelve a descontar. Producto dañado no reaparece como vendible. Un reembolso sin retorno físico no crea stock. Conteo incluye físico reservado y no solo disponible; diferencias requieren autorización. Trámite de factura externa queda registrado, no simulado como ejecutado.

Dependencias: I3, D5, D8 básico y D9.

Hito: operación interna demostrable. No abrir al público sin validación y autorización de lanzamiento; si se desea un piloto solo presencial, debe acotarse y aprobarse expresamente.

## I5. Compra en línea hasta reserva y pago reportado

**Resultado:** cliente invitado o registrado envía un pedido con disponibilidad y total claros.

Entregables: catálogo/carretilla corregidos, selección de sucursal, unidades/fardos, contacto verificable y seguimiento privado; retiro/envío, tarifa aceptada; pedido con precios guardados, reserva atómica y hora de vencimiento; carga de evidencia de pago. Copiar pedido vencido a borrador nuevo, sin heredar pago ni reserva.

Aceptación: carretilla no reserva; dos clientes por la última unidad no reservan ambas veces; nunca se toma stock de sala/central/tránsito. Vencimiento sin reporte libera una vez; reporte vigente mantiene reserva en revisión. Cambio de sucursal revalida. Error de cálculo de ruta no produce envío gratis. Invitado no accede a datos ajenos.

Dependencias: I4, D2, D3, D4 y reglas de edición D7. Proveedores externos requieren autorización y configuración; no sustituir verificación real por una demostración presentada como producción.

Este flujo se demuestra en pruebas; no se aceptan pedidos públicos hasta poder cumplirlos con I6.

## I6. Pago verificado, preparación y entrega

**Resultado:** completar el ciclo web y resolver sus incidencias.

Entregables: verificación de efectivo/transferencias múltiples que cubren el total; bandeja de revisión con responsable/antigüedad/alertas; liberación manual; cambios de pedido con revisión; pickeo PEPS, reempaque y procedencias; faltantes con autorización; orden de venta/impresión para facturación; despacho y entrega confirmada. Conciliación de pagos tardíos, reembolsos y referencias externas por superiores.

Aceptación: pago reportado no permite preparar sin verificación; confirmar pago no vuelve a descontar disponible. Despacho descuenta físico una sola vez y entregado no repite salida. Reempaque de varios lotes conserva cantidades y costos restringidos. Vencimiento/verificación/liberación simultáneos no generan doble asignación. Un cambio fallido mantiene el estado anterior coherente. Dinero tardío no reactiva pedido vencido ni se aplica dos veces.

Dependencias: I5, D7, D8 completo y D10. Datos financieros visibles solo según permisos.

## I7. Piloto integral y preparación de lanzamiento

**Resultado:** evidencia de que el equipo puede operar y recuperar el sistema.

Entregables: prueba central → traslado → sucursal → venta presencial/web → factura externa referenciada → devolución/conteo; capacitación y manuales breves por rol; respaldo/restauración probado; monitoreo y procedimientos de incidencias. Validar rendimiento con volumen acordado, uso móvil/escritorio y vistas previas públicas WhatsApp/Facebook en entorno autorizado.

Aceptación: stock físico/movimientos/saldos concilian; pagos y documentos conciliables; permisos y concurrencia probados; restauración verificada; personal completa tareas críticas. Sin bloqueos de seguridad, inventario o cobros abiertos. Autorizar producción y carga real por separado, con plan de recuperación y límites de piloto.

Dependencia: I1-I6 completos para sus flujos y decisiones de operación cerradas. No reemplazar verificación de negocio por conteo de pruebas automatizadas.

## Después del piloto: parking lot

Fidelización/puntos, recomendaciones, PWA sin conexión y detalles visuales finales permanecen fuera de estos incrementos. La instalación PWA puede evaluarse sin habilitar transacciones sin conexión, pero no se promete en el piloto ni exige activación de servicios. Campañas e integraciones de analítica requieren aprobación propia.

## Definición común de terminado

Cada incremento entrega interfaz utilizable, validaciones de servidor, permisos, auditoría donde aplique, migraciones y datos de prueba seguros, pruebas proporcionales al riesgo, demostración del flujo con errores y documentación actualizada. Registrar lo que no se verificó; no declarar terminado un flujo con un sustituto ilustrativo de un paso obligatorio.

## Seguimiento y estimaciones

Estado al 28 de agosto: usuario autorizó continuar desde I0 y aprobó denegación prioritaria D6. I1 en progreso, con [implementación inicial y verificación aislada](i1/README.md); no cerrado ni aplicado a base comercial. I2-I7 pendientes. Los trabajos previos de framework, base y SEO son insumos, no equivalen a incrementos terminados.

Antes de estimar calendario: número de sucursales/usuarios, volumen SKU y pedidos, equipo disponible, dispositivos, infraestructura y proveedores externos. Estimar esfuerzo después de desglosar I0-I1, revisar al terminar cada incremento y no prometer fechas sin datos.

Siguiente paso: completar verificaciones y aplicación controlada de I1 descritas en su informe. Mantener una lista de decisiones con propuesta, aprobación y criterio de aceptación; no iniciar I2 dando I1 por terminado.
