# Recorridos y bocetos operativos I0

Esquemas de información en texto, no prototipos implementados ni diseño visual final. Versión 0.1. Cada acción debe mostrar progreso, error recuperable y confirmación verificable; deshabilitar botones no sustituye control de servidor.

## UX común

- Cabecera: contexto de sucursal y función; evitar operar sin saber de qué inventario se trata.
- Una acción principal por paso y resumen antes de movimientos irreversibles. Errores junto al campo y foco en el primer error; no depender solo de color.
- Teclado en ventas, controles amplios en preparación, texto legible y adaptación móvil. Validar con dispositivos reales antes de fijar medidas finales.
- Estado vacío con siguiente acción; reintento sin duplicidad; pérdida de conexión indica que no se ha confirmado. No mostrar éxito hasta respuesta del servidor.
- Cambiar sucursal explica qué se revalidará. El dato de disponibilidad lleva contexto de ubicación y estado; nunca sustituir físico por disponible sin etiquetarlo.

## UX01. Comprar y enviar pedido

Actor: cliente invitado o con cuenta. RN01-RN09, RN25. Dependencias D1-D4.

Secuencia: elegir sucursal → explorar ficha → elegir presentación/cantidad → revisar carretilla → contacto y entrega → aceptar total → enviar → reportar pago → seguimiento.

Boceto del resumen:

```text
LOS CHELES                       Sucursal: [Elegida v]
Tu pedido                         [Iniciar sesión, opcional]
Producto / variante | Presentación | Cantidad | Precio | Total
A / azul            | Fardo x10    | 3        | ...    | ...
Contenido: 30 unidades. Precio por fardo: mayoreo.
[Seguir comprando]
Entrega: [Retiro] [Domicilio]
Contacto: nombre / teléfono verificable
Dirección y cotización (solo domicilio)
Productos ...   Envío ...   Total ...
Aviso: los productos se reservan al enviar, no en la carretilla.
[Enviar pedido y reservar]
```

Después de enviar: número, sucursal, resumen, vencimiento calculado por servidor, instrucciones de transferencia/efectivo y [Reportar pago]. Tras reporte vigente, reemplazar la cuenta atrás por “Pago en revisión: tus productos siguen reservados”; no insinuar que venció o está pagado.

Excepciones:

- Sin stock: marcar líneas afectadas, conservar resto del borrador; no crear pedido reservado a medias sin autorización.
- Precio cambió antes de enviar: mostrar diferencia y solicitar nueva aceptación (D2).
- Ruta no disponible: “No pudimos calcular el envío”; permitir corregir dirección/retiro o revisión autorizada, nunca envío cero inventado.
- Vencido: “Reserva liberada” + [Volver a pedir]; crea borrador nuevo con validación.
- Acceso privado inválido: no mostrar datos; ofrecer recuperar acceso por el mecanismo D3.

Revisión de escritorio A: cliente añade 3 fardos x10, otra persona compra antes de enviar y solo quedan 20. Resultado especificado: no reservar 30; conservar carretilla y explicar faltante. La ficha no promete reserva anticipada.

## UX02. Venta presencial y entrega directa

Actor: vendedor autorizado. RN04, RN14, RN22-RN24.

Secuencia: elegir sucursal/ubicación real → buscar/escanear → cantidad/presentación → validar → registrar cobro/referencia → entregar/registrar salida → vincular factura externa.

```text
VENTA PRESENCIAL   Sucursal: ...   Entrega desde: SALA ...
Buscar SKU/código [________________] [Agregar]
Producto | Presentación | Cantidad | Precio aplicado | Importe
Disponible en sala: ...       Total: ...
Cobro/referencia: [________]
[Revisar venta]
Resumen de cantidades y salida [Confirmar entrega y salida]
Orden de venta ...   [Vincular factura externa]
```

No mostrar costos. No descontar al agregar líneas. La confirmación registra venta y salida coherentes; si falla, no dejar venta entregada sin movimiento. La vinculación fiscal registra referencia, no otra venta ni factura emitida por Los Cheles.

Excepciones: falta en sala → no tomar bodega silenciosamente; indicar reposición o ubicación real autorizada. Doble clic → una sola operación. Error de referencia fiscal → permitir corregir con historial sin revertir físicamente mercancía ya entregada.

Revisión de escritorio B: sala 8 unidades, venta 3 → sala 5. Factura vinculada después → sala sigue 5. Devolución real de 1 dañada → físico 6, de las cuales 1 bloqueada; baja autorizada → físico 5. No ofrecer la dañada.

## UX03. Preparar, reempacar y despachar

Actor: bodega de sucursal; responsable comercial resuelve faltantes/pago. RN08, RN11-RN17.

Secuencia: cola de pedidos habilitados → tomar hoja → extraer por lote/ubicación → registrar reempaque → resolver faltantes → cerrar preparación → orden comercial → despacho → entrega.

```text
PREPARACIÓN  Pedido ...  Sucursal ...  Responsable ...
Estado: listo para preparar (sin información monetaria)
Producto / foto / variante
Solicitado: 1 fardo x12   Total a preparar: 12 unidades
Ubicación | Lote | Extraer | Extraído
A-01-02   | L-A  | 5       | [5]
B-02-01   | L-B  | 7       | [7]
Paquete resultante: [Código]
[Registrar extracción]  [Informar faltante]
Progreso: 12 / 12
[Finalizar preparación]
```

Bodega no recibe importes en pantalla, impresión ni datos ocultos. Orden de venta/prefactura con precios accesible en vista comercial separada. Despacho exige permiso y cantidades preparadas; puede corresponder a otro operador, no se asume que todo preparador puede despachar.

Excepciones: faltante → pausa y ticket a comercial, sin sustitución automática. Escaneo de variante equivocada → rechazar. Cancelación tras extracción → revisión y movimiento real de retorno, no liberar stock ubicado ficticiamente.

Revisión de escritorio C: lote antiguo 5 + siguiente 7 = paquete 12; conservar dos componentes. Extraer conserva físico de sede. Despachar 12 reduce físico una vez. Volver a imprimir hoja o marcar entregado no modifica saldos.

## UX04. Traslado central → sucursal

Actores: preparador origen, autorizador, receptor destino. RN18-RN23.

Secuencia: solicitar/autorizar → preparar bultos → confirmar salida → tránsito → contar recepción → resolver diferencias → cerrar.

```text
TRASLADO ...   CENTRAL → SUCURSAL ...
Estado: preparado
Bulto | Producto/variante | Lote | Fardos/paquetes | Unidades base
C-101 | A/azul           | L-A  | 5 x10           | 50
C-102 | A/azul           | L-A  | 5 x10           | 50
Salida: responsable + fecha/hora registrada por servidor
[Confirmar salida: 2 bultos / 100 unidades]

RECEPCIÓN (destino)
Bulto | Enviado | Apto recibido | Dañado recibido | Pendiente
C-101 | 50      | [50]          | [0]             | 0
C-102 | 50      | [48]          | [0]             | 2
[Registrar recepción parcial]  [Documentar diferencia]
```

No autocompletar “recibido” como verdad por escanear la caja; el contenido necesita confirmación según procedimiento. Hora de recepción separada de hora de salida. Receptor no modifica el registro original de despacho.

Revisión de escritorio D: central F=200; despacho100 → central100/tránsito100. Recibir98 → sucursal98/tránsito2. Consolidado físico+tránsito sigue200 hasta resolución de diferencia. Repetir recepción no suma otros98.

## UX05. Bandeja de revisión y cambios

Complemento transversal, no quinto flujo de lanzamiento independiente.

Columnas: pedido, sucursal, tiempo en revisión, responsable, estado de evidencia y siguiente acción. Importe solo a verificador autorizado. Filtros por antigüedad/responsable/sucursal, sin liberación por alerta.

Acciones separadas: revisar evidencia, verificar total, rechazar evidencia, liberar reserva con motivo, escalar incidencia. Rechazar no pulsa implícitamente liberar. Cambios muestran antes/después de cantidades, sucursal, entrega, total y disponibilidad; requieren revisión D7.

## Validación posterior con usuarios

Tareas: enviar invitado; identificar si precio es por fardo; corregir falta de stock; completar venta por teclado; extraer sin confundir lote; recibir con diferencia. Observar errores, ayuda requerida y tiempo. No se asignan metas ni se reportan pruebas de usabilidad realizadas en esta entrega documental.
