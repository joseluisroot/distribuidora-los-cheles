<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= esc($title ?? 'Distribuidora Los Cheles') ?></title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1d4ed8',
                        secondary: '#9333ea',
                        muted: '#64748b',
                    }
                }
            }
        }
    </script>

    <!-- Utilitarios simples sin @apply -->
    <style>
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:.5rem;font-weight:600}
        .btn-primary{background:#1d4ed8;color:#fff}.btn-primary:hover{background:#1e40af}
        .btn-outline{border:1px solid #cbd5e1;color:#334155}.btn-outline:hover{background:#f1f5f9}
        .badge{display:inline-flex;align-items:center;padding:.15rem .5rem;border-radius:999px;font-size:.75rem;font-weight:700;background:#e2e8f0;color:#334155}
        .text-primary{color:#1d4ed8}.text-muted{color:#64748b}
        .card{background:#fff;border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,.08);padding:1rem}
        .alert{padding:.75rem 1rem;border-radius:.75rem;font-weight:600}
        .alert-success{background:#dcfce7;color:#065f46}
        .alert-error{background:#fee2e2;color:#991b1b}
        .input{width:100%;padding:.5rem .75rem;border:1px solid #cbd5e1;border-radius:.5rem}
        .input:focus{outline:none;box-shadow:0 0 0 3px rgba(29,78,216,.2)}

        .btn[disabled], .btn.is-disabled {
            opacity: .5;
            pointer-events: none;
            cursor: not-allowed;
            filter: grayscale(30%);
        }

    </style>

    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
            integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
    />


</head>
<body class="bg-gray-100 min-h-screen text-gray-800">
<?php
// switches opcionales desde la vista:
// $showNavbar = false; $showFooter = false;
$showNavbar = $showNavbar ?? true;
$showFooter = $showFooter ?? true;
?>

<div class="max-w-6xl mx-auto px-4 py-4">
    <!-- Navbar integrado (usa session('user') para roles) -->
    <?php if ($showNavbar): ?>
        <?php $user = session('user'); ?>
        <nav class="bg-white rounded-xl shadow mb-6">
            <div class="px-4 py-3 flex items-center justify-between">
                <a href="<?= site_url('/') ?>" class="font-extrabold text-lg text-primary">Distribuidora Los Cheles</a>
                <ul class="flex items-center gap-3">
                    <?php if($user): ?>
                        <?php if(($user['role'] ?? '') === 'admin'): ?>
                            <li><a class="text-muted hover:text-primary" href="<?= site_url('productos') ?>">Productos</a></li>
                            <li><a class="text-muted hover:text-primary" href="<?= site_url('pedidos') ?>">Pedidos</a></li>
                            <li><a class="text-muted hover:text-primary" href="<?= site_url('usuarios') ?>">Usuarios</a></li>
                        <?php else: ?>
                            <li><a class="text-muted hover:text-primary" href="<?= site_url('catalogo') ?>">Catálogo</a></li>
                            <li><a class="text-muted hover:text-primary" href="<?= site_url('carretilla') ?>">Mi Carretilla</a></li>
                            <li><a class="text-muted hover:text-primary" href="<?= site_url('pedidos') ?>">Mis Pedidos</a></li>
                        <?php endif; ?>
                        <li><span class="badge"><?= esc($user['name'] ?? 'Usuario') ?></span></li>
                        <li><a class="btn btn-outline" href="<?= site_url('logout') ?>">Salir</a></li>
                    <?php else: ?>
                        <li><a class="btn btn-primary" href="<?= site_url('login') ?>">Ingresar</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Flash messages -->
    <?php if(session('message')): ?>
        <div class="alert alert-success mb-4"><?= esc(session('message')) ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-error mb-4"><?= esc(session('error')) ?></div>
    <?php endif; ?>

    <!-- Contenido -->
    <?= $this->renderSection('content') ?>

    <!-- Footer integrado -->
    <?php if ($showFooter): ?>
        <?= $this->include('partials/footer') ?>
    <?php endif; ?>
</div>

<!-- Helpers JS simples (modales, etc.) -->
<script>
    function openModal(id){ document.getElementById(id)?.classList.remove('hidden'); }
    function closeModal(id){ document.getElementById(id)?.classList.add('hidden'); }
</script>

<?= $this->include('partials/whatsapp_fab') ?>
</body>
</html>
