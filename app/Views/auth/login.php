<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="max-w-md mx-auto bg-white shadow-lg rounded-2xl p-6">
    <h1 class="text-2xl font-bold mb-4 text-gray-800">Ingresar</h1>
    <form method="post" action="<?= site_url('login') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm mb-1 text-gray-700">Correo</label>
            <input name="email" type="email" class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-300" required>
        </div>
        <div>
            <label class="block text-sm mb-1 text-gray-700">Contraseña</label>
            <input name="password" type="password" class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-300" required>
        </div>
        <div class="flex items-center justify-between">
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Entrar</button>
            <a href="<?= site_url('forgot') ?>" class="text-sm text-blue-600 hover:underline">¿Olvidaste tu contraseña?</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
