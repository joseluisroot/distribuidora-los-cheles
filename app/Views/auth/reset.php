<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="max-w-md mx-auto card">
    <h1 class="text-2xl font-bold mb-4">Nueva contraseña</h1>
    <form method="post" action="<?= site_url('reset') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= esc($token) ?>">
        <div>
            <label class="block text-sm mb-1">Contraseña</label>
            <input name="password" type="password" class="input" required minlength="8">
        </div>
        <div>
            <label class="block text-sm mb-1">Confirmar contraseña</label>
            <input name="password_confirm" type="password" class="input" required minlength="8">
        </div>
        <button class="btn-primary">Actualizar</button>
    </form>
</div>
<?= $this->endSection() ?>
