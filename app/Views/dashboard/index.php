<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Hola, <?= esc($user['name']) ?></h1>
        <form method="post" action="<?= site_url('logout') ?>"><?= csrf_field() ?><button type="submit"  class="text-red-600 hover:underline">Cerrar sesión</button></form>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="card">
            <h2 class="text-lg font-semibold mb-2">Menú</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li><a href="<?= site_url('catalogo') ?>">Catálogo</a></li>
                <li><a href="<?= site_url('carretilla') ?>">Mi Carretilla</a></li>
                <li><a href="<?= site_url('pedidos') ?>">Mis Pedidos</a></li>
                <?php if ($canManageProducts): ?>
                    <li><a href="<?= site_url('productos') ?>">Productos</a></li>
                <?php endif; ?>
                <?php if ($canManageAccess): ?>
                    <li><a href="<?= site_url('admin/accesos') ?>">Permisos y auditoría</a></li>
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
