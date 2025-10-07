<?php $user = session('user'); ?>
<nav class="bg-white rounded-xl shadow mb-6">
    <div class="px-4 py-3 flex items-center justify-between">
        <!-- Logo o nombre -->
        <a href="<?= site_url('/') ?>" class="font-extrabold text-lg text-primary">Mi Tienda</a>

        <!-- Menú -->
        <ul class="flex items-center gap-3">
            <?php if($user): ?>
                <?php if($user['role']==='admin'): ?>
                    <li><a class="text-muted hover:text-primary" href="<?= site_url('productos') ?>">Productos</a></li>
                    <li><a class="text-muted hover:text-primary" href="<?= site_url('pedidos') ?>">Pedidos</a></li>
                    <li><a class="text-muted hover:text-primary" href="<?= site_url('usuarios') ?>">Usuarios</a></li>
                <?php else: ?>
                    <li><a class="text-muted hover:text-primary" href="<?= site_url('catalogo') ?>">Catálogo</a></li>
                    <li><a class="text-muted hover:text-primary" href="<?= site_url('carretilla') ?>">Mi Carretilla</a></li>
                    <li><a class="text-muted hover:text-primary" href="<?= site_url('pedidos') ?>">Mis Pedidos</a></li>
                <?php endif; ?>

                <!-- Usuario + logout -->
                <li>
                    <span class="badge badge-gray"><?= esc($user['name'] ?? 'Usuario') ?></span>
                </li>
                <li>
                    <a class="btn btn-outline" href="<?= site_url('logout') ?>">Salir</a>
                </li>
            <?php else: ?>
                <!-- Si no hay login -->
                <li><a class="btn btn-primary" href="<?= site_url('login') ?>">Ingresar</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
