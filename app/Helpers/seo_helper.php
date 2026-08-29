<?php

/** Text for metadata, without HTML or line breaks. Escape at rendering time. */
function seo_text(string $text, int $limit = 160): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strip_tags($text);
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

    return mb_strimwidth($text, 0, $limit, '…', 'UTF-8');
}

/** Only HTTP(S) images; local asset paths are resolved using app.baseURL. */
function seo_image_url(?string $path): string
{
    $path = trim($path ?? '');
    if (preg_match('~^https?://~i', $path) && filter_var($path, FILTER_VALIDATE_URL)) {
        return $path;
    }

    if ($path !== '' && !preg_match('~[:\\\\?#\x00-\x20]|^//|(?:^|/)\.\.(?:/|$)~', $path)) {
        return base_url(ltrim($path, '/'));
    }

    return base_url('assets/Logo_LosCheles.PNG');
}

/** Public product metadata. Never includes costs, user data or query parameters. */
function seo_product(array $product, ?array $primaryImage = null): array
{
    $name = seo_text((string) $product['nombre']);
    $description = seo_text((string) ($product['descripcion'] ?? ''));

    return [
        'public' => true,
        'title' => $name . ' | Distribuidora Los Cheles',
        'description' => $description ?: 'Consulta ' . $name . ' en Distribuidora Los Cheles. Venta al por mayor y al detalle.',
        'url' => site_url('catalogo/' . rawurlencode(($product['slug'] ?? '') ?: $product['sku'])),
        // Full-sized primary image, not the 160px thumbnail.
        'image' => $primaryImage['path'] ?? $product['imagen_url'] ?? null,
        'image_alt' => $primaryImage['alt'] ?? $name,
    ];
}

function seo_metadata(string $title, array $options = []): array
{
    $public = ($options['public'] ?? false) === true;

    return [
        'title' => seo_text((string) ($options['title'] ?? $title), 200),
        'public' => $public,
        'description' => seo_text((string) ($options['description'] ?? 'Distribuidora Los Cheles: artículos al por mayor y al detalle. Consulta nuestro catálogo y prepara tu pedido.')),
        'robots' => $public && ($options['index'] ?? true) ? 'index, follow, max-image-preview:large' : 'noindex, nofollow',
        'url' => $options['url'] ?? site_url('/'),
        'image' => seo_image_url($options['image'] ?? null),
        'image_alt' => seo_text((string) ($options['image_alt'] ?? 'Distribuidora Los Cheles')),
    ];
}
