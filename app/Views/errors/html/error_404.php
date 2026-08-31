<!doctype html>
<html lang="es">
<head>
    <?= view('partials/favicon') ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Página no encontrada | Distribuidora Los Cheles</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/error-pages.css') ?>">
</head>
<body>
<main class="error-shell" aria-labelledby="error-title">
    <section class="error-card">
        <header class="error-brand">
            <img src="<?= base_url('assets/Logo_LosCheles.PNG') ?>" alt="Distribuidora Los Cheles" width="138">
            <span aria-hidden="true">404</span>
        </header>
        <div class="error-illustration" aria-hidden="true">
            <svg viewBox="0 0 64 64" width="58" height="58" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="28" cy="28" r="17"/><path d="m41 41 12 12M20 28h16M28 20v16"/>
            </svg>
        </div>
        <p class="error-eyebrow">PÁGINA NO ENCONTRADA · 404</p>
        <h1 id="error-title">No encontramos lo que buscabas</h1>
        <p class="error-copy">Es posible que el enlace haya cambiado, esté incompleto o que la página ya no esté disponible.</p>
        <nav class="error-actions" aria-label="Opciones para continuar">
            <a class="error-primary" href="<?= site_url('catalogo') ?>">Ir al catálogo</a>
            <a class="error-secondary" href="<?= site_url('dashboard') ?>">Volver a mi panel</a>
        </nav>
        <?php if (ENVIRONMENT !== 'production' && ! empty($message)): ?>
            <details class="error-diagnostic">
                <summary>Detalle para desarrollo</summary>
                <code><?= nl2br(esc($message)) ?></code>
            </details>
        <?php endif ?>
        <p class="error-help">Si llegaste aquí desde un enlace de la plataforma, puedes reportarlo al administrador.</p>
    </section>
</main>
</body>
</html>
