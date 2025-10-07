<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<?= view('partials/navbar') ?>

<h1 class="text-2xl font-bold mb-4">Nuevo Producto</h1>

<form method="post" class="card space-y-4">
    <?= csrf_field() ?>
    <div>
        <label class="block mb-1">SKU</label>
        <input name="sku" class="input" required>
    </div>
    <div>
        <label class="block mb-1">Nombre</label>
        <input name="nombre" class="input" required>
    </div>
    <div>
        <label class="block mb-1">Descripción</label>
        <textarea name="descripcion" class="input"></textarea>
    </div>
    <div>
        <label class="block mb-1">Precio Base</label>
        <input type="number" step="0.01" name="precio_base" class="input" required>
    </div>
    <div>
        <label class="block mb-1">Stock Inicial</label>
        <input type="number" name="stock" class="input" value="0">
    </div>
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_activo" value="1" checked>
        <span>Activo</span>
    </label>
    <div class="flex gap-2">
        <button class="btn btn-primary">Guardar</button>
        <a href="<?= site_url('productos') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
