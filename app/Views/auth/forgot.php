<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="max-w-md mx-auto card">
    <h1 class="text-2xl font-bold mb-4">Recuperar contraseña</h1>
    <form method="post" action="<?= site_url('forgot') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm mb-1">Correo</label>
            <input name="email" type="email" class="input" required>
        </div>
        <button class="btn-primary">Enviar instrucciones</button>
    </form>
</div>
<?= $this->endSection() ?>
