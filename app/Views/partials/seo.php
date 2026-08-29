<?php
helper('seo');
$metadata = seo_metadata($title ?? 'Distribuidora Los Cheles', $seo ?? []);
?>
<title><?= esc($metadata['title']) ?></title>
<?= view('partials/favicon') ?>
<meta name="robots" content="<?= esc($metadata['robots'], 'attr') ?>" />
<?php if ($metadata['public']): ?>
<meta name="description" content="<?= esc($metadata['description'], 'attr') ?>" />
<link rel="canonical" href="<?= esc($metadata['url'], 'attr') ?>" />
<meta property="og:type" content="website" />
<meta property="og:site_name" content="Distribuidora Los Cheles" />
<meta property="og:locale" content="es_SV" />
<meta property="og:title" content="<?= esc($metadata['title'], 'attr') ?>" />
<meta property="og:description" content="<?= esc($metadata['description'], 'attr') ?>" />
<meta property="og:url" content="<?= esc($metadata['url'], 'attr') ?>" />
<meta property="og:image" content="<?= esc($metadata['image'], 'attr') ?>" />
<meta property="og:image:alt" content="<?= esc($metadata['image_alt'], 'attr') ?>" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="<?= esc($metadata['title'], 'attr') ?>" />
<meta name="twitter:description" content="<?= esc($metadata['description'], 'attr') ?>" />
<meta name="twitter:image" content="<?= esc($metadata['image'], 'attr') ?>" />
<meta name="twitter:image:alt" content="<?= esc($metadata['image_alt'], 'attr') ?>" />
<?php endif; ?>
