# Contexto del negocio: Distribuidora Los Cheles

> Actualización vigente del 28 de agosto: consultar [Decisiones del 28 de agosto](decisiones-2026-08-28.md). Sustituye umbrales, propuestas de invitados y permisos anteriores incompatibles; no acredita implementación.

## Estado de este documento

Información proporcionada por el usuario el 27 de agosto de 2026. El levantamiento está en curso. Consultar también docs/decisiones-operativas-confirmadas.md para las últimas aclaraciones sobre sucursal por defecto, sala, entregas, fardos, pagos completos, devoluciones y documentos. Distinguir reglas confirmadas de propuestas; no se han implementado por documentarlas.

## Información recibida

- El sistema es para una distribuidora de artículos.
- La distribuidora vende al por mayor y al detalle.
- La venta individual se considera venta al detalle.
- Regla de mayoreo confirmada: **desde 3 unidades sueltas del mismo producto y con las mismas características en una sola compra**, según actualización del 28 de agosto; fardos tienen precio normal y mayorista propios, con mínimo confirmado de 3 fardos iguales. No se pueden sumar productos diferentes ni variantes con características distintas para alcanzar el umbral.
- Sitio donde funcionará el sistema: https://distribuidoraloscheles.com/
- Posible repositorio histórico indicado por el usuario: https://github.com/joseluisroot/distribuidora-los-cheles.git
- El usuario desea continuar explicando el proyecto antes de decidir qué se implementará.

Los enlaces se registran como referencias proporcionadas por el usuario. No se ha verificado en esta etapa el contenido del sitio, el repositorio remoto ni su correspondencia con el proyecto local. No constituyen autorización para publicar o hacer push.

## Análisis preliminar

- La regla inicial de más de 3 fue sustituida el 28 de agosto: unidades sueltas desde 3 inclusive. Para fardos, definir mínimo de su propia escala; no convertir equivalencias automáticamente en descuentos.
- El usuario confirmó que no se mezclan productos: la elegibilidad se calcula por producto y conjunto de características, no por el total del pedido. Ejemplo: 2 unidades de A y 2 de B no califican; 4 de A sí califican para el precio mayorista de A. Variantes distintas tampoco se acumulan.
- La implementación actual selecciona escalas por la cantidad de cada producto/línea, no por la cantidad total de artículos del pedido.
- El catálogo actual muestra referencias de precio para cantidades 1, 3 y 10. Esto no confirma que esos sean los umbrales comerciales definitivos.
- Al implementar la regla, revisar catálogo, carretilla, cálculo de precios y pedido para aplicar el umbral confirmado de forma consistente. La identificación técnica de las características/variantes todavía debe diseñarse. Esta confirmación queda documentada; no implica que el código actual ya la aplique.

## Aspectos pendientes de precisar durante la conversación

- Cómo se define el importe del precio mayorista para cada producto; solo los productos que cumplen individualmente el umbral reciben esa mejora de precio.
- Si existen más escalas, excepciones o categorías con reglas diferentes.
- Detalles pendientes de confirmación, inventario, pagos, entrega y cancelaciones, según el flujo descrito abajo.

Estas preguntas son notas de análisis; no representan requisitos acordados ni bloquean que el usuario siga describiendo el negocio.

## Flujo operativo descrito por el usuario

### Acceso y clientes

- Cualquier visitante puede consultar el catálogo y armar una lista de intereses sin iniciar sesión.
- Aprobado el 28 de agosto: compra como invitado con datos y contacto verificable, sin cuenta obligatoria; login y cuenta opcionales. Mecanismo de verificación pendiente.
- Con cuenta, el cliente podrá conservar sus pedidos e historial en su perfil.
- Se plantea acumulación de puntos para retención de clientes; sus reglas aún no están definidas.
- Los pedidos podrán ser ingresados por el propio cliente o por un colaborador/vendedor.

### Panel de trabajo y seguimiento

- Los pedidos llegan a un panel de trabajo para su seguimiento operativo.
- El cliente debe poder consultar el avance de su pedido.
- Estados del cliente: recibido, pendiente de pago, preparando, despachado y entregado, con confirmación de entrega. Mantener además estado de pago en revisión y estados excepcionales; faltan permisos detallados y evidencia de entrega.
- El sistema emitirá un comprobante de pedido para el cliente y también lo generará desde administración. No confundir este comprobante con una factura fiscal o una constancia de pago.

### Pago e inventario disponible

- No se integrará una pasarela de pagos en el alcance descrito.
- Los pagos se coordinarán mediante transferencia electrónica o efectivo.
- Para pedidos en línea se define un plazo de **una hora para realizar/reportar el pago**, con reserva temporal al enviar y aceptar el pedido. Si no se reporta el pago dentro del plazo, se libera la reserva. El usuario confirmó que reportarlo mientras la reserva está vigente la mantiene durante la revisión, aunque pase la hora; no equivale a verificarlo ni autoriza preparación. Un reporte tardío no recupera automáticamente una reserva ya vencida.
- **La carretilla no reserva; el pedido enviado y aceptado sí reserva inventario.** El usuario aclaró que «libre» se refería al producto en carretilla, no al pedido pendiente de pago. Esta aclaración reemplaza la interpretación anterior.
- Al enviar el pedido se debe validar la disponibilidad y crear la reserva de forma atómica. Una carretilla anterior no da prioridad: si otro cliente ya reservó el producto, se informa la falta de disponibilidad y no se confirma una reserva inexistente.
- Al verificar el pago, las unidades reservadas pasan a estar confirmadas/asignadas para preparar el pedido; no se descuentan de nuevo del disponible. Si vence sin pago reportado, las unidades vuelven a estar disponibles, sin aumentar el stock físico. Con pago reportado, conservar la reserva en revisión hasta resolver la verificación. Se permite liberación manual por rechazo o incidencias; faltan los detalles de resolución y cancelación; no se acepta pago incompleto para continuar; no liberar por el vencimiento original mientras está en revisión.
- El inventario debe reflejar los movimientos en tiempo real.
- Confirmado: la salida física del pedido web ocurre al completar la preparación y despachar, coordinando la entrega con la orden de venta. Reservar, extraer a preparación, emitir prefactura y confirmar entrega no deben generar descuentos físicos adicionales.

### Liberación manual de reservas

- Confirmado: se permite liberar manualmente la reserva de un pedido en línea cuando se rechaza el comprobante o existe otra incidencia con el pedido. Esto también permite resolver reservas mantenidas por pago reportado en revisión; no elimina el vencimiento automático de pedidos sin reporte.
- Propuesta de control: permiso específico para liberar reservas, motivo obligatorio y registro de responsable, fecha/hora, pedido, cantidades y estado previo/posterior. La asignación de este permiso a roles concretos sigue pendiente.
- Liberar una reserva devuelve disponibilidad, no incrementa existencias físicas ni elimina el historial del pedido o del pago. Rechazar el comprobante y liberar son acciones distinguibles; no deducir una liberación automática del rechazo.
- Propuesta de seguridad: ejecutar la liberación de forma atómica e idempotente y coordinarla con la verificación del pago y la preparación. Si ya existe pago verificado, mercancía extraída o despacho, exigir el flujo de cancelación, devolución o corrección correspondiente; no tratar esos casos como una reserva pendiente ni asumir que liberar equivale a reembolsar.
- Confirmado: pago completo antes de continuar, que puede cubrirse con varias transferencias verificadas; efectivo en sucursal. Pedido vencido requiere uno nuevo, con opción de copiar y validar existencias. Pendiente conciliar dinero recibido después del vencimiento y definir estados de resolución de incidencias.

### Venta presencial y sala de ventas

- Confirmado: la venta en tienda es un proceso de entrega directa. La reserva de una hora y su extensión por pago reportado corresponden exclusivamente a pedidos en línea; no trasladar automáticamente ese flujo a la venta presencial.
- Confirmado: sala y bodega de sucursal se manejan como ubicaciones separadas. Web no reserva de sala; para utilizar esas unidades se requiere traslado registrado y físico a bodega antes de reservar/extraer. Un solo inventario con saldos separados, sin duplicar unidades.
- Registrar reposiciones desde la bodega de sucursal a sala mediante movimientos internos. El stock de central no equivale a disponibilidad inmediata en sala; un traslado entre bodegas sigue su proceso de envío/recepción.
- Confirmado: la facturación de la venta presencial se realiza en el sistema externo, pero la descarga de inventario debe registrarse en la plataforma Los Cheles. Propuesta: validar disponibilidad y registrar una única salida desde la ubicación real de entrega, con responsable y referencia al documento externo. Vincular una factura no debe descontar nuevamente las unidades. No se presupone integración automática con el sistema externo.
- Si se entrega desde bodega de sucursal y no desde sala, registrar esa ubicación real. No exigir un traslado ficticio a sala cuando el producto no pasa por allí.
- Confirmado: el cliente puede elegir sucursal. Se conserva la configuración por defecto; propuesta: usarla como selección inicial modificable antes de enviar. Web reserva solo desde bodega de la sucursal elegida, nunca sala ni central. Cambiar la sucursal de la carretilla requiere revalidar disponibilidad y entrega; una reserva existente no se reasigna silenciosamente.
- Propuesta: reportes separados por central, bodega de sucursal y sala, mostrando físico, reservado/en revisión, comprometido y disponible, con consolidación sin doble conteo. Las reservas en revisión son parte del reservado, no una cantidad adicional.

### Almacenamiento y preparación

- Los artículos deben tener una ubicación para identificar dónde están almacenados.
- Administración debe generar una hoja de extracción o **hoja de pickeo** por pedido.
- La hoja debe mostrar, como mínimo, artículo, ubicación y cantidad que se debe extraer.
- Después de finalizar la hoja de extracción se generará una **orden de compra**, que servirá como documento para que el pedido sea facturado fuera de esta plataforma.
- El proceso debe dar trazabilidad de los movimientos y permitir conciliar inventario físico con inventario del sistema.
- Confirmado: encargado/supervisor de tienda recibe devoluciones. Se registra ingreso, también de dañados, seguido de baja por daños/deterioro. Propuesta: ingresar en condición bloqueada/en revisión y habilitar solo lo apto; conservar trazabilidad al pedido y lote identificable.
- El usuario no recuerda el contexto de «descarga de inventario por defecto». Conservar como antecedente ambiguo, sin función adicional ni bloqueo. Las bajas por daños/deterioro ya están confirmadas mediante otra aclaración.

### Analítica y recomendaciones

- Identificar artículos más vistos y más comprados.
- Identificar productos que suelen comprarse juntos.
- Mostrar artículos relacionados con el producto consultado o incluido en la lista; ejemplo proporcionado: cuchara, tenedor y cuchillo.
- Considerar sugerencias basadas en compras de otros clientes.
- Falta definir métricas, períodos, tratamiento de visitas repetidas y criterios para contar compras válidas.

### Roles, permisos y protección de información sensible

Requisitos confirmados por el usuario:

- Los accesos deben ser administrables por roles y permisos de cada sección del sistema.
- Los colaboradores comerciales no deben ver costos de compra ni datos sensibles de costos; solo precios finales de venta.
- El personal de bodega no debe ver precios de compra ni de venta. Debe acceder únicamente a la información operativa necesaria del pedido y su extracción.
- Estas restricciones deben mantenerse en las distintas pantallas, documentos y salidas del sistema.

Propuesta de diseño pendiente de detalle/aprobación:

- Permisos por módulo, acción y sensibilidad de datos; por ejemplo consultar pedidos, registrar pedidos, verificar pagos, extraer, trasladar inventario, importar existencias, consultar costos, consultar márgenes, modificar precios y administrar permisos.
- Definir alcance de registros además del permiso de acceso: pedidos propios, asignados o todos, según el rol y las decisiones que faltan.
- Matriz inicial propuesta: administrador autorizado ve costos y márgenes y administra permisos; vendedor registra/consulta pedidos permitidos y ve precios finales; bodega ve productos, SKU, presentación, cantidades, lotes, antigüedad y ubicaciones sin importes. Los roles definitivos y facultades de supervisión/caja siguen pendientes.
- Separar autorización para ver precios de autorización para modificarlos; ver un precio final no debe permitir cambiarlo, aplicar descuentos o consultar costos.
- Comprobar autorización en servidor para cada operación y registro. No basta con ocultar botones: excluir campos restringidos de respuestas, APIs, HTML, reportes, exportaciones y documentos.
- Hoja de pickeo para bodega sin precios ni totales monetarios. El documento final para facturación requiere permisos comerciales distintos, aunque ambos estén vinculados.
- La fecha/origen operativos del lote pueden ser útiles a bodega, pero no deben exponer los costos mediante archivos de compra o importaciones originales. Restringir esos documentos y generar vistas operativas sin datos sensibles.
- Aplicar mínimo privilegio y denegar por defecto; impedir que usuarios se asignen permisos superiores. Auditar cambios de roles/permisos y operaciones sensibles con responsable y fecha.
- Separar cargas operativas sin costos de las cargas con costos, o restringir las últimas al personal autorizado; no exponer costos en previsualizaciones, errores o descargas a usuarios sin permiso.
- El sistema actual tiene filtros básicos de rol, no una administración granular de permisos. Este requisito requiere ampliar autorización y adaptar pantallas/documentos en la implementación futura.

### Cargas masivas, antigüedad y costos de ingreso

Requisitos indicados por el usuario:

- Estandarizar las cargas masivas de inventario e identificar su origen y fecha.
- Priorizar la extracción del inventario antiguo frente al nuevo: rotación física PEPS/FIFO.
- Distinguir costos de compras sucesivas del mismo producto. Ejemplo: un fardo de 12 unidades comprado inicialmente a USD 30 y otro después a USD 25.
- Aprovechar reducciones de costo para mejorar ganancias. La expresión «ajustar al precio de venta mayor» queda pendiente de precisar; no establece una fórmula automática ni obliga a usar el máximo histórico.

Propuesta técnica pendiente de aprobación:

- Separar el evento de importación (archivo, origen, responsable, fecha de carga y resultado) de los lotes de ingreso. Un archivo puede contener varios lotes.
- Registrar por lote: producto, origen/proveedor o documento cuando aplique, fecha real de recepción, fecha de registro, cantidades, equivalencia de presentación usada al ingreso, costo de compra y saldo por ubicación/estado de empaque.
- La fecha de carga no reemplaza la recepción: importar hoy mercancía antigua no debe rejuvenecerla. Para inventario inicial sin fecha conocida, acordar una política explícita.
- La importación de entradas sumaría mediante movimientos auditables; no sobrescribiría saldos. Separar inventario inicial, entradas nuevas y ajustes por conteo.
- Previsualizar y validar filas, equivalencias, ubicaciones y costos; mostrar resumen antes de confirmar. Identificar cargas/documentos para impedir duplicados y conservar resultados por fila. Revertir con movimientos compensatorios autorizados, sin borrar historial.
- Conservar lote y antigüedad al trasladar o abrir fardos. Cada extracción debe indicar lote, ubicación, cantidad y pedido, enlazados después al documento final.
- Aplicar PEPS entre lotes disponibles y aptos. Priorizar fardos abiertos dentro del lote elegible más antiguo, no consumir un lote nuevo antes de uno antiguo solo por estar abierto. El usuario confirmó que se pueden reempacar unidades del mismo producto para completar un fardo solicitado, conservando trazabilidad de su procedencia.

### Reempaque para completar pedidos: regla confirmada

- Si hay unidades suficientes del mismo producto, se permite completar la presentación solicitada mediante reempaque, incluso tomando unidades de distintos lotes o ubicaciones. Respetar las características de la variante pedida; no sustituir por productos o variantes diferentes.
- Mantener la prioridad PEPS: consumir primero las unidades aptas del lote más antiguo y completar con el siguiente cuando sea necesario.
- Registrar de dónde se tomó cada cantidad y vincularla con la preparación y el pedido. Reempacar no borra los lotes de origen ni convierte unidades antiguas en inventario nuevo.
- Diseño propuesto: un registro de reempaque con identificador, producto/variante, presentación y equivalencia, cantidad resultante, responsable, fecha/hora, pedido y hoja de pickeo; detalle por lote de origen, ubicación, cantidad extraída y movimiento asociado. Si se identifican cajas o fardos físicamente, conservar también su identificador de origen.
- Mantener las fechas originales de ingreso y costos de cada lote. Los costos quedan restringidos a los roles autorizados; bodega consulta únicamente procedencias, cantidades e instrucciones operativas.
- El reempaque cambia la composición del paquete, no la existencia física total ni genera un segundo descuento por venta. Si se mueve a preparación, registrar ese traslado conservando el desglose por lote.
- Ejemplo: un fardo de 12 unidades puede componerse de 5 del lote A en A-01-02 y 7 del lote B en B-02-01, todos del mismo producto y características. El paquete contiene 12 unidades y conserva ambos orígenes; no se registra como un nuevo ingreso de 12 unidades.
- Distinguir internamente un fardo reempacado de uno sellado de fábrica. Para una devolución, conservar la vinculación con la composición original y no adjudicar un lote específico sin evidencia cuando no se pueda identificar la unidad devuelta.
- Separar costo histórico por lote de precio comercial vigente por producto/presentación/escala. Una compra más barata no cambiaría automáticamente el precio de venta ni reescribiría costos anteriores.
- Conservar historial y aprobación de cambios de precio. El momento de fijación del precio del pedido sigue pendiente.
- Calcular margen bruto operativo con el costo de las unidades de los lotes realmente extraídos. No confundir con utilidad neta ni asumir un método de valoración contable/fiscal externo.
- Mantener precisión suficiente para costos fraccionarios (25 / 12); no redondear prematuramente a dos decimales. Definir redondeo consistente para importes finales.
- Precisar si transporte u otros costos de adquisición forman parte del costo y cómo se distribuyen.
- Ejemplo ilustrativo, no precio aprobado: a USD 3 por unidad, costo de USD 30/12 deja diferencia bruta de USD 0.50 por unidad; costo de USD 25/12 deja aproximadamente USD 0.9167, antes de otros gastos. El margen mejora manteniendo el precio.
- Las devoluciones deberían conservar referencia al lote original cuando sea identificable y pasar revisión antes de volver a disponibilidad; excepciones pendientes.

### Ubicaciones y trazabilidad de extracciones

#### Bodega central y bodegas de sucursal

Requisitos confirmados por el usuario:

- Existirá una bodega central o centro de distribución y bodegas de sucursal. Confirmado: los pedidos en línea se preparan exclusivamente en las sucursales; el centro de distribución no atiende pedidos de clientes, abastece a las sucursales mediante traslados.
- Se necesitan hojas de traslado entre bodegas para mantener existencias reales sincronizadas.
- Se debe poder consultar, para cada producto, qué cantidad existe en la bodega central/centro de distribución y qué cantidad en la tienda/sucursal, junto con el total consolidado.
- La consulta debe respetar los permisos del usuario; consultar cantidades no concede acceso a costos ni precios restringidos.
- El diseño debe distinguir bodegas, además de las ubicaciones internas ya definidas. Inventario único por producto significa una identidad de producto y un total consolidado, no un saldo indistinto entre bodegas.

Propuesta operativa pendiente de aprobación:

- Jerarquía: bodega → pasillo → fila → columna. Una misma coordenada puede existir en bodegas diferentes sin ser la misma ubicación.
- Hoja de traslado con identificador, origen, destino, productos, lotes, presentaciones, cantidades solicitadas/enviadas/recibidas, responsables, fechas e incidencias; sin precios para personal de bodega.
- Flujo propuesto: solicitado → autorizado → en preparación → en tránsito → recibido/cerrado. Falta definir qué pasos son necesarios y quién puede ejecutarlos.
- Al despachar, mover el saldo físico del origen a tránsito; al confirmar recepción, mover únicamente lo recibido a la ubicación de destino. No sumar stock disponible en destino antes de recibirlo ni descontarlo dos veces.
- Mostrar existencias por bodega y un consolidado que incluya el tránsito por separado, sin contarlo como disponible para venta en ambas bodegas.
- Conservar lote, fecha original de recepción, costo histórico y estado de empaque durante el traslado; los datos monetarios permanecen restringidos según permisos.
- Registrar recepción parcial, diferencias y daños. Lo no recibido debe permanecer pendiente de conciliación o tener una resolución autorizada, nunca desaparecer por cerrar la hoja.
- Tratar traslados internos de ubicación y traslados entre bodegas como movimientos relacionados pero con procesos diferentes cuando exista despacho/recepción.
- Permisos por bodega y acción: solicitar, autorizar, preparar, despachar, recibir y resolver diferencias. Auditar responsables y evitar confirmaciones duplicadas.
- El cliente elige sucursal para web y existe una configuración por defecto. Falta definir reasignación posterior, pedidos pendientes de abastecimiento y cancelación de traslados despachados. No asumir división entre sucursales ni reservar central/tránsito como stock recibido.

#### Ubicaciones internas

- Las cajas estarán almacenadas en ubicaciones identificadas por **pasillo, fila y columna**. Ejemplo confirmado: pasillo A, fila 1, columna 2.
- Se podrán registrar movimientos de inventario entre ubicaciones y se conservará su historial.
- Se conservará el historial de extracciones de cada producto, vinculado con las órdenes de compra correspondientes.
- Para respetar la secuencia ya definida (la orden de compra se genera después de finalizar la extracción), las extracciones se relacionarán inicialmente con el pedido y su hoja de pickeo; al generarse la orden, quedará enlazada con esos registros. No se requiere una orden de compra previamente emitida para empezar el pickeo.

#### Detalle de diseño propuesto para esa trazabilidad

- Mostrar una identificación legible como A-01-02, conservando pasillo, fila y columna como datos separados. El formato definitivo del código no está decidido.
- Cada traslado registraría producto, cantidad y presentación, ubicación de origen y destino, fecha/hora, responsable y motivo. El traslado cambia saldos por ubicación, no la existencia total del producto.
- Cada extracción registraría producto, cantidad real extraída, presentación, ubicación de origen, fecha/hora, responsable, pedido y hoja de pickeo. La orden de compra posterior permitiría consultar ese historial y viceversa.
- Separar reserva, extracción a preparación y salida física. Confirmado: la salida web se registra al completar y despachar el pedido, sin descontar nuevamente al entregar o facturar.
- Confirmado: generar codificación de cajas/bultos y relacionar su contenido con unidades/fardos/paquetes enviados y recibidos. Definir el momento de identificación, formato de etiqueta y detalle de división/reempaque. El código no agrega existencias a las del contenido.

### Unidades, fardos y cajas

- El inventario debe permitir conteo y manejo por unidades y agrupaciones, incluyendo fardos y cajas.
- Ejemplo proporcionado: una caja de 100 unidades equivale en cantidad a 10 fardos de 10 unidades. Es un ejemplo, no una equivalencia universal para todos los productos.
- Se venderán unidades y fardos; el precio por unidad al comprar un fardo debe ser menor que al comprar unidades sueltas. Los precios concretos y su relación con las compras de 3 unidades siguen pendientes.
- La extracción para venta al detalle puede abrir fardos y dejar fardos incompletos.
- Confirmado: sala y bodega de sucursal separadas; web solo utiliza bodega. La disposición interna de fardos cerrados y abiertos sigue por diseñar.

#### Diseño aprobado por el usuario

El usuario aprobó la propuesta de manejo de inventario por unidades, presentaciones y ubicaciones. Esta aprobación define el diseño; no implica iniciar su implementación mientras continúa el levantamiento del alcance.

- Usar un único inventario por producto, con la unidad individual como base del stock. Fardo y caja son presentaciones con equivalencias configurables por producto; no existencias adicionales que se sumen de nuevo ni inventarios independientes para detalle y mayoreo.
- Distinguir la cantidad total de unidades de su estado de empaque: equivaler a 10 fardos no implica que existan 10 fardos cerrados físicamente.
- Registrar apertura de fardos como cambio de empaque, sin disminuir el stock total; la salida de venta se registra por separado y una sola vez.
- Llevar saldo por ubicación y distinguir fardos cerrados y unidades sueltas o contenido de fardos abiertos. Evaluar identificación individual de fardos abiertos solo si la operación lo necesita.
- Para detalle, priorizar fardos abiertos dentro del lote elegible según PEPS. Para una venta por fardo, usar la presentación existente o completar mediante reempaque autorizado del mismo producto y características, con cantidades y orígenes registrados. No confundir equivalencia de unidades con la existencia previa de un fardo físico.
- Mantener un inventario único por producto con ubicaciones físicas. Iniciar con una ubicación si el volumen lo permite, separando físicamente cerrados y abiertos; permitir después una zona de reserva y otra de preparación/detalle con traslados registrados, sin duplicar productos.
- La hoja de extracción mostrará presentación solicitada, equivalencia en unidades, ubicación y desglose de extracción (fardos completos y unidades sueltas).
- Ejemplo: 10 fardos cerrados de 10 unidades suman 100 unidades. Al vender 3 unidades de uno de ellos quedan 9 fardos cerrados y 7 unidades en el fardo abierto: 97 unidades totales.

#### Decisiones que siguen pendientes

- Elegir la distribución física inicial: misma ubicación con separación de cerrados/abiertos, o zonas distintas de reserva y preparación. El diseño soportará ambas.
- Definir si el descuento por fardo requiere pedir esa presentación cerrada, si se aplica automáticamente por cantidad y cómo se cotiza una compra de unidades y fardos del mismo producto. No se permite mezclar productos o características diferentes para alcanzar el mayoreo; el precio mayorista por fardo aplica desde 3 fardos iguales; importes y mezcla de presentaciones pendientes.
- Decidir si es necesario identificar cada fardo abierto individualmente o basta con controlar sus cantidades por ubicación.

## Implicaciones y propuestas preliminares, no aprobadas

### Marketing digital, SEO y campañas

Implementación puntual solicitada posteriormente: se agregaron metadatos SEO, Open Graph y tarjetas sociales al HTML de páginas públicas, con imagen de cada producto y logo de respaldo. Ver `docs/metadatos-seo-y-redes.md`. Este cambio no implica implementar el resto del alcance de marketing ni desplegar a producción.

- El usuario solicita preparar la web para campañas en redes sociales, enlaces semánticos y los componentes necesarios para cumplir los requisitos aplicables a las campañas.
- Se incorpora este objetivo al alcance. No existe una aprobación universal de campañas garantizada por el desarrollo: se deben revisar políticas vigentes de cada plataforma, anuncios, productos y cuentas al preparar su lanzamiento.
- Propuesta: URLs descriptivas y estables para productos/categorías, canonical y redirecciones 301 cuando cambie una URL. Evitar que un cambio de nombre rompa enlaces de anuncios anteriores; el código actual regenera slugs al cambiar el nombre.
- Propuesta: títulos y descripciones editables, sitemap, robots e indexación del catálogo público, datos estructurados coherentes con precios y disponibilidad visibles, e imágenes optimizadas. Excluir áreas privadas de indexación sin sustituir autenticación por robots.txt.
- Propuesta: metadatos Open Graph para compartir productos/campañas con título, descripción e imagen. Las páginas deben funcionar en web sin exigir instalar la PWA.
- Propuesta: páginas de destino para campañas, coherencia entre anuncio y oferta, experiencia móvil rápida, información clara de precio por presentación/cantidad, disponibilidad, entrega, medios de pago, contacto y políticas. No anunciar un precio de fardo como si fuera precio unitario sin sus condiciones.
- Propuesta: parámetros UTM y atribución del origen de pedidos; eventos diferenciados de vista de producto, lista/carretilla, inicio de pedido, pedido enviado y pago verificado. Definir el evento de conversión final según el flujo: no contar como compra pagada un pedido pendiente ni una carga de comprobante.
- Evaluar GA4, Meta Pixel/Conversions API y catálogos de anuncios solo al elegir canales y autorizar integraciones. Deduplicar eventos de navegador/servidor y reintentos para no inflar resultados.
- No activar rastreadores, enviar datos a terceros ni publicar campañas en esta etapa. Definir privacidad, consentimiento y minimización de datos según el contexto aplicable antes de activar medición; no enviar costos, márgenes, comprobantes, datos internos o información personal en URLs/eventos.
- Permisos específicos de marketing para contenidos, páginas de destino y métricas, sin conceder acceso implícito a costos ni administración de usuarios.
- Fuentes oficiales consultadas: https://developers.google.com/search/docs/specialty/ecommerce/designing-a-url-structure-for-ecommerce-sites ; https://support.google.com/google-ads/answer/14086 ; https://www.facebookblueprint.com/student/activity/612287-optimize-meta-conversions-api

### Uso como PWA

- El usuario solicita contemplar el uso del sistema como PWA y pide evaluar la propuesta.
- Se incorpora al alcance de diseño una experiencia web adaptable e instalable, manteniendo una sola aplicación y permisos por rol. Las capacidades exactas sin conexión siguen pendientes.
- Propuesta inicial: permitir instalación en dispositivos compatibles y uso en móvil, tableta y escritorio; HTTPS en producción, manifiesto e iconos, y una estrategia explícita de actualización y caché.
- Para evitar inconsistencias, exigir conexión y validación del servidor al enviar/confirmar pedidos, verificar pagos, comprometer existencias, confirmar extracciones, despachar/recibir traslados y registrar ajustes/devoluciones. No confirmar localmente operaciones que cambien inventario como si ya estuvieran sincronizadas.
- Sin conexión, proponer solo catálogo previamente consultado y lista/borrador de compra no sensible, claramente marcado como no enviado. Mostrar fecha de actualización y advertir que precios y existencias pueden haber cambiado; revalidar al reconectar.
- No almacenar automáticamente en caché costos, pagos, documentos privados o respuestas autenticadas. Definir segregación y limpieza de datos locales al cerrar/cambiar sesión, especialmente en dispositivos compartidos de bodega.
- Instalación, notificaciones y capacidades disponibles varían por navegador/dispositivo. Notificaciones no se consideran aprobadas ni sustituyen el panel de seguimiento.
- PWA no garantiza inventario en tiempo real: se necesita sincronización con el servidor y actualización de vistas conectadas. No se ha elegido transporte (consultas periódicas, eventos u otro).
- Proponer PWA después de estabilizar los flujos y permisos, sin posponer el diseño adaptable y táctil desde el inicio.

### Nombre y función del documento posterior al pickeo

- El usuario aclara que el documento llamado hasta ahora «orden de compra» será similar al pedido, pero representará el resultado de la preparación y será el documento utilizado para facturar externamente.
- El nombre «orden de compra» **no es definitivo**: el usuario solicita sugerencias. Las menciones anteriores deben entenderse como un nombre provisional, sin cambiar la función ni el momento de generación acordados.
- El usuario utiliza **orden de venta** para la preparación final y propone **prefactura** para facturación externa. Se recomienda una impresión de la misma orden con ese título, sin duplicar documentos operativos ni salidas. Nombre final de la impresión pendiente.
- Mantener la distinción entre pedido (lo solicitado), hoja de pickeo (instrucción y registro de extracción) y documento final (resultado de la preparación para facturar).
- Conservar el pedido original y generar la orden de venta con cantidades efectivamente preparadas. Confirmado: contactar al cliente por faltantes para autorizar parcial y devolver lo no suministrado. Propuesta: prefactura como impresión para facturación externa de esa misma orden, sin duplicar venta ni salida; nombre final pendiente.
- No implica facturación fiscal dentro de la plataforma ni confirma que el proceso externo haya facturado el documento.

### Rediseño UX/UI solicitado

- El usuario solicita una transformación del diseño, con análisis cuidadoso de UX y UI.
- Objetivos expresos: sistema moderno, atractivo, de impacto visual, fácil de usar y con procesos ágiles.
- Este trabajo debe contemplar tanto la experiencia del cliente como las tareas de vendedores, administración y bodega. No limitar el rediseño a colores y estilos.
- Propuesta de proceso: completar el alcance, definir recorridos por rol, arquitectura de navegación, estados e incidencias; después realizar prototipos y validar tareas representativas antes de implementar las pantallas.
- Propuestas para el cliente: catálogo legible, selección clara de unidad/fardo/caja y precios, lista/carretilla sencilla, instrucciones de pago y seguimiento comprensible.
- Propuestas para operación: panel de pedidos con prioridades y tiempos, acciones según rol y estado, verificación de pagos diferenciada, pickeo con ubicación y cantidades destacadas, registro rápido de movimientos y manejo visible de incidencias.
- Propuestas transversales: diseño adaptable a dispositivos, contraste y legibilidad, botones cómodos, prevención de errores, confirmación de acciones sensibles y mensajes claros.
- La dirección visual, dispositivos de uso en bodega, indicadores de agilidad y herramientas de validación aún no están decididos. No se han creado diseños ni modificado pantallas en esta etapa.

### Otras implicaciones del flujo operativo

- El checkout actual intenta descontar inventario inmediatamente. Debe adaptarse para separar reserva temporal al aceptar el pedido, confirmación al verificar el pago y salida física, sin confundir esos eventos.
- Pedidos simultáneos no deben reservar las mismas unidades. La aceptación y reserva deben ser atómicas; vencimiento y verificación deben coordinarse para no liberar una reserva ya confirmada. Un pago tardío no debe reactivar una reserva vencida sin revalidar disponibilidad y resolver la incidencia.
- Distinguir existencias físicas, reservas temporales, unidades comprometidas tras verificar el pago y unidades disponibles. Las categorías deben ser excluyentes; confirmar, preparar y despachar no deben descontar dos veces el mismo artículo.
- Subir un comprobante no equivale a verificar pago. Encargado o vendedor autorizado verifican; varias transferencias deben cubrir el total y quedar verificadas para confirmar el pedido. El permiso es configurable.
- La hora para pagar/reportar y el tiempo de verificación son eventos distintos. Confirmado para pedidos en línea: reportar el pago con reserva vigente mantiene las unidades durante la revisión. Propuesta: cola de revisión con responsable, antigüedad y alertas; no convertir una alerta en liberación automática. Se permite liberación manual por reportes rechazados u otras incidencias; faltan los estados de resolución y el tratamiento de pagos tardíos.
- Conviene separar el estado del pedido del estado del pago para manejar incidencias sin perder trazabilidad.
- Si se permiten invitados, el seguimiento requerirá acceso seguro y datos mínimos de contacto; no basta con un número de pedido público. La creación de una cuenta posterior podría vincular pedidos solo después de verificar la identidad/contacto.
- Aprobado: invitado con contacto verificable y seguimiento seguro; cuenta opcional. Ver docs/decisiones-2026-08-28.md.
- Para pedidos ingresados por vendedores, conservar por separado cliente, colaborador y canal (presencial/en línea): el actor no determina por sí solo el flujo de reserva.
- Confirmado: el reempaque puede extraer de varios lotes/ubicaciones del mismo producto y características, con trazabilidad. Ante faltantes después del pago se consulta al cliente para entrega parcial y devolución de lo no suministrado; faltan detalles de reembolso/cancelación.
- La plataforma **no realizará facturación por el momento**. La orden generada al concluir la extracción no debe presentarse como factura ni implicar que el pedido ya fue facturado.
- Se utiliza orden de venta para el pedido preparado; el usuario propone prefactura para facturación externa. Pendiente nombre final, numeración, campos y referencia de la factura externa. No confundir estos documentos con comprobantes de pago o factura fiscal.
- El inventario inicial, las salidas, devoluciones y ajustes por conteo deben ser auditables para explicar diferencias físicas. La implementación actual permite sobrescribir stock sin registrar todos esos movimientos.
- Las recomendaciones pueden iniciar con asociaciones manuales y evolucionar a compras conjuntas cuando exista suficiente historial; no se ha decidido la técnica.

### Últimas precisiones: carga, conteo y alcance diferido

- Adaptar la carga inicial al modelo de productos/variantes, presentaciones, ubicaciones, lotes, cajas y costos restringidos; ver propuesta de formato en docs/decisiones-operativas-confirmadas.md. El importador CSV actual no cubre ese alcance.
- Confirmado: conteos físicos periódicos para bodegas y salas. Propuesta: programación por zonas, responsables, corte, recuento de diferencias y ajuste autorizado; frecuencia y control de movimientos durante conteo pendientes.
- Confirmado: traslados con fecha/hora de salida, cajas identificadas y cantidades que deben conciliar con la recepción. Propuesta: registrar solo recepción real y resolver faltantes, sobrantes o daños con trazabilidad, sin forzar igualdad.
- Confirmado: factura externa vinculada a orden de venta, sin otra descarga. Campos y cardinalidad de la relación pendientes.
- Parking lot confirmado: fidelización, recomendaciones, PWA sin conexión y detalles visuales finales. UX/UI de los procesos continúa formando parte de la definición funcional.

Se continúa recopilando información. No se han implementado estos cambios ni establecido reglas no confirmadas por el usuario.

### Estándar de UX/UI confirmado para próximos entregables — 28 de agosto de 2026

El usuario aprueba la calidad de presentación de la pantalla de permisos y auditoría como referencia para los próximos entregables. El sistema debe ser moderno, útil, minimalista y mantener una experiencia de usuario e interfaces de alto nivel. Este criterio acompaña cada incremento; no se difiere toda la presentación al final.

- Mantener coherencia de tipografía, colores, espaciado, botones, formularios y navegación entre módulos.
- Organizar las pantallas según la tarea y las responsabilidades del usuario, con acciones principales claras y la información necesaria para decidir.
- Utilizar lenguaje comprensible, etiquetas explícitas y estados distinguibles por texto, no solo por color.
- Diseñar también estados vacíos, errores, acceso denegado, validaciones y confirmaciones de operaciones sensibles.
- Cuidar adaptación a pantallas pequeñas, navegación por teclado, foco visible y legibilidad de tablas y formularios.
- Verificar comportamiento y presentación antes de dar por validada una interfaz; declarar cualquier revisión visual pendiente.
- Preservar permisos, trazabilidad y reglas de negocio al mejorar la interfaz. No introducir indicadores ficticios ni presentar funciones pendientes como disponibles.

Los detalles decorativos finales pueden seguir en el alcance diferido, pero la claridad, usabilidad y buena presentación son criterios de aceptación de cada entrega.
