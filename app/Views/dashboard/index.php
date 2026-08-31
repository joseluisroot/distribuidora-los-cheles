<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard.css') ?>">
<?php $firstName = explode(' ', trim($user['name']))[0] ?? $user['name']; ?>
<main class="workspace">
    <header class="workspace-hero">
        <div><p class="workspace-eyebrow">MI ESPACIO DE TRABAJO</p><h1>Hola, <?= esc($firstName) ?></h1><p>Accede rápidamente a las operaciones disponibles para tu cuenta.</p></div>
        <div class="workspace-identity" aria-label="Sesión activa"><span><?= esc(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span><div><small>Sesión activa</small><strong><?= esc($user['name']) ?></strong></div></div>
    </header>
    <section aria-labelledby="workspace-modules-title">
        <div class="workspace-heading"><div><p class="workspace-eyebrow">ACCESOS RÁPIDOS</p><h2 id="workspace-modules-title">¿Qué deseas hacer?</h2></div><p>Solo aparecen los módulos autorizados para tu perfil.</p></div>
        <div class="workspace-grid">
            <a class="workspace-module" href="<?= site_url('catalogo') ?>"><span class="workspace-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 6h16v13H4zM8 6V4h8v2M8 11h8M8 15h5"/></svg></span><div><h3>Explorar catálogo</h3><p>Consulta productos y presentaciones disponibles.</p></div><b aria-hidden="true">→</b></a>
            <a class="workspace-module" href="<?= site_url('carretilla') ?>"><span class="workspace-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 4h2l2.2 10h9.9l2-7H7M9 19h.01M17 19h.01"/></svg></span><div><h3>Mi carretilla</h3><p>Revisa los artículos seleccionados antes de pedir.</p></div><b aria-hidden="true">→</b></a>
            <a class="workspace-module" href="<?= site_url('pedidos') ?>"><span class="workspace-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h12v18H6zM9 8h6M9 12h6M9 16h4"/></svg></span><div><h3>Pedidos</h3><p>Consulta pedidos y su avance operativo.</p></div><b aria-hidden="true">→</b></a>
            <?php if ($canManageProducts): ?><a class="workspace-module" href="<?= site_url('productos') ?>"><span class="workspace-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9zM4 7.5l8 4.5 8-4.5M12 12v9"/></svg></span><div><h3>Productos</h3><p>Administra catálogo, imágenes y presentaciones.</p></div><b aria-hidden="true">→</b></a><?php endif ?>
            <?php if ($canViewInventory ?? false): ?><a class="workspace-module workspace-module-featured" href="<?= site_url('inventario/ubicaciones') ?>"><span class="workspace-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 21V7l8-4 8 4v14M8 21v-6h8v6M8 9h.01M12 9h.01M16 9h.01"/></svg></span><div><small>INVENTARIO</small><h3>Bodegas y ubicaciones</h3><p>Configura sedes, almacenes y posiciones físicas.</p></div><b aria-hidden="true">→</b></a><?php endif ?>
            <?php if ($canManageAccess): ?><a class="workspace-module" href="<?= site_url('admin/accesos') ?>"><span class="workspace-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 4 6v5c0 5 3.4 8.6 8 10 4.6-1.4 8-5 8-10V6zM9 12l2 2 4-5"/></svg></span><div><h3>Permisos y auditoría</h3><p>Consulta accesos efectivos y cambios de seguridad.</p></div><b aria-hidden="true">→</b></a><?php endif ?>
        </div>
    </section>
    <aside class="workspace-progress"><div class="workspace-progress-icon" aria-hidden="true">✓</div><div><p class="workspace-eyebrow">PLATAFORMA EN EVOLUCIÓN</p><h2>Inventario trazable en construcción</h2><p>La estructura de sedes, bodegas y ubicaciones ya está disponible. Las existencias y movimientos se incorporarán en los siguientes cortes.</p></div><?php if ($canViewInventory ?? false): ?><a href="<?= site_url('inventario/ubicaciones') ?>">Continuar configuración →</a><?php endif ?></aside>
</main>
<?= $this->endSection() ?>
