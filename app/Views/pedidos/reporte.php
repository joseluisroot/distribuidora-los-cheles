<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-bold mb-4">Reporte de Pedidos</h1>

<!-- Filtros -->
<form method="get" class="card grid sm:grid-cols-4 gap-3 mb-4">
    <div>
        <label class="block text-sm mb-1 text-slate-600">Desde</label>
        <input type="date" name="from" value="<?= esc($from) ?>" class="input">
    </div>
    <div>
        <label class="block text-sm mb-1 text-slate-600">Hasta</label>
        <input type="date" name="to" value="<?= esc($to) ?>" class="input">
    </div>
    <div>
        <label class="block text-sm mb-1 text-slate-600">Estado</label>
        <select name="estado" class="input">
            <option value="">Todos</option>
            <option value="ingresado"  <?= $estado==='ingresado'?'selected':'' ?>>Ingresado</option>
            <option value="preparando" <?= $estado==='preparando'?'selected':'' ?>>Preparando</option>
            <option value="procesado"  <?= $estado==='procesado'?'selected':'' ?>>Procesado</option>
        </select>
    </div>
    <div class="flex items-end gap-2">
        <button class="btn btn-outline">Aplicar</button>
        <a href="<?= site_url('pedidos/reporte') ?>" class="btn btn-outline">Limpiar</a>
    </div>
</form>

<!-- Widgets Totales -->
<div class="grid sm:grid-cols-3 gap-4 mb-4">
    <div class="card">
        <div class="text-sm text-slate-500">Pedidos</div>
        <div class="text-2xl font-extrabold"><?= (int)$cantidad ?></div>
    </div>
    <div class="card">
        <div class="text-sm text-slate-500">Importe total</div>
        <div class="text-2xl font-extrabold">$<?= number_format($importe,2) ?></div>
    </div>
    <div class="card">
        <div class="text-sm text-slate-500">Estado</div>
        <div class="text-2xl font-extrabold"><?= $estado ? ucfirst($estado) : 'Todos' ?></div>
    </div>
</div>

<!-- Exportar -->
<?php
$qs = http_build_query(array_filter(['from'=>$from,'to'=>$to,'estado'=>$estado]));
$exportUrl = site_url('pedidos/reporte/export'.($qs?('?'.$qs):''));
?>
<div class="mb-4 flex justify-end">
    <a class="btn btn-outline" href="<?= $exportUrl ?>">Exportar CSV</a>
</div>

<!-- Tabla -->
<div class="card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
        <tr>
            <th class="p-3 text-left">#</th>
            <th class="p-3 text-left">Fecha</th>
            <th class="p-3 text-left">Cliente</th>
            <th class="p-3 text-left">Estado</th>
            <th class="p-3 text-right">Total</th>
            <th class="p-3 text-right">Acciones</th>
        </tr>
        </thead>
        <tbody class="divide-y">
        <?php if (!$pedidos): ?>
            <tr><td class="p-3" colspan="6">Sin resultados para el filtro.</td></tr>
        <?php else: foreach($pedidos as $p): ?>
            <tr class="hover:bg-slate-50">
                <td class="p-3"><?= $p['id'] ?></td>
                <td class="p-3"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                <td class="p-3"><?= esc($p['cliente']) ?></td>
                <td class="p-3">
                    <?php if($p['estado']==='ingresado'): ?>
                        <span class="badge" style="background:#dbeafe;color:#1e40af">Ingresado</span>
                    <?php elseif($p['estado']==='preparando'): ?>
                        <span class="badge" style="background:#fef3c7;color:#92400e">Preparando</span>
                    <?php else: ?>
                        <span class="badge" style="background:#dcfce7;color:#166534">Procesado</span>
                    <?php endif; ?>
                </td>
                <td class="p-3 text-right">$<?= number_format($p['total'],2) ?></td>
                <td class="p-3 text-right">
                    <a class="btn btn-outline" href="<?= site_url('pedidos/'.$p['id']) ?>">Ver</a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
