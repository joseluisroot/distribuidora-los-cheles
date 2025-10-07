<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/navbar') ?>

<h1 class="text-2xl font-bold mb-4">Pedido #<?= $pedido['id'] ?></h1>

<div class="card mb-6">
    <p><b>Cliente:</b> <?= esc($pedido['cliente']) ?></p>
    <p><b>Estado:</b> <?= ucfirst($pedido['estado']) ?></p>
    <p><b>Total:</b> $<?= number_format($pedido['total'],2) ?></p>
    <p><b>Fecha:</b> <?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></p>
</div>

<h2 class="text-xl font-semibold mb-2">Detalles</h2>
<div class="card overflow-x-auto mb-6">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
        <tr>
            <th class="p-3 text-left">Producto</th>
            <th class="p-3 text-right">Cantidad</th>
            <th class="p-3 text-right">Precio Unit.</th>
            <th class="p-3 text-right">Subtotal</th>
        </tr>
        </thead>
        <tbody class="divide-y">
        <?php foreach($detalles as $d): ?>
            <tr>
                <td class="p-3"><?= esc($d['nombre']) ?></td>
                <td class="p-3 text-right"><?= $d['cantidad'] ?></td>
                <td class="p-3 text-right">$<?= number_format($d['precio_unit'],2) ?></td>
                <td class="p-3 text-right">$<?= number_format($d['subtotal'],2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $user = session('user'); ?>
<?php if($user && ($user['role'] ?? '') === 'admin'): ?>
    <h2 class="text-xl font-semibold mb-2">Cambiar estado</h2>
    <div class="card mb-6">
        <form method="post" action="<?= site_url('pedidos/cambiar-estado/'.$pedido['id']) ?>" class="grid md:grid-cols-4 gap-3">
            <?= csrf_field() ?>

            <div class="md:col-span-2">
                <label class="block text-sm mb-1 text-slate-600">Estado actual</label>
                <input class="input" value="<?= ucfirst($pedido['estado']) ?>" disabled>
            </div>

            <div>
                <label class="block text-sm mb-1 text-slate-600">Nuevo estado</label>
                <select name="estado_destino" class="input" required>
                    <option value="" disabled selected>Selecciona…</option>
                    <?php if($pedido['estado']==='ingresado'): ?>
                        <option value="preparando">Preparando</option>
                        <option value="procesado">Procesado (confirmar y procesar)</option>
                    <?php elseif($pedido['estado']==='preparando'): ?>
                        <option value="procesado">Procesado</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="md:col-span-4">
                <label class="block text-sm mb-1 text-slate-600">Nota (opcional)</label>
                <input name="nota" class="input" placeholder="Motivo / referencia de cambio">
            </div>

            <div class="md:col-span-4 flex justify-end">
                <button class="btn btn-primary">Aplicar cambio</button>
            </div>
        </form>
    </div>
<?php endif; ?>


<h2 class="text-xl font-semibold mb-2">Historial de Estados</h2>
<div class="card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
        <tr>
            <th class="p-3 text-left">Estado</th>
            <th class="p-3 text-left">Usuario</th>
            <th class="p-3 text-left">Nota</th>
            <th class="p-3 text-left">Fecha</th>
        </tr>
        </thead>
        <tbody class="divide-y">
        <?php foreach($historial as $h): ?>
            <tr>
                <td class="p-3"><?= ucfirst($h['estado']) ?></td>
                <td class="p-3"><?= esc($h['usuario']) ?></td>
                <td class="p-3"><?= esc($h['nota']) ?></td>
                <td class="p-3"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
