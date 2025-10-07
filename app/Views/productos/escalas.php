<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/navbar') ?>

<h1 class="text-2xl font-bold mb-4">Escalas - <?= esc($producto['nombre']) ?></h1>

<form method="post" class="card flex gap-4 mb-6">
    <?= csrf_field() ?>
    <input type="number" name="min_cantidad" placeholder="Mín cantidad" class="input" required>
    <input type="number" step="0.01" name="precio" placeholder="Precio" class="input" required>
    <button class="btn btn-primary">Agregar</button>
</form>

<div class="card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
        <tr>
            <th class="p-3 text-left">Mín Cantidad</th>
            <th class="p-3 text-left">Precio</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($escalas as $e): ?>
            <tr class="divide-y">
                <td class="p-3"><?= $e['min_cantidad'] ?></td>
                <td class="p-3">$<?= number_format($e['precio'],2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
