# Escenarios de aceptación I0

Versión 0.1. Casos de especificación revisados en escritorio, **no ejecutados contra la aplicación**. C = regla confirmada; P = diseño pendiente; cerrar decisión antes de convertirla en test normativo.

| ID | Regla | Dado / Cuando | Resultado esperado | Prueba futura |
|---|---|---|---|---|
| CA01 | RN04 C | Misma variante, 2 unidades → 3 | Precio normal → mayorista unitario | Unitario I2 |
| CA02 | RN04 C | 2 fardos → 3 fardos x10 | Precio normal → mayorista por fardo; stock30 | Unitario I2 |
| CA03 | RN04 C | 1 fardo x10 | Precio normal de fardo, no mayorista por contener10 | Unitario I2 |
| CA04 | RN04 C | 2 unidades A y 1 B | No sumar para mayoreo | Unitario I2 |
| CA05 | RN05 P/D1 | 1 fardo + 2 unidades de A | Precios separados si D1 se aprueba | Unitario I2 |
| CA06 | RN06 C | Producto en carretilla; otro cliente envía primero | Sin prioridad por carretilla; revalidar al enviar | Integración I5 |
| CA07 | RN06 C | Dos solicitudes simultáneas por última unidad | Solo una reserva aceptada | MySQL concurrente I5 |
| CA08 | RN03 C | Sala10, bodega0, web pide1 | No reservar sala | Integración I5 |
| CA09 | RN03 C | Traslado real sala→bodega de5 | Sala5/bodega5, web puede reservar de bodega | Integración I3/I5 |
| CA10 | RN07 C | Reporte antes de vencer; revisión tarda más de una hora | Reserva mantenida, alerta no libera | Reloj controlado I5/I6 |
| CA11 | RN09 C | Vencimiento sin reporte | Reserva liberada una vez; copia crea nuevo ID | Integración I5 |
| CA12 | RN07 P | Reporte y vencimiento compiten | Estado único consistente; reporte tardío va a incidencia | MySQL concurrente I5 |
| CA13 | RN08 C | Total100; transferencias verificadas40 y60 | Preparar solo al cubrir100; dos evidencias conservadas | Integración I6 |
| CA14 | RN10 C | Liberación manual por vendedor permitido | Disponible vuelve, historial/motivo; no reembolso implícito | Permisos I6 |
| CA15 | RN11 C | Cambiar cantidad de pagado sin aprobación | No modificar silenciosamente | Integración I6 |
| CA16 | RN11 P/D7 | Cambio autorizado falla por stock | Estado/reserva original coherentes, sin modificación parcial | Integración I6 |
| CA17 | RN12 C | Reempaque5 de loteA y7 de loteB | 12 unidades, dos orígenes/costos históricos | Integración I6 |
| CA18 | RN13 C | Extraer30, despachar30, marcar entregado | Extracción no reduce físico sede; despacho -30 una vez | Integración I6 |
| CA19 | RN14/RN24 C | Sala8, venta3, luego factura referenciada | Sala5 después de ambas acciones | Integración I4 |
| CA20 | RN18/RN19 P | Enviar100, recibir98 | Tránsito2 pendiente; no forzar100 recibido | Integración I3 |
| CA21 | RN16/RN17 P | Recibir100 con2 dañadas | 98 aptas y2 bloqueadas, sin faltante ficticio | Integración I3/I4 |
| CA22 | RN16 C/P | Devolución física dañada1 | Ingreso trazable bloqueado; baja separada autorizada | Integración I4 |
| CA23 | RN15 C | Faltante pagado, cliente no ha respondido | No cerrar como completo; autorización/reembolso pendientes | Flujo I6 |
| CA24 | RN20 C | Bodega detecta diferencia de conteo | Captura no modifica stock; aprobación superior | Permisos I4 |
| CA25 | RN20 P/D5 | Movimiento durante zona bloqueada | Rechazo controlado o conciliación si se elige corte | Integración I4 |
| CA26 | RN21 P | Repetir carga inicial | No duplicar ingreso; filas explicables | Integración I2 |
| CA27 | RN22 C | Bodega consulta HTML/API/impresión/export | Sin precios ni costos, no solo columnas ocultas | Permisos I1+ |
| CA28 | RN23 C | Vendedor cambia precio o tarifa por petición directa | Denegado aun si conoce URL | Permisos I1+ |
| CA29 | RN23 P/D6 | Rol permite, usuario deniega | Denegación efectiva si se aprueba precedencia | Permisos I1 |
| CA30 | RN01 C | Invitado conoce número ajeno sin acceso privado | No obtiene pedido ajeno | Seguridad I5 |
| CA31 | RN25 P/D4 | Sin ruta para destino | No asignar cero; corregir o revisión autorizada | Integración I5 |
| CA32 | RN27 P/D2 | Cambia tarifa después de aceptar | Pedido conserva precio aceptado | Integración I5 |
| CA33 | RN28 C/P | Transferencia llega tras vencimiento | No revivir reserva; superior concilia, nuevo pedido revalida | Integración I6 |
| CA34 | RN24 P/D9 | Registrar corrección externa | Conservar referencia/estado sin afirmar trámite automático | Integración I4 |
| CA35 | RN11/RN13 C | Reimprimir orden y reintentar despacho | Una salida; impresión no mueve stock | Integración I6 |
| CA36 | RN17 P | Reembolso por faltante que nunca salió | No ingreso ficticio de mercancía | Integración I4/I6 |

## Comprobaciones de escritorio realizadas

Se contrastaron los cuatro recorridos de recorridos-y-bocetos.md con estas reglas. Los ejemplos aritméticos preservan cantidades: reserva30 sobre100 mantiene disponible70 al verificar/despachar; traslado100 con recepción98 deja2 pendientes; reempaque5+7 produce12 sin nueva entrada.

No se ha probado concurrencia real, entrega de mensajes, cálculo de rutas ni flujo de negocio en ejecución. No usar esta revisión como autorización de producción.
