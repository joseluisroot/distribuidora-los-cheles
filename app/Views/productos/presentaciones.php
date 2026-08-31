<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/access.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/sweetalert2/sweetalert2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/presentations.css') ?>">
<main class="access-page presentations-page">
    <header class="access-heading">
        <div><p class="access-eyebrow">CATÁLOGO / PRESENTACIONES</p><h1><?= esc($producto['nombre']) ?></h1><p>Configura cómo se vende este producto sin mezclar unidades y fardos.</p></div>
        <a class="access-link" href="<?= site_url('productos') ?>">Volver a productos →</a>
    </header>

    <aside class="access-notice"><strong>Regla comercial acordada</strong><p>El mayoreo se activa desde 3 unidades idénticas o 3 fardos idénticos. Un fardo de 10 equivale a 10 unidades base para inventario, pero cuenta como una presentación para precio.</p></aside>

    <section class="presentation-grid">
        <article class="access-card presentation-form-card">
            <div class="presentation-section-title"><div><p class="access-eyebrow">NUEVA PRESENTACIÓN</p><h2>Definir formato de venta</h2></div><span class="presentation-sku"><?= esc($producto['sku']) ?></span></div>
            <form method="post" action="<?= site_url('productos/'.$producto['id'].'/presentaciones') ?>" class="presentation-form" novalidate>
                <?= csrf_field() ?>
                <div class="presentation-fields">
                    <label>Código interno<input name="codigo" maxlength="30" value="<?= old('codigo') ?>" placeholder="UNIDAD o FARDO-10" required><small>Letras, números y guiones.</small></label>
                    <label>Nombre visible<input name="nombre" maxlength="80" value="<?= old('nombre') ?>" placeholder="Fardo de 10 unidades" required></label>
                    <label>Unidades base<input type="number" name="unidades_base" min="1" max="1000000" value="<?= old('unidades_base', '1') ?>" required><small>Unidad = 1; fardo de 10 = 10.</small></label>
                    <label>Precio detalle ($)<input type="number" name="precio_detalle" min="0" step="0.01" value="<?= old('precio_detalle') ?>" placeholder="0.00" required></label>
                    <label>Precio mayoreo ($)<input type="number" name="precio_mayoreo" min="0" step="0.01" value="<?= old('precio_mayoreo') ?>" placeholder="0.00" required><small>Se usa desde 3 presentaciones iguales.</small></label>
                </div>
                <label>Motivo del registro<textarea name="reason" maxlength="500" placeholder="Ej.: incorporación de presentación autorizada" required><?= old('reason') ?></textarea></label>
                <button class="access-button" type="submit">Guardar presentación</button>
            </form>
        </article>

        <article class="access-card presentation-summary">
            <p class="access-eyebrow">VISTA RÁPIDA</p><h2>Cómo se calculará</h2>
            <div class="presentation-example"><span>1–2</span><div><strong>Precio detalle</strong><p>De la misma presentación.</p></div></div>
            <div class="presentation-example is-wholesale"><span>3+</span><div><strong>Precio mayoreo</strong><p>Sin combinar formatos diferentes.</p></div></div>
            <p class="access-muted">Los costos de compra no se muestran ni se administran en esta pantalla.</p>
        </article>
    </section>

    <section class="access-card">
        <div class="presentation-section-title"><div><p class="access-eyebrow">CONFIGURACIÓN ACTUAL</p><h2>Presentaciones registradas</h2></div><strong><?= count($presentaciones) ?></strong></div>
        <?php if (!$presentaciones): ?><div class="presentation-empty"><strong>Aún no hay presentaciones</strong><p>Registra primero la unidad o el formato principal de venta.</p></div>
        <?php else: ?><div class="access-table-wrap"><table><thead><tr><th>Presentación</th><th>Equivalencia</th><th>Detalle</th><th>Mayoreo (3+)</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
        <?php foreach ($presentaciones as $item): ?><tr><td><strong><?= esc($item['nombre']) ?></strong><br><span class="access-muted"><?= esc($item['codigo']) ?></span></td><td><?= number_format((int) $item['unidades_base']) ?> unidad(es) base</td><td>$<?= number_format((float) $item['precio_detalle'], 2) ?></td><td><strong>$<?= number_format((float) $item['precio_mayoreo'], 2) ?></strong></td><td><span class="presentation-status <?= $item['activa'] ? 'is-active' : '' ?>"><?= $item['activa'] ? 'Activa' : 'Inactiva' ?></span></td><td><button type="button" class="presentation-edit-button" data-presentation-toggle="presentation-<?= (int)$item['id'] ?>">Editar</button></td></tr>
        <tr id="presentation-<?= (int)$item['id'] ?>" class="presentation-editor" hidden><td colspan="6"><form method="post" action="<?= site_url('productos/'.$producto['id'].'/presentaciones/'.$item['id']) ?>"><?= csrf_field() ?><div class="presentation-editor-grid"><label>Código<input name="codigo" maxlength="30" value="<?= esc($item['codigo']) ?>" required></label><label>Nombre<input name="nombre" maxlength="80" value="<?= esc($item['nombre']) ?>" required></label><label>Unidades base<input type="number" name="unidades_base" min="1" value="<?= (int)$item['unidades_base'] ?>" required></label><label>Detalle ($)<input type="number" name="precio_detalle" min="0" step="0.01" value="<?= esc($item['precio_detalle']) ?>" required></label><label>Mayoreo ($)<input type="number" name="precio_mayoreo" min="0" step="0.01" value="<?= esc($item['precio_mayoreo']) ?>" required></label><label class="presentation-check"><input type="checkbox" name="activa" value="1" <?= $item['activa'] ? 'checked' : '' ?>> Presentación activa</label></div><label>Motivo del cambio<textarea name="reason" maxlength="500" required placeholder="Explica por qué cambia esta presentación"></textarea></label><button class="access-button" type="submit">Guardar cambios</button></form></td></tr><?php endforeach ?>
        </tbody></table></div><?php endif ?>
    </section>
</main>
<script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.min.js') ?>"></script>
<script>window.presentationFlash=<?= json_encode(['success'=>session('message'),'error'=>session('error')], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;</script>
<script src="<?= base_url('assets/js/presentations.js') ?>"></script>
<?= $this->endSection() ?>
