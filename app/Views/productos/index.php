<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-bold mb-4">Productos</h1>

<a href="<?= site_url('productos/crear') ?>" class="btn btn-primary mb-4">+ Nuevo</a>

<div class="card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
        <tr>
            <th class="p-3 text-left">SKU</th>
            <th class="p-3 text-left">Nombre</th>
            <th class="p-3 text-right">Precio</th>
            <th class="p-3 text-right">Stock</th>
            <th class="p-3 text-center">Activo</th>
            <th class="p-3 text-right">Acciones</th>
        </tr>
        </thead>
        <tbody class="divide-y">
        <?php foreach($productos as $p): ?>
            <tr class="hover:bg-slate-50">
                <td class="p-3"><?= esc($p['sku']) ?></td>
                <td class="p-3"><?= esc($p['nombre']) ?></td>
                <td class="p-3 text-right">$<?= number_format($p['precio_base'],2) ?></td>
                <td class="p-3 text-right"><?= $p['stock'] ?? 0 ?></td>
                <td class="p-3 text-center"><?= $p['is_activo'] ? '✅' : '❌' ?></td>
                <td class="p-3 text-right space-x-2">
                    <a class="btn btn-outline" href="<?= site_url('productos/editar/'.$p['id']) ?>">Editar</a>
                    <a class="btn btn-outline" href="<?= site_url('productos/escalas/'.$p['id']) ?>">Escalas</a>
                    <a class="btn btn-outline text-red-600" href="<?= site_url('productos/eliminar/'.$p['id']) ?>">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
