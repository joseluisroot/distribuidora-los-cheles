<form method="post" action="<?= site_url('inventario/estructura/estado') ?>" class="warehouse-status-form" data-status-form>
    <?= csrf_field() ?>
    <input type="hidden" name="entity_type" value="<?= esc($type) ?>">
    <input type="hidden" name="entity_id" value="<?= (int) $item['id'] ?>">
    <input type="hidden" name="active" value="<?= $item['activa'] ? '0' : '1' ?>">
    <label><span class="sr-only">Motivo del cambio</span><input name="reason" maxlength="500" placeholder="Motivo obligatorio" required></label>
    <button class="warehouse-action <?= $item['activa'] ? 'is-disable' : 'is-enable' ?>"><?= $item['activa'] ? 'Desactivar' : 'Activar' ?></button>
</form>
