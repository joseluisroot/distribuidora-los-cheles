<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="px-4 md:px-8 py-6">

    <!-- Título y acciones rápidas -->
    <div class="flex flex-wrap gap-4 items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">Panel administrativo</h1>
            <p class="text-sm text-slate-500">Resumen de pedidos registrados e inventario actual</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= site_url('pedidos') ?>" class="btn btn-primary px-4 py-2 rounded-xl shadow">
                Ver pedidos
            </a>
            <a href="<?= site_url('productos') ?>" class="btn border px-4 py-2 rounded-xl">
                Ver Inventario
            </a>
        </div>
    </div>
    <p class="mb-6 text-sm text-slate-500">La creación de pedidos se habilitará con el flujo de reservas y pagos. Los importes mostrados no representan cobros verificados ni facturas.</p>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <?php
        $cards = [
            ['title' => 'Importe de pedidos de hoy', 'value' => isset($kpi['ventasHoy']) ? number_format($kpi['ventasHoy'], 2) : '—', 'hint' => 'Según fecha de registro', 'icon' => '💰'],
            ['title' => 'Pedidos pendientes', 'value' => $kpi['pedidosPendientes'] ?? '—', 'hint' => 'Indicador pendiente de implementar', 'icon' => '📦'],
            ['title' => 'Stock bajo', 'value' => $kpi['stockBajo'] ?? '—', 'hint' => isset($kpi['stockBajo']) ? '<10 unidades' : 'Dato no disponible', 'icon' => '⚠️'],
            ['title' => 'Clientes activos', 'value' => $kpi['clientesActivos'] ?? '—', 'hint' => isset($kpi['clientesActivos']) ? 'Con pedidos recientes' : 'Dato no disponible', 'icon' => '👥'],
        ];
        ?>
        <?php foreach ($cards as $c): ?>
            <div class="rounded-2xl p-5 bg-white shadow border">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-500 text-sm"><?= esc($c['title']) ?></p>
                        <div class="text-2xl font-bold mt-1"><?= esc($c['value']) ?></div>
                        <p class="text-xs text-slate-400 mt-1"><?= $c['hint'] ?></p>
                    </div>
                    <div class="text-3xl"><?= $c['icon'] ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Contenido principal: Tabla + Gráfica + Stock crítico -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Últimos pedidos -->
        <div class="xl:col-span-2 bg-white shadow border rounded-2xl">
            <div class="p-5 border-b">
                <h2 class="font-semibold">Últimos pedidos</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left px-4 py-2">#</th>
                        <th class="text-left px-4 py-2">Cliente</th>
                        <th class="text-left px-4 py-2">Estado</th>
                        <th class="text-right px-4 py-2">Total</th>
                        <th class="text-left px-4 py-2">Fecha</th>
                        <th class="text-right px-4 py-2">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($ultimosPedidos)): ?>
                        <?php foreach ($ultimosPedidos as $row): ?>
                            <tr class="border-t">
                                <td class="px-4 py-2"><?= esc($row['id']) ?></td>
                                <td class="px-4 py-2 truncate max-w-[240px]"><?= esc($row['customer_name'] ?? '—') ?></td>
                                <td class="px-4 py-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs
                                            <?= ($row['status'] ?? '') === 'PENDIENTE' ? 'bg-amber-100 text-amber-700' :
                                            (($row['status'] ?? '') === 'PAGADO' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700') ?>">
                                            <?= esc($row['status'] ?? '—') ?>
                                        </span>
                                </td>
                                <td class="px-4 py-2 text-right">$<?= number_format($row['total'] ?? 0, 2) ?></td>
                                <td class="px-4 py-2"><?= esc(date('Y-m-d H:i', strtotime($row['created_at'] ?? 'now'))) ?></td>
                                <td class="px-4 py-2 text-right">
                                    <a class="text-primary hover:underline" href="<?= site_url('pedidos/'.(int) $row['id']) ?>">Ver pedido</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td class="px-4 py-6 text-center text-slate-500" colspan="6">Sin pedidos recientes</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ventas últimos 7 días (Gráfica) -->
        <div class="bg-white shadow border rounded-2xl">
            <div class="p-5 border-b flex items-center justify-between">
                <h2 class="font-semibold">Importe de pedidos por fecha</h2>
            </div>
            <div class="p-5">
                <canvas id="chart-ventas-7d" class="w-full h-64"></canvas>
            </div>
        </div>

        <!-- Stock crítico -->
        <div class="xl:col-span-1 bg-white shadow border rounded-2xl">
            <div class="p-5 border-b flex items-center justify-between">
                <h2 class="font-semibold">Stock crítico</h2>
                <a href="<?= site_url('productos') ?>" class="text-sm text-primary hover:underline">Ver todos los productos</a>
            </div>
            <div class="p-5">
                <ul class="space-y-3">
                    <?php if (!empty($stockCritico)): ?>
                        <?php foreach ($stockCritico as $p): ?>
                            <li class="flex items-center justify-between">
                                <div class="min-w-0">
                                    <p class="truncate font-medium"><?= esc($p['name'] ?? '—') ?></p>
                                    <p class="text-xs text-slate-500"><?= esc($p['sku'] ?? '') ?></p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">
                                    <?= (int) ($p['stock'] ?? 0) ?> u
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-sm text-slate-500"><?= ($stockCritico ?? null) === null ? 'Consulta de stock crítico no disponible.' : 'Sin productos críticos.' ?></p>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js CDN (ligero y directo) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function() {
        const ventas = <?= json_encode(array_map(fn($r)=> (float)$r['total'], $ventas7d ?? [])) ?>;
        const labels = <?= json_encode(array_map(fn($r)=> $r['fecha'], $ventas7d ?? [])) ?>;

        const ctx = document.getElementById('chart-ventas-7d');
        if (ctx && labels.length) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Importe de pedidos ($)',
                        data: ventas,
                        fill: false,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    })();
</script>
<?= $this->endSection() ?>
