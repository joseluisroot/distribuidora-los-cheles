<!doctype html>
<html lang="es">
<head>
    <?= view('partials/favicon') ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>No pudimos completar la solicitud | Distribuidora Los Cheles</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/error-pages.css') ?>">
</head>
<body>
<main class="error-shell" aria-labelledby="error-title">
    <section class="error-card">
        <header class="error-brand">
            <img src="<?= base_url('assets/Logo_LosCheles.PNG') ?>" alt="Distribuidora Los Cheles" width="138">
            <span aria-hidden="true">ERROR</span>
        </header>
        <div class="error-illustration error-illustration-warm" aria-hidden="true">
            <svg viewBox="0 0 64 64" width="58" height="58" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M32 8 57 52H7L32 8Z"/><path d="M32 23v14M32 44h.01"/>
            </svg>
        </div>
        <p class="error-eyebrow">NO PUDIMOS COMPLETAR LA SOLICITUD</p>
        <h1 id="error-title">Algo no salió como esperábamos</h1>
        <p class="error-copy">La operación no pudo completarse en este momento. Tus datos no deben enviarse nuevamente hasta comprobar el resultado.</p>
        <nav class="error-actions" aria-label="Opciones para continuar">
            <a class="error-primary" href="<?= site_url('dashboard') ?>">Volver a mi panel</a>
            <a class="error-secondary" href="<?= site_url('catalogo') ?>">Ir al catálogo</a>
        </nav>
        <p class="error-help">Si el problema continúa, comunícate con el administrador e indica qué acción estabas realizando.</p>
    </section>
</main>
</body>
</html>
