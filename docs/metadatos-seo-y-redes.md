# Metadatos SEO y vista previa de enlaces

## Implementación

Logo institucional indicado por el usuario: https://www.distribuidoraloscheles.com/assets/Logo_LosCheles.PNG . El proyecto ya tiene `public/assets/Logo_LosCheles.PNG` y lo usa como imagen predeterminada de metadatos. No se pudo verificar el recurso remoto con la herramienta de consulta, por lo que no se afirma que ambos archivos sean idénticos. Mantener la foto específica en enlaces de productos; usar el logo para páginas generales y como respaldo.

La plantilla principal incluye `partials/seo` dentro de `<head>`. Las etiquetas se generan en el servidor; no requieren ejecutar JavaScript ni instalar la PWA.

- Catálogo y políticas: título, descripción, canonical, robots, Open Graph y tarjeta de imagen grande para Twitter/X.
- Producto: nombre, descripción sin HTML, URL por slug e imagen principal completa de su galería. Si no hay galería, se utiliza imagen_url; si no hay imagen, se usa el logo existente.
- Las imágenes locales se convierten en URLs absolutas; las URLs HTTP(S) externas se conservan.
- Las páginas internas que usan la plantilla no se habilitan para indexación por defecto y no generan tarjetas sociales. `noindex` no sustituye el control de acceso.
- La portada y el catálogo comparten canonical de catálogo. Se conservan los parámetros de paginación/tamaño de página y se omiten parámetros de campaña. Búsquedas y ordenamientos alternativos llevan noindex.
- No se agregaron píxeles, rastreadores, datos privados ni precios a las tarjetas.

Para agregar otra página pública, su controlador debe pasar `seo` con `public => true`, `url` canónica y, opcionalmente, `title`, `description`, `image` e `image_alt`.

## Publicación y comprobación

Canales prioritarios confirmados: compartir enlaces en chats de WhatsApp y publicaciones del muro de Facebook. La aceptación final requiere comprobar un enlace público de producto en ambos: fotografía del producto, nombre, descripción cuando la plataforma la muestre y apertura de la ficha correcta. También comprobar la portada con la imagen institucional. Las pruebas locales de metadatos no equivalen a esta validación externa.

1. En producción, configurar `app.baseURL` con `https://distribuidoraloscheles.com/`. La configuración local no se cambió ni se realizó despliegue.
2. La página y la imagen deben ser accesibles públicamente por HTTPS, sin login, bloqueos al rastreador ni URLs temporales vencidas.
3. Verificar el HTML publicado y la URL de imagen de un producto real. La base local todavía no tiene productos cargados; las pruebas automatizadas usan datos de ejemplo sin insertarlos.
4. Comprobar el enlace en las herramientas de depuración de la red elegida; las redes pueden conservar vistas previas antiguas y tardar en volver a leerlas.
5. Compartir el enlace directo del producto, no solamente la portada: la foto se asocia a la URL de su ficha.

Las etiquetas facilitan indexación y previsualización, pero no garantizan aparecer en Google, aprobación de anuncios ni que todas las aplicaciones muestren una imagen. La fotografía, accesibilidad y caché del servicio también influyen. El diseño de una imagen institucional específica para compartir queda fuera de este cambio; se reutiliza el logo.

## Referencias

- Open Graph: https://ogp.me/
- Metadatos admitidos por Google: https://developers.google.com/search/docs/crawling-indexing/special-tags
