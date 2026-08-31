# I2.2 — Productos y presentaciones comerciales

El esquema heredado usa una fila de `productos` por SKU vendible. Durante I2 se conserva como variante comercial para no romper catálogo, imágenes, pedidos e inventario histórico. La presentación se modela aparte.

Cada presentación define código, nombre, equivalencia en unidades base, precio detalle y precio mayoreo. El mayoreo se activa desde 3 elementos de la misma presentación: 3 unidades usan precio mayorista unitario y 3 fardos usan precio mayorista de fardo. Una unidad y dos fardos no se combinan para alcanzar el mínimo.

Ejemplo: UNIDAD equivale a 1 unidad base; FARDO-10 equivale a 10. Comprar un solo FARDO-10 mueve 10 unidades base, pero no activa mayoreo de fardo. No se generan presentaciones automáticamente ni se transforma `precio_base` sin revisión.

Los cambios son auditados y el precio mayorista no puede superar al detalle. Existencias, apertura de fardos, lotes y costos pertenecen a los siguientes cortes de I2.
