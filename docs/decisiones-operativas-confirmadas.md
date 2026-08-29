# Decisiones operativas - ampliación del 27 de agosto de 2026

> Actualización vigente del 28 de agosto: consultar [Decisiones del 28 de agosto](decisiones-2026-08-28.md). Sustituye umbrales, propuestas de invitados y permisos anteriores incompatibles; no acredita implementación.

Este documento complementa el contexto y el diagnóstico. Registra aclaraciones del usuario y distingue recomendaciones pendientes. No acredita implementación; el PDF anterior no incorpora estas novedades.

## 1. Sucursal y ubicaciones

Confirmado: el cliente puede elegir la sucursal para su pedido en línea. Se conserva la configuración de sucursal por defecto; propuesta de convivencia: mostrarla como selección inicial que el cliente puede cambiar antes de enviar. Central abastece a sucursales mediante traslados y no prepara pedidos de clientes.

Sala de ventas y bodega de sucursal tendrán ubicaciones y saldos separados dentro del mismo inventario. La web solo reserva de la bodega de la sucursal elegida, nunca de sala. Para usar mercancía de sala en un pedido web debe trasladarse físicamente y registrarse su ingreso a bodega antes de reservarla/extraerla. No duplicar existencias.

No se autoriza por defecto dividir pedidos entre sucursales. Propuesta: un pedido se vincula a una sucursal al aceptarse; cambiar la configuración global solo afecta pedidos nuevos. Cambiar sucursal en la carretilla obliga a revalidar existencias, condiciones de entrega y precios aplicables, sin reservar mientras sea carretilla. Una reserva existente no cambia de sucursal silenciosamente; falta confirmar reasignaciones excepcionales. Si no alcanza el stock, no tomar automáticamente de sala, central, tránsito u otra sucursal.

## 2. Clientes invitados - aprobados el 28 de agosto

Aprobado: permitir compra como invitado con nombre, teléfono/contacto verificable, método de entrega y dirección solo para envío; permitir login a quienes ya tienen cuenta. Correo según el canal de seguimiento elegido, evitando pedir datos innecesarios.

Invitado no significa anónimo: guardar el pedido con datos del cliente y ofrecer seguimiento seguro. Propuesta: enlace privado con token no predecible o código de acceso; el número de pedido por sí solo no concede acceso. Definir canal y proveedor antes de prometer SMS o WhatsApp automático.

Ofrecer crear cuenta después, para historial, recompra y futura fidelización. Vincular pedidos anteriores únicamente tras verificar el contacto; no crear cuentas ni suscripciones comerciales sin consentimiento. Verificar contacto y limitar abuso ayuda con reservas falsas; no exigir contraseña no elimina la necesidad de controles.

La investigación de Baymard sobre checkout respalda evitar la creación de cuenta obligatoria como barrera, sin garantizar los mismos resultados para esta empresa: https://baymard.com/blog/make-guest-checkout-prominent

## 3. Entrega y tarifas

Confirmado: retiro en sucursal y entrega a domicilio, con seguimiento de despachado a entregado y confirmación de entrega. El costo de envío varía por distancia o medio de transporte. Se contempla configurar un monto mínimo para envío gratuito.

Pendiente: cobertura, medios, tarifas, monto mínimo, excepciones de gratuidad, horarios, responsable y evidencia de entrega. No se interpreta seguimiento como GPS en vivo ni integración automática con transportistas.

Propuesta: calcular o cotizar y aceptar el envío antes de solicitar el pago completo. Definir cuándo inicia la hora si la tarifa requiere cotización manual. No cobrar un total desconocido ni agregar cargos sin aceptación. La recepción por el transportista no equivale a entrega al cliente.

## 4. Presentaciones y precios

Confirmado: algunos productos se venden por fardo, seleccionado como presentación comercial, pero el inventario sigue en unidades base. Ejemplo: 3 fardos de 10 equivalen a 30 unidades del mismo producto y califican para mayoreo. La venta de una unidad suelta puede tener un precio unitario superior.

Actualización del 28 de agosto: unidades sueltas desde tres inclusive y precios normal/mayorista propios para fardos. La interpretación anterior por unidades base no se adopta como regla general: mínimo de fardos confirmado en 3 iguales; combinación de presentaciones pendiente. Ver docs/decisiones-2026-08-28.md.

Falta precisar el precio de 1 fardo, la relación entre fardo y unidades sueltas en cantidad mayorista, las escalas adicionales y el momento en que se fija el precio. No suponer que 1 fardo tiene el mismo precio total que comprar su contenido suelto, ni inventar un importe. Mantener la regla de no mezclar productos o variantes diferentes.

## 5. Pagos completos y pedidos vencidos

Confirmado: no hay pagos parciales como condición para preparar/entregar ni venta a crédito. Sí pueden registrarse varias transferencias para cubrir un pedido; verificar cada una y confirmar el pago del pedido solo cuando se compruebe el total exigible. No confundir varios movimientos de pago con autorización de cumplimiento parcial.

El efectivo de un pedido web puede recibirse en sucursal para continuar su entrega, registrado y confirmado por personal autorizado. Si vence la reserva sin reporte de pago, el pedido no se reactiva: se crea uno nuevo.

Se permite iniciar un pedido a partir de otro. Copiarlo como borrador, conservando vínculo al original y revalidando disponibilidad; propuesta: también precios, presentaciones vigentes, sucursal y envío. No copiar pagos verificados, reservas, estado de entrega ni cargos como si fueran nuevos. El original conserva su historial.

Reportar el pago con reserva vigente sigue manteniéndola durante revisión. La liberación manual por incidencias permanece autorizada. Pendiente: cómo resolver dinero recibido después del vencimiento y si se puede aplicar al nuevo pedido tras conciliación autorizada; nunca reutilizar automáticamente una transferencia en dos pedidos.

## 6. Faltantes después del pago

Confirmado: contactar al cliente para autorizar entrega parcial. Devolver el dinero correspondiente a lo no suministrado; falta concretar el alcance del reembolso si el cliente rechaza toda la entrega, gastos de envío y el procedimiento externo.

Registrar autorización del cliente, cantidades preparadas/omitidas, importe por conciliar y responsable. Propuesta: no sustituir productos ni cerrar el pedido como completo sin autorización; si se cancela todo, gestionar devolución total según la política que se apruebe. No presentar como autorizado el mecanismo de reembolso todavía no definido.

## 7. Devoluciones y deterioros

Confirmado: recibe el encargado o supervisor de tienda. Las devoluciones ingresan al inventario; los productos dañados también ingresan y luego se descargan por daños/deterioro.

Propuesta de control: el ingreso se realiza en condición de revisión o dañado, no disponible para venta. Tras inspección, lo apto se habilita; lo dañado se da de baja con motivo y autorización. Así se registra entrada y baja sin abrir una ventana donde se pueda vender mercancía dañada.

Conservar pedido/venta original cuando exista, producto/variante, cantidad, lote identificable, ubicación, condición, responsable y movimientos. El artículo ya incluido en existencias que se daña no recibe una segunda entrada: se reclasifica y luego se da de baja. No inventar un lote cuando una devolución no pueda identificarse.

## 8. Permisos

Confirmado: encargado de tienda o vendedor pueden verificar pagos; vendedor puede liberar reservas; encargado realiza ajustes y confirma traslados. Estos permisos serán configurables según responsabilidad y confianza, no atribuciones rígidas para todos los vendedores.

Mantener las restricciones ya acordadas: vendedores sin costos y bodega sin importes. Propuesta: permisos separados por acción y sucursal, administración restringida a quien puede conceder/revocar accesos, motivos e historial para operaciones sensibles. Confirmado el 28 de agosto: precios y permisos solo superiores; parametrizables por rol/usuario. Ver actualización para límites y delegación.

Confirmado el 28 de agosto: conceder y quitar permisos por rol, con excepciones individuales administradas por superiores. Definir precedencia y mostrar permisos efectivos.

## 9. Salida física y documentos

Confirmado: la salida física del pedido web se registra cuando la preparación está completa y se despacha/coordinan los medios de entrega, con orden de venta. Para una entrega parcial autorizada, registrar únicamente la cantidad efectivamente despachada. No descontar de nuevo al marcar entregado o al facturar externamente.

La venta presencial descarga inventario en Los Cheles aunque se facture fuera. Central registra salidas por traslado y recepción en sucursal, no ventas a clientes.

Recomendación de nombres: orden de venta para el documento comercial resultante de la preparación; vista o impresión titulada “Prefactura - documento para facturación externa”, vinculada a la misma orden, con cantidades e importes preparados. No crear otra venta ni otra salida por emitirla.

El nombre prefactura es una propuesta del usuario, pendiente de elección final. Presentarla como documento operativo, no como factura fiscal ni prueba de pago. No se está dictaminando su validez legal. Propuesta: conservar referencia de la factura externa sin alterar el documento histórico.

## 10. Decisiones prioritarias aún abiertas

- Definir método de contacto/seguimiento para invitados; checkout sin cuenta ya aprobado.
- Confirmar cálculo de mayoreo por unidades base y tabla de precios por presentación, incluido 1 fardo.
- Definir tarifas, mínimo gratuito y momento de cotizar envío respecto a reserva y pago.
- Resolver pagos tardíos, autorización y ejecución de reembolsos.
- Completar responsables de precios/permisos y excepciones de reasignación de sucursal.

No hace falta reabrir la separación sala/bodega, el abastecimiento desde central o la política de salida física: esas decisiones ya están confirmadas.

## 11. Carga inicial de inventario

Revisado el código local: ProductoImportController ofrece CSV con sku, nombre, descripcion, precio_base, stock e is_activo, previsualización y confirmación. Al actualizar un SKU existente establece el stock absoluto. Es una base reutilizable de interfaz, pero no representa lotes, ubicaciones, cajas ni movimientos completos del nuevo alcance. No se ejecutó una importación ni se cambió inventario en esta revisión.

El usuario solicita adaptar el formato a lo discutido. Propuesta: separar catálogo/presentaciones, existencias iniciales y costos restringidos. Puede resolverse mediante plantillas CSV relacionadas; no se ha elegido ni generado una plantilla definitiva.

Una fila de existencias describe una cantidad homogénea por producto/variante, lote, ubicación, condición y presentación. Datos propuestos:

- SKU/variante; bodega/sucursal y ubicación (sala/bodega, pasillo, fila, columna).
- Lote interno o referencia de origen, documento/proveedor cuando se conozca, fecha original de recepción y condición.
- Presentación, cantidad de esa presentación y equivalencia a unidades base. El total base se calcula y verifica; no se suma otra vez como stock independiente.
- Identificador de caja/bulto cuando esté identificado; un mismo bulto puede requerir varias líneas de contenido. Definir si se permiten productos mezclados dentro de una caja física; no asumirlo.
- Fecha de corte del inventario inicial y observaciones de datos desconocidos.

El sistema genera identificador de carga y línea, usuario, fecha de carga, archivo/huella y resultado. La fecha de carga no reemplaza la antigüedad real. Datos desconocidos se marcan como tales y requieren una política de validación; no inventar fechas o costos cero. Costos/moneda y base del costo se cargan con permiso separado, ligados al lote; bodega no recibe esos campos ni los archivos originales.

Evitar doble conteo: por ejemplo, 2 fardos cerrados de 10 más 3 sueltas se expresan como dos líneas de composición (20 + 3 = 23), no como 2 cajas más 23 unidades adicionales. Las cajas son contenedores identificados, no stock que se suma a su contenido.

Propuesta de proceso: validar maestros y archivo → mostrar errores y totales por bodega/ubicación → aprobar → registrar movimientos de apertura con trazabilidad. Detectar cargas repetidas, definir confirmación atómica o parcial explícita y no sobrescribir saldos operativos. Los conteos posteriores generan ajustes autorizados, no una nueva carga inicial.

## 12. Conteos físicos periódicos

Confirmado: se requiere un proceso periódico para conocer el inventario físico de bodegas y salas de venta. No es una solicitud de recordatorio o automatización de esta conversación.

Propuesta: ciclos configurables por sede, zona y productos, con responsable, fecha programada, fecha de corte y estados planificado/en conteo/en revisión/cerrado. Frecuencia y política de movimiento durante conteo siguen pendientes.

Contar existencias físicas por lote/ubicación/condición, no solo disponible comercial: las unidades reservadas que siguen presentes también se cuentan, una vez. Registrar fardos/cajas por su contenido y unidades sueltas, sin duplicarlos. Si un bulto está sellado, definir verificación de contenido; escanear su código no prueba por sí solo la cantidad física.

Propuesta: captura ciega cuando corresponda, segundo conteo de diferencias, motivo y aprobación del ajuste. No cambiar stock automáticamente al capturar una diferencia. Para operar mientras se cuenta, elegir bloqueo temporal de la zona o un corte con conciliación de movimientos; una reserva no equivale a una salida física.

## 13. Cajas identificadas y diferencias de traslado

Confirmado: generar codificación para cajas/bultos y registrar cuántas salen y qué cantidades de unidades/fardos/paquetes contienen. Conservar fecha y hora de salida de origen y contrastar el detalle enviado con lo realmente recibido.

Propuesta: código único estable, etiqueta legible con código de barras o QR, contenido por producto/variante, lote y unidades base, estado de empaque y ubicación. No codificar la ubicación como identidad permanente ni exponer costos en la etiqueta. Definir si el código se asigna al ingreso o al armar el bulto; conservar genealogía cuando se divide o reempaca.

La hoja de traslado vincula códigos de bulto, contenido enviado, fecha/hora, origen/destino y responsables. La recepción registra fecha/hora, bultos y cantidades reales por línea. Comparar ambos, no solo el número de cajas: dos cajas pueden tener contenidos distintos.

Propuesta para diferencias: 100 unidades enviadas, 98 recibidas aptas y 2 sin localizar → ingresar 98, mantener 2 como diferencia de tránsito pendiente de resolución, sin fingir recepción de 100. Si se reciben 100 y 2 están dañadas → registrar 98 aptas y 2 bloqueadas/dañadas, sin faltante ficticio. Un sobrante inesperado requiere investigación, no creación automática de stock vendible sin origen.

La exigencia de que cuadre significa conciliación por cantidad y condición, no forzar números. Cerrar con igualdad o resolución documentada y autorizada de cada diferencia; este mecanismo de resolución es una propuesta pendiente de detalle.

## 14. Factura externa y alcance diferido

Confirmado: la factura externa se relaciona con la orden de venta. La cadena propuesta es pedido → pickeo/extracciones → orden de venta → referencia de factura externa; movimientos y pagos quedan enlazados sin otra descarga al registrar la factura.

Propuesta de campos: sistema emisor, tipo/serie/número o identificador, fecha, total, usuario que vincula y archivo opcional restringido. Definir si una orden puede tener varias facturas y el tratamiento de anulaciones/notas; no presumir una relación uno a uno ni integración automática. Importes y documentos solo para roles autorizados.

La frase anterior “descarga de inventario por defecto” no tiene significado recuperado por el usuario. Se conserva como antecedente ambiguo, sin crear una función adicional ni bloquear el diseño. Las bajas por daños/deterioro ya tienen un requisito explícito independiente.

Confirmado como parking lot: fidelización/puntos, recomendaciones, capacidades PWA sin conexión y detalles visuales finales. No se descartan ni se implementan ahora. UX/UI de los flujos de compra, preparación y venta presencial sigue acompañando las definiciones; no se pospone la claridad de estados, navegación, mensajes o prevención de errores.
