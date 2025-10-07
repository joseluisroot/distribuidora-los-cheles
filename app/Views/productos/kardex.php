<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<h1 class="text-2xl font-bold mb-2">Kardex - <?= esc($producto['nombre']) ?> <span class="text-sm text-slate-500">(<?= esc($producto['sku']) ?>)</span></h1>

<!-- Encabezado existente... -->

<!-- Widgets de Totales -->
<div class="grid sm:grid-cols-3 gap-4 mb-4">
    <div class="card">
        <div class="text-sm text-slate-500">Entradas</div>
        <div class="text-2xl font-extrabold"><?= (int)$totales['entradas'] ?></div>
    </div>
    <div class="card">
        <div class="text-sm text-slate-500">Salidas</div>
        <div class="text-2xl font-extrabold"><?= (int)$totales['salidas'] ?></div>
    </div>
    <div class="card">
        <div class="text-sm text-slate-500">Balance</div>
        <div class="text-2xl font-extrabold"><?= (int)$totales['balance'] ?></div>
    </div>
</div>

<!-- Botón Exportar CSV (respeta filtros) -->
<div class="mb-4 flex justify-end">
    <?php
    $qs = http_build_query(array_filter(['from'=>$from, 'to'=>$to]));
    $urlExport = site_url('productos/kardex/'.$producto['id'].'/export'.($qs ? ('?'.$qs) : ''));
    ?>
    <a class="btn btn-outline" href="<?= $urlExport ?>">Exportar CSV</a>
</div>


<div class="mb-4 flex items-center justify-between">
    <div class="card">
        <div class="text-sm text-slate-500">Stock actual</div>
        <div class="text-2xl font-extrabold"><?= (int)$stockActual ?></div>
    </div>

    <form method="get" class="card flex gap-2 items-end">
        <div>
            <label class="block text-sm mb-1 text-slate-600">Desde</label>
            <input type="date" name="from" value="<?= esc($from) ?>" class="input w-44">
        </div>
        <div>
            <label class="block text-sm mb-1 text-slate-600">Hasta</label>
            <input type="date" name="to" value="<?= esc($to) ?>" class="input w-44">
        </div>
        <button class="btn btn-outline">Filtrar</button>
        <a href="<?= site_url('productos/kardex/'.$producto['id']) ?>" class="btn btn-outline">Limpiar</a>
    </form>
</div>

<!-- Alta manual de movimiento (solo admin; este módulo ya está en ruta auth:admin) -->
<div class="card mb-4">
    <form method="post" action="<?= site_url('productos/kardex/'.$producto['id'].'/movimiento') ?>" class="grid md:grid-cols-5 gap-3">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm mb-1">Tipo</label>
            <select name="tipo" class="input" required>
                <option value="entrada">Entrada</option>
                <option value="salida">Salida</option>
            </select>
        </div>
        <div>
            <label class="block text-sm mb-1">Cantidad</label>
            <input type="number" name="cantidad" min="1" class="input" required>
        </div>
        <div>
            <label class="block text-sm mb-1">Referencia</label>
            <input name="referencia" class="input" placeholder="COMP-001 / AJUSTE / PED-123">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm mb-1">Detalle</label>
            <input name="detalle" class="input" placeholder="Detalle del movimiento">
        </div>
        <div class="md:col-span-5 flex justify-end">
            <button class="btn btn-primary">Registrar movimiento</button>
        </div>
    </form>
</div>

<div class="card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
        <tr>
            <th class="p-3 text-left">Fecha</th>
            <th class="p-3 text-left">Tipo</th>
            <th class="p-3 text-right">Cantidad</th>
            <th class="p-3 text-right">Saldo</th>
            <th class="p-3 text-left">Referencia</th>
            <th class="p-3 text-left">Detalle</th>
        </tr>
        </thead>
        <tbody class="divide-y">
        <?php if(!$movimientos): ?>
            <tr><td class="p-3" colspan="6">Sin movimientos en el rango seleccionado.</td></tr>
        <?php else: foreach($movimientos as $m): ?>
            <tr class="hover:bg-slate-50">
                <td class="p-3"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                <td class="p-3">
                    <?php if($m['tipo']==='entrada'): ?>
                        <span class="badge" style="background:#dcfce7;color:#166534">Entrada</span>
                    <?php else: ?>
                        <span class="badge" style="background:#fee2e2;color:#991b1b">Salida</span>
                    <?php endif; ?>
                </td>
                <td class="p-3 text-right"><?= (int)$m['cantidad'] ?></td>
                <td class="p-3 text-right font-semibold"><?= (int)$m['saldo'] ?></td>
                <td class="p-3"><?= esc($m['referencia'] ?? '') ?></td>
                <td class="p-3"><?= esc($m['detalle'] ?? '') ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4">
    <a class="btn btn-outline" href="<?= site_url('productos') ?>">Volver a Productos</a>
</div>

<?= $this->endSection() ?>
