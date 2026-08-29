# Diagnóstico integral — Distribuidora Los Cheles

> Actualización vigente del 28 de agosto: consultar [Decisiones del 28 de agosto](decisiones-2026-08-28.md). Sustituye umbrales, propuestas de invitados y permisos anteriores incompatibles; no acredita implementación.

Fecha: 27 de agosto de 2026.

## 1. Dictamen ejecutivo

El proyecto tiene potencial para convertirse en una plataforma comercial y logística para venta al detalle y mayoreo, conectando adquisición de clientes, pedidos, pagos verificados, preparación, inventario por bodega y entrega de documentos para facturación externa.

La propuesta de valor no debe depender únicamente del aspecto de la tienda: debe permitir comprar sin confusiones, preparar sin errores y explicar cada unidad de inventario y cada importe. Una campaña que produce pedidos imposibles de surtir no es un éxito operativo ni financiero.

Recomiendo conservar CodeIgniter y el trabajo útil, pero rediseñar el núcleo de pedidos, inventario y permisos. No basta con ampliar la tabla actual de stock. Tampoco hay evidencia de que necesitemos microservicios, una aplicación nativa o una sustitución completa del framework.

**Estado actual:** base técnica recuperada y actualizada, pero no sistema listo para producción. El alcance comercial y logístico está mucho más desarrollado en la documentación que en el código.

Este documento distingue requisitos confirmados, implementación y recomendaciones. Se complementa con docs/decisiones-operativas-confirmadas.md, que detalla las últimas aclaraciones. Las recomendaciones requieren aprobación y no están implementadas por escribirlas. No es una auditoría fiscal, de seguridad completa ni una validación de la web publicada.

## 2. Qué tenemos realmente

| Componente | Situación comprobada |
|---|---|
| Framework | CodeIgniter 4.7.4, actualizado desde 4.4.8 |
| PHP | CLI 8.3.28; Apache se verificó previamente configurado para esa versión |
| Base local | MySQL, distribuidora; 12 tablas, 15 migraciones registradas |
| Datos | Tablas comerciales y usuarios vacíos en la comprobación de este informe |
| Pruebas | 13 pruebas y 28 aserciones correctas, ejecutadas de nuevo para este diagnóstico |
| Catálogo | Código para búsqueda, paginación, fichas, galería y vista rápida |
| Productos | Código para altas, edición, borrado lógico, imágenes y escalas |
| Importación y kardex | Implementación básica, sin lotes ni trazabilidad completa de cambios de stock |
| Pedidos/carretilla | Implementación parcial con bloqueos conocidos |
| Marketing técnico | Metadatos SEO, canonical, Open Graph y tarjetas sociales implementados localmente |
| PWA, bodegas, pickeo, permisos granulares | Requisitos documentados; no implementados |
| Publicación | No se ha desplegado este trabajo ni verificado las vistas previas en redes con URLs públicas |
| Diseño | No se han creado prototipos ni ejecutado pruebas de usabilidad del nuevo alcance |

Las pruebas actuales cubren ejemplos básicos, una regresión POST y metadatos. No prueban ventas concurrentes, lotes, traslados, pagos o el funcionamiento integral.

Bloqueos que siguen presentes: PedidoModel apunta a pedido_detalle; la carretilla guarda y lee estructuras distintas; la ruta de agregar incluye un método inexistente; el servicio invoca lockForUpdate sin implementación encontrada; faltan comprobaciones de propiedad de pedidos; existen rutas demo y de imágenes sin los controles necesarios; CSRF sigue deshabilitado; registro, recuperación y paneles tienen partes incompletas.

La auditoría de dependencias realizada durante la actualización reportó avisos en PhpSpreadsheet y PHPUnit. No se ha repetido esa consulta remota en este informe. Deben resolverse antes de producción; evaluar retirar la dependencia de hojas de cálculo si se confirma que no se utiliza.

## 3. Alcance y decisiones abiertas

### Confirmado o solicitado

- Venta al detalle y mayoreo; regla confirmada: desde tres unidades sueltas del mismo producto y características, según actualización del 28 de agosto. No se mezclan variantes. Fardos tienen precios normal/mayorista separados y mínimo confirmado de 3 fardos iguales.
- Catálogo y lista de intereses sin login; pedidos del cliente o de un vendedor.
- Pago manual por transferencia o efectivo. En línea: carretilla sin reserva; aceptar el pedido reserva stock durante una hora. Si el pago se reporta con reserva vigente, mantenerla durante la revisión aunque pase la hora. Verificar confirma la asignación; vencer sin reporte libera. La venta presencial es de entrega directa y no utiliza esta reserva de una hora.
- Seguimiento del cliente: recibido, pendiente de pago, preparando y despachado.
- Unidades como base del inventario, equivalencias por producto, fardos cerrados y abiertos. Reempaque permitido para completar la presentación del mismo producto y características, con trazabilidad de cantidades por lote y ubicación de origen.
- Lotes/origen/antigüedad, prioridad de extracción del inventario antiguo y costos de distintas compras.
- Centro de distribución y tienda/sucursal, ubicaciones por pasillo/fila/columna y hojas de traslado.
- Consulta y reporte de existencias por producto y bodega.
- Pickeo, trazabilidad hacia el pedido y documento final para facturación externa.
- Devoluciones, conteos físicos y conciliación del stock.
- Permisos administrables; vendedores sin costos, bodega sin precios ni costos.
- Diseño moderno, minimalista, agradable, ágil y adaptable; uso como PWA.
- Compartir productos en WhatsApp/Facebook, preparar campañas y medir comportamiento comercial.

### No decidido todavía

- Importes de los precios mayoristas por producto y posibles escalas adicionales. Unidades sueltas desde tres iguales; fardos desde tres iguales; combinación de presentaciones pendiente.
- Completar precios por presentación: 3 fardos de 10 equivalen a 30 unidades y califican para mayoreo; confirmar cálculo general por unidades base y precio de 1 fardo frente a unidades sueltas.
- Método de verificación de contacto y seguimiento seguro para invitados; compra sin cuenta aprobada el 28 de agosto.
- Confirmados efectivo en sucursal y pago completo, admitiendo varias transferencias verificadas. Pendientes prórrogas y conciliación del dinero recibido tras vencer la reserva; el pedido vencido requiere uno nuevo.
- Resolución de pagos recibidos cuando ya no hay disponibilidad.
- Reasignación excepcional de sucursal después de enviar: el cliente ya puede elegir sucursal, con configuración por defecto como propuesta inicial. Web solo usa su bodega, nunca sala. Las ventas presenciales descargan en Los Cheles y se facturan externamente.
- Nombre final del documento: se propone orden de venta, lista para facturar.
- Detallar tarifas por distancia/transporte y mínimo de envío gratuito. Confirmados retiro, domicilio, estado entregado, consulta por faltantes y parcial autorizado con devolución de lo no servido. Pendientes procedimiento de reembolso, cancelaciones y sustituciones.
- Reglas de puntos, privacidad y canales de notificación.
- Cantidades concretas de usuarios, SKU, pedidos diarios y sucursales, presupuesto y disponibilidad exigida.

No se incluyen por defecto facturación fiscal, contabilidad completa, nómina, crédito a clientes, marketplace ni venta para múltiples empresas independientes.

## 4. Perspectiva de mercadeo

### Posicionamiento y experiencia de compra

Propuesta de promesa comercial: comprar por unidad o volumen con precios comprensibles, información de disponibilidad confiable y seguimiento del pedido. Es una propuesta estratégica, no un eslogan aprobado.

Diseñar dos recorridos sobre el mismo catálogo. El comprador de detalle necesita descubrir productos y entender qué recibe; el mayorista necesita búsqueda por SKU, cantidades rápidas, presentaciones, ahorro y repetición de compras. No asumir que son segmentos excluyentes ni duplicar catálogos.

La ficha debe mostrar fotografía clara, descripción útil, contenido del fardo/caja, precio total de la presentación y equivalente unitario cuando corresponda. Nunca destacar un precio sin explicar el mínimo o el empaque necesario para obtenerlo. No confundir preferencia de cliente mayorista con la regla de precio de una compra.

Aprobado el 28 de agosto: compra como invitado con contacto verificable y cuenta opcional para historial; definir el mecanismo de verificación y controles de abuso. El seguimiento de invitados requiere un mecanismo seguro; un número de pedido no debe revelar datos a cualquiera.

### Embudo y medición

Embudo propuesto: campaña → producto → lista/carretilla → pedido enviado → pago reportado → pago verificado → pedido cumplido → recompra.

Cada paso necesita eventos distintos. Pedido enviado no equivale a venta cobrada. El reporte comercial debe distinguir importes solicitados, verificados, despachados, devueltos y netos según la definición acordada. No enviar costos o márgenes a plataformas publicitarias.

Registrar origen UTM y canal interno: web, vendedor o tienda. Una compra creada por vendedor no debe atribuirse a publicidad solo porque el vendedor abrió un enlace con parámetros. Definir si se conservará primer contacto, último contacto u otro modelo; la atribución no demuestra causalidad.

Indicadores: conversión a pedido, porcentaje de pedidos pagados, ticket de pedidos pagados, recompra por cohorte, cancelaciones por faltante y costo de adquisición. ROAS solo mide ingresos atribuidos frente a gasto publicitario; debe acompañarse de margen y devoluciones. Si marketing no tiene permiso financiero, gerencia recibe ese análisis restringido.

### SEO, redes y fidelización

Los metadatos ya agregados son un inicio, no toda la estrategia SEO. Faltan decidir taxonomía, categorías, sitemap, datos estructurados, redirecciones y gobierno editorial. Los slugs actuales cambian al renombrar productos: conservar URLs o mantener redirecciones es necesario para no perder enlaces de campañas. Google describe prácticas de estructura de URLs para comercio electrónico. [Google Search Central](https://developers.google.com/search/docs/specialty/ecommerce/designing-a-url-structure-for-ecommerce-sites).

No retirar una ficha automáticamente por agotamiento temporal; diseñar cómo comunicar agotado y sugerir alternativas sin aceptar pedidos imposibles. Una red puede conservar tarjetas antiguas: el precio actualizado debe comprobarse en la página y el pedido.

Iniciar recomendaciones con relaciones curadas por categoría, uso y compatibilidad. Más adelante usar compras conjuntas válidas, considerando devoluciones, stock y tamaño de muestra. No exigir IA para una primera versión útil.

Los puntos requieren reglas de emisión, redención, vencimiento, devoluciones y límites económicos. No premiar pedidos sin pago verificado. Propongo posponer su activación hasta contar con transacciones y controles confiables.

## 5. Perspectiva de gerencia de procesos

### Un flujo visible y estados internos separados

Conservar los cuatro estados simples para el cliente, pero separar internamente el estado del pedido, pago y cumplimiento. Un pedido puede tener pago reportado sin verificar, o pago verificado con incidencia de preparación; una sola etiqueta no explica ambos.

Flujo en línea: carretilla sin reserva → envío y aceptación del pedido con validación y reserva atómicas → pago pendiente con reserva de una hora → pago reportado/en revisión con reserva mantenida → pago verificado y asignación confirmada → pickeo → control de preparación → documento final → facturación externa → despacho → entrega/cierre. Si vence sin reporte de pago, liberar la reserva. Reportar no equivale a verificar ni autoriza preparación. Entregado con confirmación está aprobado; faltan evidencia de entrega y mecanismo de comprobación de facturación externa. La venta presencial tiene entrega directa y un flujo separado, sin la espera de una hora.

### Reserva temporal al enviar el pedido

El usuario aclaró que el inventario permanece libre mientras el producto está solo en la carretilla. Enviar y aceptar el pedido sí congela sus unidades durante el plazo de pago. Esta aclaración sustituye la interpretación anterior de «pago sin reserva».

La validación de disponibilidad y la reserva deben realizarse juntas, de forma atómica. Si otro cliente ya reservó el producto, una carretilla anterior no garantiza existencia: debe mostrar la indisponibilidad y no permitir confirmar unidades que no se pueden reservar. Verificar el pago transforma la reserva temporal en asignación confirmada sin descontar dos veces el disponible; vencer libera solo la reserva sin pago reportado, no aumenta el stock físico.

La hora debe gestionarse en servidor desde la aceptación del pedido, con fechas auditables. Diferenciar pago realizado, comprobante recibido y pago verificado. Confirmado: reportar el pago con reserva vigente mantiene las unidades mientras se revisa, aunque venza la hora; no liberarlas por el plazo original. El reporte, el vencimiento y la verificación deben coordinarse de forma atómica. Un pago tardío no reactiva automáticamente una reserva vencida. Confirmados efectivo en sucursal y pago total con una o varias transferencias verificadas. Faltan prórrogas y detalles de resolución de incidencias. Propuesta: cola de revisión con responsable, antigüedad y alertas, sin liberación automática por alerta; limitar reservas abusivas sin bloquear compras legítimas.

### Liberación manual por incidencias

Confirmado: se permite liberar manualmente la reserva de un pedido en línea ante un comprobante rechazado u otra incidencia, incluso si estaba mantenida por pago reportado en revisión. Esto complementa el vencimiento automático de pedidos sin reporte; rechazar un comprobante no implica por sí solo una liberación automática.

Propuesta: permiso específico, motivo obligatorio y auditoría de responsable, fecha/hora, pedido, cantidades y estados previo/posterior. Liberar devuelve disponibilidad sin aumentar existencias físicas ni borrar historial. Debe ser una operación atómica e idempotente coordinada con pago y preparación. Si ya hay pago confirmado, extracción o despacho, utilizar el flujo de cancelación, devolución o corrección aplicable; liberar no equivale a reembolsar. Falta concretar roles autorizados y estados de resolución de incidencias.

### Venta directa y stock de sala

Confirmado: venta en tienda con entrega directa; reserva de una hora solo para pedidos en línea. Sala y bodega son ubicaciones separadas. Reponer sala mediante movimientos internos y registrar venta desde la ubicación real de entrega, sin traslados ficticios ni doble descarga al facturar externamente.

Confirmado: el cliente puede elegir sucursal; se conserva una configuración por defecto, propuesta como selección inicial. Web solo se atiende desde bodega de la sucursal elegida. Sala y bodega tienen saldos separados; web no reserva sala salvo traslado previo de esas unidades a bodega. Central solo abastece mediante traslados. No sumar central/tránsito a disponibilidad inmediata. Cambiar sucursal en carretilla exige revalidación; reasignar pedidos reservados o dividirlos entre sucursales requiere definición adicional.

Confirmado: aunque la venta presencial se facture en el sistema externo, su descarga de inventario se realiza en Los Cheles. Registrar una única salida desde la ubicación real de entrega, conservando trazabilidad. Propuesta: vincular el documento externo a la operación para conciliación sin repetir la descarga. No se ha definido ni implementado una integración automática con el sistema de facturación.

### Modelo físico de inventario

Mantener identidad única de producto, con saldos por bodega, ubicación, lote y condición. Presentaciones y empaque describen esas unidades; no se suman como existencias independientes.

Propuesta de saldos, definiendo categorías sin superposición:

- Físico por bodega: todo lo que realmente está allí, incluido el área de preparación.
- Reservado temporalmente: unidades de pedidos en línea aceptados dentro del plazo o con pago reportado en revisión; las unidades en revisión son parte de este saldo, no se suman de nuevo.
- Comprometido: unidades confirmadas tras verificar el pago o asignadas a traslados autorizados que aún no salieron; no contadas simultáneamente como reserva temporal.
- No vendible: unidades bloqueadas, dañadas o en revisión, no contadas simultáneamente como comprometidas.
- Disponible = físico − reservado temporalmente − comprometido − no vendible.
- En tránsito: fuera de origen y aún no recibido en destino; separado del disponible de ambos.

Abrir un fardo cambia el empaque; mover a preparación cambia ubicación y no registra otra venta. Confirmado: la salida física web se registra al completar preparación y despachar/coordinando entrega, con orden de venta. No descontar de nuevo al marcar entregado ni emitir o vincular factura externa.

La equivalencia puede variar entre productos y compras: no suponer que todo fardo tiene diez o doce unidades. Conservar la equivalencia usada al ingresar/vender para que cambios futuros no alteren documentos históricos. Si una nueva presentación cambia el contenido, crear/versionar esa presentación.

### PEPS y preparación

Ordenar lotes por recepción original, no por fecha de carga ni por último traslado. Dentro del lote antiguo elegible, priorizar abiertos. El usuario confirmó que se permite reempacar unidades del mismo producto para completar el fardo solicitado; respetar las características de la variante pedida. Si el lote antiguo no alcanza, completar desde el siguiente lote apto sin perder el desglose de procedencias. Si existen artículos con vencimiento, evaluar prioridad por caducidad sin suponer que aplica a todo el catálogo.

La trazabilidad del reempaque debe vincular pedido y hoja de pickeo con cada lote, ubicación y cantidad utilizados. Diseño propuesto: identificar el paquete resultante y registrar producto/variante, presentación/equivalencia, responsable, fecha/hora y movimientos asociados. Mantener costos y fechas originales por lote, sin exponer costos a bodega. Un reempaque no reinicia la antigüedad ni incrementa el stock físico; tampoco produce un segundo descuento por venta. Distinguir el paquete reempacado de uno sellado de fábrica.

Ejemplo: fardo de 12 unidades = 5 del lote A, ubicación A-01-02, más 7 del lote B, ubicación B-02-01, del mismo producto y características. Conservar ambas procedencias en el paquete y en la extracción del pedido. En una devolución, consultar esa composición sin atribuir un lote concreto a unidades indistinguibles sin evidencia.

La hoja de pickeo debe guiar por recorrido físico, mostrar foto/SKU/presentación/lote/ubicación/cantidad, y capturar lo realmente extraído. Registrar faltantes sin sustituir silenciosamente artículos. La cantidad del documento final debe concordar con la preparación autorizada; las diferencias respecto al pedido y pago necesitan resolución.

### Traslados, devoluciones y conteos

El traslado necesita despacho y recepción diferenciados. Una hoja impresa no sincroniza existencias: son las confirmaciones registradas las que cambian saldos. Registrar parciales, daños, responsables y tiempos de tránsito; cerrar solo con diferencias resueltas o pendientes formalmente controlados.

Confirmado: encargado/supervisor recibe devoluciones e ingresa productos, incluidos dañados, que luego se descargan por deterioro. Propuesta: ingreso bloqueado/en revisión, habilitando solo lo apto; daños nunca pasan a disponible antes de su baja. Conservar referencias y lote identificable. Devolución física y reembolso son operaciones relacionadas pero distintas.

Para conteos, proponer conteos cíclicos por ubicación y riesgo, captura sin mostrar el saldo esperado cuando convenga, recuento de diferencias y autorización del ajuste. Definir cómo se controla el movimiento durante el conteo: congelación por zona o conciliación desde un corte. No sobrescribir el stock para hacerlo coincidir sin investigar la causa.

### Cargas masivas

Plantilla versionada con producto, presentación, equivalencia, cantidad, bodega, ubicación, recepción, origen y costos restringidos. Flujo: cargar → validar → previsualizar → confirmar → movimientos → resultado.

Definir importación atómica o aceptación parcial explícita; nunca informar éxito total si algunas filas fallaron. Detectar duplicados, conservar archivo y responsable con acceso restringido, y revertir por compensación. Revisar codificación, separadores decimales, unidades, fórmulas peligrosas en exportaciones y límites de tamaño. Separar carga inicial, recepción y conteo físico.

## 6. Perspectiva financiera

Este apartado propone control operativo; el reconocimiento contable, impuestos y valoración oficial deben acordarse con el responsable contable y el sistema externo.

### Costos, precios y margen

Guardar costo por lote y precio comercial por presentación/escala. No actualizar el costo del stock antiguo con la última factura del proveedor. Una compra más barata puede mejorar el margen manteniendo el precio; no es una regla de subir precios automáticamente.

Ejemplo ilustrativo, sin impuestos ni costos adicionales:

| Concepto | Lote A | Lote B |
|---|---:|---:|
| Costo por fardo de 12 | $30 | $25 |
| Costo unitario | $2.50 | $2.083333… |
| Precio supuesto por unidad | $3 | $3 |
| Diferencia por unidad | $0.50 | $0.916667… |
| Margen sobre venta | 16.67% | 30.56% |

Margen = (precio − costo) / precio. Recargo sobre costo = (precio − costo) / costo. No son intercambiables: con costo 2.50 y precio 3, el margen es 16.67% y el recargo 20%.

No usar coma flotante binaria para importes definitivos. Usar decimales o enteros escalados, precisión suficiente para conversiones y redondeo consistente. Distribuir residuos de redondeo de modo que el costo de todas las unidades concilie con el total del lote.

El margen operativo por extracción puede seguir los lotes reales. Eso no define automáticamente la valoración contable: IAS 2 contempla, para inventarios ordinariamente intercambiables, fórmulas FIFO o promedio ponderado, entre otras distinciones del estándar. El marco aplicable a esta empresa debe determinarse aparte. [IFRS Foundation — IAS 2](https://www.ifrs.org/issued-standards/list-of-standards/ias-2-inventories/).

### Conciliación y control

Separar pedido, cobro y factura. Sin emitir facturas, la plataforma sí debería poder enlazar la referencia del documento externo, responsable, fecha y diferencias; es una propuesta necesaria para cerrar el circuito, no una integración ya construida.

Para transferencias: referencia, monto, fecha, evidencia, verificador y distribución a pedidos. Para efectivo: receptor, momento y cierre/conciliación con el sistema que lleve caja. No ampliar por defecto a un módulo contable completo.

Evitar reutilizar un mismo comprobante sin explicación; no autorizar preparación con pago incompleto; sí admitir varias transferencias verificadas que cubran el total, según lo confirmado. Separar solicitud y autorización de reembolso/ajuste. En equipos pequeños, donde no haya dos personas disponibles, registrar la excepción y facilitar revisión posterior.

El inventario parado consume capital. Gerencia debería consultar costo de existencias por antigüedad, productos de baja rotación, pérdidas/daños, margen realizado y pedidos pagados pendientes de surtir. Venta solicitada no es ingreso cobrado; cobro no debe asumirse como ingreso contable reconocido.

No proyectar ROI numérico sin datos. Medir volumen, margen, horas de preparación, errores, pérdidas y gasto publicitario. Comparar beneficios incrementales y ahorros comprobables con desarrollo, hosting, soporte, capacitación, dispositivos y mantenimiento, sin contar dos veces la misma mejora.

## 7. Perspectiva de TI y arquitectura

### Arquitectura propuesta: monolito modular

Una aplicación CodeIgniter con módulos claros y MySQL transaccional es una base razonable para el alcance conocido. Permite reutilizar código y reduce la carga de despliegue; exige disciplina para no concentrar reglas en controladores y vistas. No justifica microservicios sin evidencia de escala/equipos que los necesiten.

Módulos propuestos: catálogo/presentaciones; precios; identidad/permisos; pedidos; pagos; inventario/lotes; bodegas/ubicaciones; preparación; traslados; devoluciones/conteos; documentos; importaciones; analítica. Son fronteras lógicas, no aplicaciones independientes.

Controladores reciben solicitudes; servicios ejecutan casos de uso y autorizaciones; modelos/repositorios persisten; vistas presentan datos autorizados. Evitar consultas de negocio directamente en vistas. Mantener APIs públicas limitadas y endpoints privados con contratos explícitos.

### Integridad y concurrencia

Saldo y movimientos deben actualizarse en la misma transacción. Bloquear o actualizar condicionalmente las existencias correspondientes, comprobar cantidades y evitar negativos. Usar claves de idempotencia para que doble clic/reintento de pago, recepción o extracción no duplique operaciones. Definir qué pasa al fallar a mitad de un proceso.

Conservar un libro de movimientos inmutable a nivel de aplicación, con compensaciones y referencia documental, más saldos para consultas rápidas y conciliación. No es necesario reconstruir toda la aplicación como event sourcing. Traslados conservan cantidades entre origen, tránsito y destino salvo ajustes autorizados.

Notificaciones y tareas de vencimiento no deben dejar una venta inconsistente si fallan. Ejecutarlas después del compromiso transaccional con reintentos controlados; evaluar una bandeja transaccional de eventos cuando se implementen. El servidor determina fechas límite; un temporizador del navegador solo las presenta.

Tiempo real significa que las decisiones leen datos autorizados del servidor y que las pantallas se actualizan con una latencia acordada. Polling o eventos son alternativas; no se ha elegido una. Ninguna animación garantiza exactitud.

### Modelo de datos mínimo por diseñar

| Área | Entidades y relaciones propuestas |
|---|---|
| Producto | Producto, presentación y equivalencia versionada, imágenes, categorías |
| Existencias | Lote de ingreso, saldo por ubicación/condición, movimiento, reserva |
| Bodegas | Bodega, ubicación interna, traslado y líneas/recepciones |
| Comercial | Cliente separado del usuario que registra, pedido y líneas con precio fijado |
| Cumplimiento | Hoja de pickeo, asignaciones y extracciones por lote |
| Financiero operativo | Pago, asignación de pago, verificaciones, reembolsos autorizados |
| Documentos | Orden final/versiones y referencia de factura externa |
| Gobierno | Roles, permisos, alcance por bodega, auditoría, cargas y filas de importación |

No hace falta crear todas las tablas inmediatamente. Sí conviene definir sus relaciones antes de construir pantallas que dependan de ellas. La tabla clientes existente en un modelo no tiene migración y los pedidos actuales usan users: resolver esta diferencia expresamente.

### Seguridad y operación

Permisos por acción, campo y alcance. Bodega no recibe precios en JSON ni en documentos; vendedor no recibe costos; administradores técnicos no necesitan automáticamente autorización funcional de costos. Quien administra la infraestructura puede tener acceso privilegiado: esto también requiere controles organizativos, secretos y auditoría.

OWASP recomienda mínimo privilegio, denegación por defecto y validar permisos en cada solicitud. Aplicarlo también a exportaciones, archivos originales de importación, enlaces de comprobantes y cuentas de invitados. [OWASP Authorization Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html).

Antes de producción: corregir rutas expuestas, CSRF y acceso por propietario; endurecer cargas, sesiones y recuperación; considerar MFA para cuentas privilegiadas; separar desarrollo/pruebas/producción; secretos fuera de Git; usuario de base dedicado con permisos limitados. Root sin contraseña es configuración local, no propuesta de producción.

Configurar registros de errores y auditoría sin información sensible innecesaria, monitoreo de trabajos fallidos, alertas de diferencias y copias de base/documentos/imágenes. Probar restauración. Acordar RPO/RTO antes de prometer disponibilidad; el respaldo local previo del código no es un respaldo operativo de producción.

Git debe tener cambios revisados, versiones etiquetadas, pruebas automatizadas y migraciones reproducibles. El repositorio remoto indicado aún no está verificado ni se ha publicado en él durante este trabajo. Evitar respaldos bajo rutas web accesibles; desplegar con public como raíz documental.

### PWA

Diseñar móvil/tableta desde el inicio. Instalación y caché llegan sobre procesos estables. Proponer sin conexión solo contenido público previamente consultado y borradores; confirmar pagos, inventario y traslados requiere servidor. Mostrar desconexión y actualización de datos; no guardar información privada indiscriminadamente en equipos compartidos. Las capacidades varían por dispositivo. [Guía PWA](https://web.dev/learn/pwa/installation/).

## 8. UX/UI: convertir complejidad en tareas claras

### Dirección visual propuesta

Minimalismo funcional: jerarquía clara, espacios bien usados, tipografía legible, fotografía consistente, paleta breve y componentes coherentes. No ocultar información crítica para conseguir una pantalla vacía. No adoptar animaciones pesadas, menús solo con iconos o gráficos decorativos como sustitutos de utilidad.

Conservar identidad de Los Cheles, pero definir un sistema de diseño antes de extender pantallas: colores, tipografía, espaciado, tablas, formularios, botones, alertas, estados y documentos imprimibles. La paleta y composición definitiva requieren prototipos; no están aprobadas todavía.

### Experiencia por rol

| Rol | Pregunta que debe resolver su pantalla inicial | Diseño propuesto |
|---|---|---|
| Cliente | ¿Qué necesito y cuánto pagaré? | Búsqueda, fotografías, presentaciones claras y total comprensible |
| Vendedor | ¿Cómo registro rápido un pedido correcto? | Búsqueda por SKU, cantidades por teclado, cliente y resumen visible |
| Bodega | ¿Qué saco, de dónde y cuánto? | Lista por recorrido, foto, ubicación destacada, cantidad y excepción |
| Verificador de pago | ¿Qué pago puedo confirmar? | Pedido, monto, referencia, evidencia y discrepancias juntas |
| Supervisor | ¿Qué está detenido y por qué? | Bandeja por prioridad, tiempos y responsable |
| Gerencia | ¿Dónde estamos perdiendo dinero o servicio? | Indicadores con definición y acceso al detalle autorizado |

Usar vistas de trabajo distintas sobre el mismo sistema, no imponer a bodega una tabla comercial con columnas ocultas informalmente. En móvil, presentar tarjetas/listas cuando una tabla ancha no sea operable; en escritorio, permitir densidad y filtros para trabajo repetitivo.

### Interacción y accesibilidad

Mostrar acción principal, siguiente paso y resultado confirmado. Estados vacíos, errores, cargando, falta de permisos y desconexión forman parte del diseño. Conservar datos al fallar validación; evitar formularios repetidos y confirmaciones innecesarias. Reservar confirmaciones para movimientos sensibles; usar acciones compensatorias donde proceda.

Proponer WCAG 2.2 nivel AA como objetivo a validar: contraste, teclado, foco visible, etiquetas y mensajes accesibles. Los estados no se comunican solo por color. Para bodega, botones táctiles amplios y separados; 44–48 px puede ser una decisión de diseño cómoda, no una afirmación de mínimo AA universal. [WCAG 2.2](https://www.w3.org/TR/WCAG22/).

### Validación de UX

Prototipar antes de desarrollar: compra unitaria y por fardo, pedido del vendedor, verificación de pago, pickeo con faltante, traslado parcial y conteo con diferencia. Observar usuarios reales de esos puestos. Medir éxito sin ayuda, errores y tiempo por tarea; no validar solo si gusta el color.

No prometer ahorro de tiempo sin medir la línea base. Como referencia técnica de rendimiento, buscar LCP ≤ 2.5 s, INP ≤ 200 ms y CLS ≤ 0.1 en el percentil 75, separado por móvil/escritorio. Son objetivos, no resultados alcanzados. [Web Vitals](https://web.dev/articles/vitals).

## 9. Indicadores de gestión propuestos

| Perspectiva | Indicador | Precaución |
|---|---|---|
| Mercadeo | Pedidos pagados / pedidos enviados | Cohortes y vencimientos comparables |
| Mercadeo | Recompra y ticket pagado | Separar nuevos/recurrentes y devoluciones |
| Operación | Tiempo pago verificado → listo para facturar | Mostrar mediana y casos lentos, no solo promedio |
| Operación | Líneas preparadas sin corrección / líneas preparadas | Definir cuándo se considera error |
| Inventario | Diferencias de conteo y valor de diferencias | Evitar que sobrantes y faltantes se cancelen y oculten problemas |
| Logística | Traslados con diferencias y tiempo en tránsito | No cerrar parciales como completos |
| Finanzas | Margen por producto/pedido/canal | Costos reales, descuentos y devoluciones coherentes |
| Finanzas | Costo de inventario por antigüedad | Acceso restringido y fecha original |
| TI | Fallos, latencia, trabajos pendientes y restauración | Medición real; no indicadores simulados |
| UX | Tareas completadas, errores y tiempo | Probar en dispositivos y condiciones reales |

Cada métrica necesita definición, origen, frecuencia, responsable y permiso. Los paneles actuales con ceros provisionales no deben utilizarse para decisiones gerenciales.

## 10. Riesgos prioritarios

| Riesgo | Consecuencia | Tratamiento propuesto |
|---|---|---|
| Reserva concurrente o vencimiento mal coordinado | Doble asignación o liberación de stock ya confirmado | Reserva atómica al aceptar pedido, vencimiento coordinado con pago e idempotencia; política de pagos tardíos |
| Ventas de tienda fuera del sistema | Inventario web ficticio | Definir captura de todas las salidas y conciliación externa |
| Confusión unidad/fardo/caja | Precio y extracción incorrectos | Equivalencias versionadas, unidades base y empaque real |
| Doble descuento al preparar/despachar | Stock menor al físico | Separar reserva, movimiento interno y salida |
| Traslado sin recepción | Mercancía perdida en registros | Tránsito y cierre con diferencias controladas |
| Costos filtrados en exportaciones | Exposición comercial | Autorización de campos, archivos y documentos |
| Importación duplicada | Stock y costos inflados | Identificación de carga, validación e idempotencia |
| Campañas antes de capacidad operativa | Más reclamos y devoluciones | Piloto controlado y límites de demanda |
| PWA con datos viejos | Confirmaciones falsas o exposición local | Operaciones críticas en línea y caché selectiva |
| Demasiadas funciones simultáneas | Retraso y complejidad | Entregas completas por flujo, no pantallas aisladas |

## 11. Hoja de ruta por resultados

La secuencia siguiente es preliminar. Para ejecutar el alcance actualizado usar [Ruta de incrementos del 28 de agosto](ruta-de-incrementos.md): coloca abastecimiento y operación interna antes de pedidos web, define criterios de aceptación y mantiene el parking lot fuera del lanzamiento.

### Etapa 0 — Definiciones y prototipos

Completar importes y escalas de precios y su relación con fardos, respetando el umbral actualizado de tres unidades sueltas del mismo producto/variante y la escala separada de fardos, sin mezclar productos. Cerrar pago/reserva, efectivo, tienda, abastecimiento, incidencias y documentos. Definir matriz de roles, datos maestros y modelos de estados. Prototipar compra, preparación y traslado. Salida: reglas y escenarios de aceptación aprobados, no solo bocetos.

### Etapa 1 — Base segura e inventario controlado

Resolver bloqueos técnicos y dependencias; crear identidad/permisos; productos/presentaciones, bodegas, ubicaciones, lotes, movimientos e importación inicial auditada. Salida: un ingreso puede explicarse y conciliarse por lote/ubicación sin duplicados ni exposición de costos.

### Etapa 2 — Un ciclo comercial completo

Catálogo y carretilla corregidos, cliente/vendedor, pago manual, asignación de stock, pickeo, documento final y despacho. Incluir desde aquí las cancelaciones y correcciones mínimas para operar, no posponer toda excepción. Salida: pedido de principio a fin con precios fijados, permisos y cantidades conciliados.

### Etapa 3 — Operación multibodega

Traslados, tránsito, recepción parcial, devoluciones, conteos, reportes y conciliación externa. Si central y tienda operan desde el primer día, esta etapa es parte obligatoria del alcance de lanzamiento, no una mejora opcional posterior.

### Etapa 4 — Piloto y PWA

Piloto acotado con datos controlados, dispositivos reales, respaldo/restauración y capacitación. Verificar permisos, errores, concurrencia y tiempos. Habilitar instalación y capacidades PWA aprobadas. Salida: lista de aceptación operativa y técnica cumplida.

### Etapa 5 — Crecimiento comercial

Medición validada, páginas de campaña, recomendaciones, recompra y después puntos. Aumentar captación según capacidad de preparación y exactitud de inventario. No atribuir resultados financieros a una funcionalidad antes de medirlos.

No se asignan fechas ni presupuesto sin volumen, equipo y prioridades. La secuencia reduce retrabajo; se pueden solapar diseño y preparación técnica cuando sus decisiones no estén bloqueadas.

## 12. Condiciones mínimas para lanzar

- Dos pedidos simultáneos por la última unidad no generan doble reserva; una carretilla no reserva. Verificar el pago confirma sin descontar dos veces; vencer libera solo si no hay reporte vigente en revisión. Un reporte a tiempo mantiene la reserva más allá de la hora. La venta presencial respeta reservas de otros pedidos si comparte existencias. Existe una política para pagos tardíos y reportes rechazados.
- Reintentar una operación no duplica pago, entrada, extracción o traslado.
- Un pedido puede completarse y un faltante puede resolverse sin editar directamente la base.
- Venta por unidad y por fardo concilia con el empaque y la existencia física. Un fardo reempacado conserva cantidades, lotes y ubicaciones de origen, sin duplicar saldos ni reiniciar antigüedad.
- Un traslado parcial conserva y explica la cantidad pendiente.
- Bodega no recibe importes en pantalla, API, impresión ni exportación; vendedor no recibe costos.
- Se puede reconstruir producto → lote → ubicación → extracción → pedido → documento final.
- Pedido, pago y facturación externa tienen referencias conciliables.
- La base inicial se valida mediante conteo; todas las salidas reales de tienda tienen mecanismo de registro.
- Se restaura un respaldo en un entorno separado y se verifica su integridad.
- Los enlaces de producto e imagen son públicos en HTTPS y se prueban en WhatsApp/Facebook sin publicar campañas automáticamente.
- El equipo completa las tareas críticas en los dispositivos previstos; las metas UX se aprueban con evidencia.

## 13. Recomendación final

Conservar la base tecnológica y transformar el núcleo con límites claros. Diseñar juntos el recorrido comercial y el trabajo físico, con un modelo financiero operativo consistente. La estética debe expresar esa claridad mediante interfaces modernas y minimalistas, pero los criterios de éxito son compra comprensible, preparación ágil, stock conciliado y margen explicable.

La siguiente entrega recomendada es una especificación funcional con estados, reglas de precio, matriz de permisos, modelo de datos y prototipos de los recorridos críticos. No avanzar a una construcción masiva antes de resolver los conflictos operativos señalados.

## Actualización de alcance: carga, conteo y documentos

Confirmados: adaptar la carga inicial a los datos del nuevo modelo; conteos físicos periódicos por bodega/sala; códigos de cajas y contenido de unidades/fardos/paquetes; hora de salida y conciliación del traslado con recepción real; factura externa relacionada con orden de venta. El importador actual usa CSV con stock global y actualización absoluta; no implementa aún el modelo propuesto.

La propuesta detallada de formato, identificación de cargas, cajas, conteos y manejo de diferencias se encuentra en docs/decisiones-operativas-confirmadas.md, secciones 11 a 14. No se ejecutó una carga ni se modificaron existencias. Las diferencias deben investigarse y resolverse sin forzar cantidades; la factura vinculada no genera otra salida.

El usuario dejó en parking lot fidelización, recomendaciones, PWA sin conexión y detalles visuales finales. Las propuestas previas sobre esos temas se conservan como futuras, no como trabajo inmediato. UX/UI de los recorridos operativos sigue activo. La frase ambigua «descarga de inventario por defecto» no genera un requisito adicional; las bajas por daños están confirmadas por separado.

## Evidencia local

- `docs/contexto-del-negocio.md`: requisitos y decisiones de la conversación.
- `docs/actualizacion-codeigniter-4.7.4.md`: actualización y comprobaciones previas.
- `docs/metadatos-seo-y-redes.md`: alcance real de SEO/social y limitaciones de publicación.
- `app/Models/PedidoModel.php`, `app/Controllers/CarretillaController.php`, `app/Services/InventarioService.php`, `app/Config/Routes.php`, `app/Config/Filters.php`: bloqueos y limitaciones contrastados.
- PHPUnit y `spark db:table --show`: ejecutados para este informe, sin cargar datos comerciales.
