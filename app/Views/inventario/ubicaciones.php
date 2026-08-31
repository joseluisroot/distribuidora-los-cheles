<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?php $sites = $sites ?? []; $warehouses = $warehouses ?? []; $locations = $locations ?? []; $audit = $audit ?? []; $canAdjust = $canAdjust ?? false; ?>
<link rel="stylesheet" href="<?= base_url('assets/css/access.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/sweetalert2/sweetalert2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/warehouse.css') ?>">
<main class="access-page">
    <header class="access-heading"><div>
        <p class="access-eyebrow">INVENTARIO / ESTRUCTURA FÍSICA</p>
        <h1>Bodegas y ubicaciones</h1>
        <p>Centro de distribución, sucursales y posiciones de almacenamiento.</p>
    </div><a class="access-link" href="<?= site_url('dashboard') ?>">Volver a mi panel →</a></header>
    <aside class="access-notice"><strong>Primera entrega de inventario</strong>
        <p>Consulta de estructura física. Las existencias, lotes y traslados se incorporarán en las siguientes entregas. No se ha trasladado el inventario anterior.</p>
    </aside>
    <div class="warehouse-summary" aria-label="Resumen de estructura">
        <div><span>Sedes</span><strong><?= count($sites) ?></strong></div>
        <div><span>Almacenes</span><strong><?= count($warehouses) ?></strong></div>
        <div><span>Ubicaciones</span><strong><?= count($locations) ?></strong></div>
    </div>
    <?php if ($installed && $canAdjust): ?>
    <section class="access-card" aria-labelledby="structure-admin-title">
        <div class="warehouse-section-heading"><div><p class="access-eyebrow">CONFIGURACIÓN CONTROLADA</p><h2 id="structure-admin-title">Registrar estructura física</h2></div><p>Los cambios requieren motivo y quedan auditados.</p></div>
        <div class="warehouse-forms">
            <form method="post" action="<?= site_url('inventario/sedes') ?>" class="warehouse-form">
                <?= csrf_field() ?><h3>1. Nueva sede</h3>
                <label>Código<input name="codigo" maxlength="30" placeholder="SUC-01" required></label>
                <label>Nombre<input name="nombre" maxlength="120" placeholder="Sucursal principal" required></label>
                <label>Tipo<select name="tipo"><option value="sucursal">Sucursal</option><option value="central">Centro de distribución</option></select></label>
                <label>Motivo<textarea name="reason" maxlength="500" required placeholder="Razón del registro"></textarea></label>
                <button class="access-button">Registrar sede</button>
            </form>
            <form method="post" action="<?= site_url('inventario/almacenes') ?>" class="warehouse-form">
                <?= csrf_field() ?><h3>2. Nuevo almacén</h3>
                <label>Sede<select name="sede_id" required><option value="">Selecciona…</option><?php foreach ($sites as $site): ?><option value="<?= (int) $site['id'] ?>"><?= esc($site['codigo'].' · '.$site['nombre']) ?></option><?php endforeach ?></select></label>
                <label>Código<input name="codigo" maxlength="30" placeholder="BOD-01" required></label>
                <label>Nombre<input name="nombre" maxlength="120" placeholder="Bodega principal" required></label>
                <label>Tipo<select name="tipo"><option value="bodega">Bodega</option><option value="sala">Sala de ventas</option></select></label>
                <label>Motivo<textarea name="reason" maxlength="500" required placeholder="Razón del registro"></textarea></label>
                <button class="access-button" <?= !$sites ? 'disabled' : '' ?>>Registrar almacén</button>
            </form>
            <form method="post" action="<?= site_url('inventario/ubicaciones') ?>" class="warehouse-form">
                <?= csrf_field() ?><h3>3. Nueva ubicación</h3>
                <label>Almacén<select name="almacen_id" required><option value="">Selecciona…</option><?php foreach ($warehouses as $warehouse): ?><option value="<?= (int) $warehouse['id'] ?>"><?= esc($warehouse['sede'].' · '.$warehouse['codigo']) ?></option><?php endforeach ?></select></label>
                <label>Código<input name="codigo" maxlength="50" placeholder="A-01-02" required></label>
                <div class="warehouse-position"><label>Pasillo<input name="pasillo" maxlength="30" required></label><label>Fila<input name="fila" maxlength="30" required></label><label>Columna<input name="columna" maxlength="30" required></label></div>
                <label>Motivo<textarea name="reason" maxlength="500" required placeholder="Razón del registro"></textarea></label>
                <button class="access-button" <?= !$warehouses ? 'disabled' : '' ?>>Registrar ubicación</button>
            </form>
        </div>
    </section>
    <?php endif ?>
    <?php if ($installed && ($sites || $warehouses)): ?>
    <section class="access-card" aria-labelledby="registered-structure-title">
        <h2 id="registered-structure-title">Sedes y almacenes registrados</h2>
        <div class="access-table-wrap"><table><thead><tr><th>Nivel</th><th>Código y nombre</th><th>Tipo</th><th>Estado</th><?php if ($canAdjust): ?><th>Acción controlada</th><?php endif ?></tr></thead><tbody>
        <?php foreach ($sites as $item): ?><tr><td>Sede</td><td><strong><?= esc($item['codigo']) ?></strong><br><?= esc($item['nombre']) ?></td><td><?= esc($item['tipo']) ?></td><td><?= $item['activa'] ? 'Activa' : 'Inactiva' ?></td><?php if ($canAdjust): ?><td><?= view('inventario/_status_form', ['type'=>'sede','item'=>$item]) ?></td><?php endif ?></tr><?php endforeach ?>
        <?php foreach ($warehouses as $item): ?><tr><td>Almacén<br><span class="access-muted"><?= esc($item['sede']) ?></span></td><td><strong><?= esc($item['codigo']) ?></strong><br><?= esc($item['nombre']) ?></td><td><?= esc($item['tipo']) ?></td><td><?= $item['activa'] ? 'Activo' : 'Inactivo' ?></td><?php if ($canAdjust): ?><td><?= view('inventario/_status_form', ['type'=>'almacen','item'=>$item]) ?></td><?php endif ?></tr><?php endforeach ?>
        </tbody></table></div>
    </section>
    <?php endif ?>
    <section class="access-card" aria-labelledby="locations-title">
        <h2 id="locations-title">Ubicaciones registradas</h2>
        <?php if (!$installed): ?>
            <p role="status">La estructura de bodegas está pendiente de instalación. El administrador debe revisar y aplicar la migración de I2 antes de registrar ubicaciones.</p>
        <?php elseif (!$locations): ?>
            <p class="access-empty">Todavía no hay ubicaciones registradas. El siguiente paso es definir los nombres y códigos reales de las sedes y sus almacenes.</p>
        <?php else: ?>
            <div class="access-table-wrap" tabindex="0" role="region" aria-label="Ubicaciones, tabla desplazable">
                <table><thead><tr><th>Sede</th><th>Almacén</th><th>Código</th><th>Pasillo / fila / columna</th><th>Estado</th><?php if ($canAdjust): ?><th>Acción controlada</th><?php endif ?></tr></thead><tbody>
                <?php foreach ($locations as $location): ?>
                    <tr><td><?= esc($location['sede']) ?><br><span class="access-muted"><?= esc($location['tipo_sede']) ?></span></td>
                    <td><?= esc($location['almacen']) ?><br><span class="access-muted"><?= esc($location['tipo_almacen']) ?></span></td>
                    <td><?= esc($location['codigo']) ?></td>
                    <td><?= esc($location['pasillo'].' / '.$location['fila'].' / '.$location['columna']) ?></td>
                    <td><span class="warehouse-status <?= $location['activa'] && $location['almacen_activo'] && $location['sede_activa'] ? 'is-active' : 'is-inactive' ?>"><?= $location['activa'] && $location['almacen_activo'] && $location['sede_activa'] ? 'Activa' : 'Inactiva' ?></span></td><?php if ($canAdjust): ?><td><?= view('inventario/_status_form', ['type'=>'ubicacion','item'=>$location]) ?></td><?php endif ?></tr>
                <?php endforeach ?>
                </tbody></table>
            </div>
        <?php endif; ?>
    </section>
    <?php if ($installed && $canAdjust && $audit): ?><section class="access-card"><h2>Actividad reciente</h2><div class="access-table-wrap"><table><thead><tr><th>Fecha</th><th>Elemento</th><th>Acción</th><th>Motivo</th></tr></thead><tbody><?php foreach ($audit as $event): ?><tr><td><?= esc($event['created_at']) ?></td><td><?= esc($event['entity_type'].' #'.$event['entity_id']) ?></td><td><?= esc($event['action']) ?></td><td><?= esc($event['reason']) ?></td></tr><?php endforeach ?></tbody></table></div></section><?php endif ?>
    <p class="access-muted">Los pedidos web se abastecerán únicamente de bodegas activas de sucursal, nunca de sala de ventas ni del centro de distribución.</p>
</main>
<script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.min.js') ?>"></script>
<script>window.warehouseFlash=<?= json_encode(['success' => session('message'), 'error' => session('error')], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;</script>
<script src="<?= base_url('assets/js/warehouse.js') ?>"></script>
<?= $this->endSection() ?>
