<!doctype html>
<html lang="es">
<head>
    <?= view('partials/favicon') ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Acceso restringido | Distribuidora Los Cheles</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/access-denied.css') ?>">
</head>
<body>
<main class="denied-card" aria-labelledby="denied-title">
    <img class="denied-logo" src="<?= base_url('assets/Logo_LosCheles.PNG') ?>" alt="Distribuidora Los Cheles" width="130">
    <div class="denied-icon" aria-hidden="true">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></svg>
    </div>
    <p class="denied-eyebrow">ACCESO RESTRINGIDO · 403</p>
    <h1 id="denied-title">Esta sección requiere permiso</h1>
    <p>Tu cuenta no tiene autorización para acceder a esta sección o realizar esta acción.</p>
    <aside>Si necesitas acceso para tu trabajo, solicita al administrador que revise los permisos de tu cuenta.</aside>
    <nav aria-label="Opciones para continuar">
        <a class="denied-primary" href="<?= site_url('dashboard') ?>">Volver a mi panel</a>
        <a class="denied-secondary" href="<?= site_url('catalogo') ?>">Ir al catálogo</a>
    </nav>
    <p class="denied-note">No es necesario crear otra cuenta.</p>
</main>
</body>
</html>
