<form method="post" class="card space-y-4">
    <?= csrf_field() ?>
    <div>
        <label class="block mb-1">SKU</label>
        <input name="sku" class="input" value="<?= esc($producto['sku']) ?>" required>
    </div>
    <div>
        <label class="block mb-1">Nombre</label>
        <input name="nombre" class="input" value="<?= esc($producto['nombre']) ?>" required>
    </div>
    <div>
        <label class="block mb-1">Descripción</label>
        <textarea name="descripcion" class="input"><?= esc($producto['descripcion']) ?></textarea>
    </div>
    <div>
        <label class="block mb-1">Precio Base</label>
        <input type="number" step="0.01" name="precio_base" class="input" value="<?= $producto['precio_base'] ?>" required>
    </div>
    <div>
        <label class="block mb-1">Stock</label>
        <input type="number" name="stock" class="input" value="<?= $inventario['stock'] ?? 0 ?>">
    </div>
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_activo" value="1" <?= $producto['is_activo'] ? 'checked':'' ?>>
        <span>Activo</span>
    </label>
    <div class="flex gap-2">
        <button class="btn btn-primary">Actualizar</button>
        <a href="<?= site_url('productos') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>
