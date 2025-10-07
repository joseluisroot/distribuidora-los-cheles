<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/navbar') ?>

<h1 class="text-2xl font-bold mb-4">Catálogo</h1>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach($productos as $p): ?>
        <div class="card">
            <div class="font-semibold"><?= esc($p['nombre']) ?></div>
            <div class="text-sm text-slate-500"><?= esc($p['sku']) ?></div>
            <div class="mt-2">
                <span class="text-lg font-bold">$<?= number_format($p['precio_base'],2) ?></span>
                <span class="text-xs ml-2 text-slate-500">Stock: <?= (int)($p['stock'] ?? 0) ?></span>
            </div>
            <div class="mt-3 flex gap-2">
                <a class="btn btn-outline" href="<?= site_url('demo/agregar-item?pedido=1&producto='.$p['id'].'&cant=1') ?>">+1</a>
                <a class="btn btn-outline" href="<?= site_url('demo/agregar-item?pedido=1&producto='.$p['id'].'&cant=3') ?>">+3 (escala)</a>
                <a class="btn btn-outline" href="<?= site_url('demo/agregar-item?pedido=1&producto='.$p['id'].'&cant=10') ?>">+10 (mayorista)</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
