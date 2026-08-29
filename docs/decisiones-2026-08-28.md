# Decisiones del 28 de agosto de 2026

Actualización del levantamiento de alcance. Sustituye las reglas anteriores incompatibles indicadas aquí; no implica cambios de código, inventario o permisos reales.

## Compra como invitado: aprobada

El usuario aprueba enviar pedidos sin crear cuenta, con datos y contacto verificable. Mantener login opcional, seguimiento privado y oferta de cuenta posterior. Falta elegir el mecanismo y proveedor de verificación, sin asumir SMS/WhatsApp automático ni activar servicios.

## Precios por presentación

Confirmado: cada producto puede tener precio normal y mayorista para unidad, y precio normal y mayorista para fardo. No duplicar productos ni existencias: la presentación conserva equivalencia a unidades base.

Cambio explícito respecto al 27 de agosto: las unidades sueltas califican desde **3 unidades iguales o más**, no desde 4. Confirmado posteriormente el 28 de agosto: el precio mayorista de fardos aplica desde **3 fardos iguales o más**. Se mantiene no mezclar productos/variantes. El contenido en unidades base sirve para inventario; no convierte un solo fardo en tres fardos para precios.

Propuesta sencilla de configuración: tabla por producto/variante y presentación con equivalencia, precio normal, cantidad mínima mayorista y precio mayorista. Mínimos confirmados: 3 unidades sueltas o 3 fardos iguales, según presentación. Sumar líneas repetidas de la misma presentación/variante antes de aplicar el umbral. No sumar unidades de un producto distinto. Ejemplos: 2 fardos usan precio normal de fardo; 3 fardos usan precio mayorista por fardo, aunque el inventario se convierta a sus unidades base.

Propuesta inicial: calcular cada presentación por separado (ejemplo: 1 fardo + 2 sueltas usa precio de 1 fardo y precio normal de las 2 sueltas), sin combinación automática para alcanzar mínimos. Pendiente aprobar esta política mixta, posibles escalas adicionales e importes. Mostrar cantidad, presentación, contenido y total para evitar confusión.

Propuesta de fijación: validar precios y envío al enviar/aceptar el pedido, guardar una copia de las tarifas aplicadas y mantenerla durante la reserva y revisión. Cambios de catálogo no alteran pedidos aceptados; repetir uno vencido usa precios actuales. Cambios de cantidades requieren nueva aceptación. El momento exacto aún requiere aprobación.

## Cambios en pedidos enviados: revisión aprobada

Confirmado: cambiar cantidades, sucursal o entrega de un pedido enviado requiere revisión explícita de existencias, precio y pago. No modificar silenciosamente pedidos reservados o pagados.

Propuesta de implementación: conservar versión anterior, responsable, motivo, diferencias y autorización; validar y aplicar el cambio de forma atómica. Si no puede completarse, mantener el pedido y su reserva anterior sin pérdidas. Diferencias monetarias se resuelven con los permisos de pagos/reembolsos ya definidos. Faltan los roles habilitados para solicitar/aprobar cada cambio y sus límites después de preparación o despacho.

Esta aprobación no fija por sí sola el instante inicial de congelación del precio ni autoriza sustituciones o cobros automáticos.

## Bandeja de reservas en revisión: aprobada

Confirmado: bandeja con responsable, antigüedad y alertas para reservas mantenidas durante la revisión de pago. Las alertas no liberan automáticamente inventario; se conserva hasta resolución o liberación manual autorizada.

Propuesta: mostrar pedido, sucursal, responsable y fecha de inicio de revisión, tiempo transcurrido, estado de evidencia y próxima acción. Definir umbrales de alerta, escalamiento y acceso por sucursal; información monetaria solo para roles autorizados. No se ha creado una automatización externa ni elegido canales de notificación.

## Tarifas de envío por distancia

El usuario propone tabla configurable de rangos de distancia con tarifa, ajustable por costos operativos. Mandos medios y superiores autorizados pueden administrar/asignar tarifas; vendedores no pueden editarlas ni sustituir manualmente el resultado. Aplicación automática de la regla no es edición de la tarifa.

Propuesta: distancia por ruta desde la sucursal elegida al destino, no distancia en línea recta. Tabla por sucursal/medio cuando sea necesario, límites de rango sin solapamientos, moneda, vigencia, cobertura y condiciones de gratuidad. Guardar distancia calculada, origen/destino y versión de tarifa aplicada, conforme a las condiciones del proveedor elegido.

Puede utilizarse un servicio de rutas; Google Routes expone distancia de ruta, pero no se ha seleccionado proveedor, aprobado presupuesto ni activado integración: https://developers.google.com/maps/documentation/routes/compute_route_directions

Propuesta: cotizar y mostrar el total antes de aceptar pedido/reserva; si dirección o ruta no se resuelve, solicitar corrección o revisión autorizada, sin inventar tarifa cero. Ajustes de tabla afectan cotizaciones nuevas, no pedidos ya aceptados. Faltan importes, cobertura, método de distancia, proveedor, mínimo gratuito y política para fallos/cotización manual.

## Pedidos vencidos y dinero recibido

Confirmado: un pedido vencido no se paga ni reactiva dentro del sistema; debe generarse otro. Se mantiene la opción de copiarlo y revalidar existencias, precios, presentación, sucursal y envío.

El usuario plantea que el vendedor cree inmediatamente el nuevo pedido desde el vencido una vez validado el dinero. Propuesta: permitir crear el nuevo borrador, pero la conciliación y asignación del dinero tardío requiere autorización de un superior, sin duplicar transferencias ni reservas. Registrar vínculo pedido origen/nuevo y aprobación. No asignar dos veces el mismo importe ni confirmar pago hasta cubrir el nuevo total. Diferencias de importe requieren resolución.

Aunque el sistema impida pagar pedidos vencidos, no puede impedir que llegue una transferencia bancaria fuera de plazo. Mantener ese dinero como incidencia pendiente de conciliación autorizada o reembolso; no borrarlo ni reactivar stock liberado.

## Reembolsos, devoluciones y factura externa

Confirmado: solo superiores autorizan y resuelven reembolsos y dinero tardío; vendedores no. Buscar la venta y relacionar la devolución con orden de venta, pagos y factura externa.

El usuario requiere realizar también el trámite correspondiente en el sistema externo de facturación, mencionando anulación de factura. Registrar evidencia/referencia y estado de ese trámite; no afirmar que siempre corresponde anular ni que el sistema externo se modifica automáticamente. El procedimiento fiscal concreto corresponde al sistema externo y a su responsable.

La plataforma registra devolución física y reingreso cuando realmente se recibe mercancía, conservando condición apta/en revisión/dañada. Un reembolso por faltante no genera entrada ficticia de producto que nunca salió. Mantener estados independientes de reembolso, devolución física y corrección externa.

## Conteos y ajustes

Confirmado: solo superiores autorizan ajustes por faltantes, sobrantes o daños. Capturar conteo no autoriza modificar saldos.

Continúa pendiente elegir bloqueo temporal de movimientos por zona frente a corte y conciliación de movimientos durante conteo. Propuesta inicial: bloqueo por zona durante una ventana corta, sin detener todas las sucursales. Debe contemplar reposiciones, ventas y extracciones que afecten esa zona; una reserva comercial no es un movimiento físico.

## Permisos configurables por rol y usuario

Confirmado: precios, administración de permisos y ajustes reservados a superiores; tarifas a mandos medios/superiores; reembolsos y resolución de dinero tardío a superiores. Vendedores conservan solo funciones operativas previamente autorizadas, como verificar pagos ordinarios y liberar reservas pendientes.

Se requiere agregar/quitar permisos de roles y conceder excepciones a usuarios concretos. Conceder una excepción exige autorización administrativa explícita: no convierte un privilegio restringido en permiso general de vendedores.

Propuesta: roles como base más concesiones/denegaciones individuales por acción y sucursal; denegación explícita prevalece. Mostrar permisos efectivos y origen de cada permiso. Quitar un permiso de un rol no elimina una concesión individual salvo revocación explícita: advertirlo al administrador.

Impedir autoescalamiento y concesión de facultades fuera de la autoridad del administrador; auditar responsable, motivo, antes/después y fecha. Los nombres definitivos de roles, alcance de delegación y precedencia requieren aprobación; costos siguen restringidos.

## Pendientes concretos

- Política para combinar fardos y unidades del mismo producto; ambos mínimos ya confirmados en 3 de su presentación.
- Momento definitivo de fijar precios y envío; montos, cobertura, gratuidad y proveedor de rutas.
- Método de verificación de contacto invitado.
- Control de movimientos durante conteo.
- Procedimiento de conciliación/reembolso y referencia de corrección externa; facultades de delegación.

Las propuestas permanecen pendientes de aprobación. Fidelización, recomendaciones, PWA sin conexión y detalles visuales finales continúan en parking lot; UX/UI de flujos permanece activo.
