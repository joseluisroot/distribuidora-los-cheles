<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/navbar') ?>

<h1 class="text-2xl font-bold mb-4">Pedidos</h1>

<div class="card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
        <tr>
            <th class="p-3 text-left">#</th>
            <th class="p-3 text-left">Cliente</th>
            <th class="p-3 text-left">Estado</th>
            <th class="p-3 text-right">Total</th>
            <th class="p-3 text-left">Fecha</th>
            <th class="p-3 text-right">Acciones</th>
        </tr>
        </thead>
        <tbody class="divide-y">
        <?php foreach($pedidos as $p): ?>
            <tr class="hover:bg-slate-50">
                <td class="p-3"><?= $p['id'] ?></td>
                <td class="p-3"><?= esc($p['cliente']) ?></td>
                <td class="p-3">
                    <?php if($p['estado']==='ingresado'): ?>
                        <span class="badge badge-blue">Ingresado</span>
                    <?php elseif($p['estado']==='preparando'): ?>
                        <span class="badge badge-yellow">Preparando</span>
                    <?php else: ?>
                        <span class="badge badge-green">Procesado</span>
                    <?php endif; ?>
                </td>
                <td class="p-3 text-right">$<?= number_format($p['total'],2) ?></td>
                <td class="p-3"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                <td class="p-3 text-right">
                    <a href="<?= site_url('pedidos/'.$p['id']) ?>" class="btn btn-outline">Ver</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
