<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/register.css') ?>">
<main class="registration" aria-labelledby="registration-title">
    <a class="registration-back" href="<?= site_url('catalogo') ?>"><span aria-hidden="true">←</span> Volver al catálogo</a>
    <div class="registration-card">
        <header class="registration-header">
            <a href="<?= site_url('catalogo') ?>" aria-label="Ir al catálogo de Los Cheles">
                <img src="<?= base_url('assets/Logo_LosCheles.PNG') ?>" alt="Distribuidora Los Cheles" width="176" class="registration-logo">
            </a>
            <p class="registration-eyebrow">BIENVENIDO A LOS CHELES</p>
            <h1 id="registration-title">Crea tu cuenta</h1>
            <p>Completa tus datos para acceder a tu espacio en Los Cheles.</p>
        </header>
        <form method="post" action="<?= site_url('register') ?>" id="registration-form" class="registration-form">
            <?= csrf_field() ?>
            <div class="registration-field">
                <label for="register-name">Nombre completo</label>
                <input id="register-name" name="name" maxlength="100" autocomplete="name" placeholder="Tu nombre y apellido" required>
            </div>
            <div class="registration-field">
                <label for="register-email">Correo electrónico</label>
                <input id="register-email" type="email" name="email" maxlength="150" autocomplete="email" placeholder="nombre@correo.com" required>
            </div>
            <div class="registration-field">
                <label for="register-password">Contraseña</label>
                <div class="registration-password">
                    <input id="register-password" type="password" name="password" minlength="12" maxlength="200" autocomplete="new-password" aria-describedby="register-password-help" required>
                    <button type="button" class="registration-toggle" data-password-target="register-password" aria-controls="register-password" aria-pressed="false" hidden>Mostrar</button>
                </div>
                <p id="register-password-help" class="registration-help">Usa al menos 12 caracteres. Puedes utilizar una frase fácil de recordar.</p>
            </div>
            <div class="registration-field">
                <label for="register-confirm">Confirmar contraseña</label>
                <div class="registration-password">
                    <input id="register-confirm" type="password" name="password_confirm" minlength="12" maxlength="200" autocomplete="new-password" required>
                    <button type="button" class="registration-toggle" data-password-target="register-confirm" aria-controls="register-confirm" aria-pressed="false" hidden>Mostrar</button>
                </div>
            </div>
            <button type="submit" class="registration-submit">Crear mi cuenta <span aria-hidden="true">→</span></button>
            <p class="registration-status" role="status" id="registration-status"></p>
        </form>
        <footer class="registration-footer">
            <p>¿Ya tienes cuenta? <a href="<?= site_url('login') ?>">Inicia sesión</a></p>
            <a class="registration-terms" href="<?= site_url('politicas-terminos-y-condiciones') ?>">Consultar términos y condiciones</a>
        </footer>
    </div>
    <p class="registration-note">Tu contraseña es personal. No la compartas con nadie.</p>
</main>
<script src="<?= base_url('assets/js/register.js') ?>" defer></script>
<?= $this->endSection() ?>
