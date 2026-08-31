# Línea gráfica de Distribuidora Los Cheles

Esta guía es obligatoria para pantallas nuevas y para la renovación gradual de vistas heredadas.

## Principios

- Jerarquía inmediata: contexto, título, explicación breve y acción primaria.
- Azul institucional para orientación y acciones; verde para éxito; rojo solo para riesgo o error.
- Superficies blancas, bordes suaves, radios amplios y sombras discretas sobre fondo neutro.
- Una acción primaria visible por contexto; acciones secundarias compactas y claramente etiquetadas.
- Estados vacíos con explicación y siguiente acción, nunca tablas desiertas sin orientación.
- Formularios agrupados por tarea, con ayuda junto al campo y conservación de datos ante error.
- Información heredada o provisional debe identificarse explícitamente para evitar decisiones operativas incorrectas.
- Adaptación móvil, foco visible, etiquetas semánticas, contraste suficiente y respeto a movimiento reducido.

## Componentes base

`commerce-admin.css` aporta encabezados, métricas, tarjetas, tablas, estados, formularios, carga visual y acciones para módulos comerciales. `dashboard.css` mantiene el espacio personal. `access.css` cubre seguridad e inventario mientras sus componentes se incorporan gradualmente al sistema compartido.

SweetAlert se usa para confirmaciones y resultados importantes, siempre con alternativa nativa. Los mensajes del servidor deben escaparse y las operaciones de escritura conservan CSRF, permisos y validación del lado servidor.

## Criterio de aceptación visual

Cada pantalla se revisa en escritorio y móvil, con datos, sin datos, con validación fallida y con permisos limitados. La presentación visual no sustituye pruebas funcionales ni autorización.
