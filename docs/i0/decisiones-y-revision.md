# Decisiones y revisión I0

Estado: propuesta lista para revisión. Responsables son funciones sugeridas; el usuario debe asignar personas. No exigir una respuesta a todo antes de I1.

| ID | Decisión abierta | Responsable propuesto | Cierre | Criterio de respuesta |
|---|---|---|---|---|
| D1 | Mezcla de fardos/unidades | Dirección comercial | I2 | Aprobar precio separado o regla exacta con ejemplos |
| D2 | Fijación de precio/envío | Dirección comercial/operaciones | I2/I5 | Momento, vigencia y tratamiento de cambios posteriores |
| D3 | Verificación invitado | Operaciones y TI | I5 | Canal, recuperación, costos y proveedor autorizado |
| D4 | Tarifas/alcance/transporte | Mando medio autorizado | I5 | Rangos, distancias, gratuidad, proveedor y fallo de cotización |
| D5 | Movimientos durante conteo | Supervisor de inventario | I4 | Bloqueo por zona o corte; frecuencia, reintentos y conciliación |
| D6 | Permisos rol/usuario | Superior que administra accesos | I1 | Precedencia de denegación, ámbito, delegación y revocación |
| D7 | Cambios de pedidos | Superior comercial/operación | I5/I6 | Quién solicita/aprueba y límites según fase |
| D8 | Dinero tardío/reembolsos | Superior financiero | I4/I6 | Autorización, evidencia, diferencias y conciliación |
| D9 | Factura externa | Responsable de facturación | I4 | Campos/cardinalidad, trámite correctivo y nombre de impresión |
| D10 | Revisión/entrega | Encargado de sucursal | I6 | Umbrales, responsables, canales y evidencia de entregado |
| D11 | Datos iniciales/bultos | Supervisor y responsable de costos | I2 | Archivo disponible, desconocidos, contenido mixto e identidad |

No confundir D1 con umbral: 3 unidades y 3 fardos ya están aprobados. D6 no reabre la prohibición general de costos para vendedor/bodega ni convierte cualquier superior en administrador sin límites.

## Propuesta concreta D6 para revisar primero

Permisos base por rol + excepciones individuales con alcance de sucursal. Denegación explícita prevalece; pantalla muestra origen del permiso. Solo el superior designado como administrador concede/revoca dentro de sus facultades, sin autoescalamiento. Retirar del rol advierte si quedan concesiones individuales. Ningún permiso de costos se deriva automáticamente de administrar accesos.

## Lista de revisión del incremento

| Entregable | Estado en esta versión |
|---|---|
| Glosario, alcance y 28 reglas | Preparado |
| Estados de pedido/pago/reserva/preparación/traslado | Preparados como propuesta de diseño |
| Matriz de responsabilidades y datos | Preparada; D6 por aprobar |
| Modelo conceptual y contratos transaccionales | Preparados; no SQL implementado |
| Cuatro recorridos con bocetos | Preparados en texto, no prototipo interactivo |
| 36 escenarios de aceptación | Especificados; no ejecutados como tests de producto |
| Inspección de código y plan de migraciones/pruebas | Preparados |
| Revisión de consistencia documental | Realizada; ver README |
| Validación de recorridos por usuario | Pendiente |
| Aceptación I0 | Pendiente; no declarar completado solo por escribir documentos |

## Revisión guiada sugerida

Recorrer UX01-UX04 con cliente/vendedor/bodega/encargado: comprobar información necesaria, responsables y errores. Registrar cambios sobre esta versión. Después validar D6 para entrar a I1. D1-D5 y D7-D11 tienen etapas de cierre específicas; no paralizan por sí solas el trabajo de seguridad.
