<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Hola, <?= esc($user['name']) ?></h1>
        <a href="<?= site_url('logout') ?>" class="text-red-600 hover:underline">Cerrar sesión</a>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="card">
            <h2 class="text-lg font-semibold mb-2">Menú</h2>
            <ul class="list-disc pl-5 space-y-1">
                <?php if ($user['role'] === 'admin'): ?>
                    <li><a class="text-blue-600 hover:underline" href="#">Productos</a></li>
                    <li><a class="text-blue-600 hover:underline" href="#">Inventarios</a></li>
                    <li><a class="text-blue-600 hover:underline" href="#">Pedidos</a></li>
                    <li><a class="text-blue-600 hover:underline" href="#">Mantenimientos</a></li>
                <?php else: ?>
                    <li><a class="text-blue-600 hover:underline" href="#">Catálogo</a></li>
                    <li><a class="text-blue-600 hover:underline" href="#">Mi Carretilla</a></li>
                    <li><a class="text-blue-600 hover:underline" href="#">Mis Pedidos</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="card">
            <h2 class="text-lg font-semibold mb-2">Estado</h2>
            <p class="text-gray-600">Aquí mostraremos KPIs básicos (próximo paso).</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
