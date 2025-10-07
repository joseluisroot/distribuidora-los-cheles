<footer class="mt-10 border-t border-slate-200 pt-6 pb-4 text-center text-sm text-slate-500">
    <p>&copy; <?= date('Y') ?> <span class="font-semibold text-primary">Mi Tienda</span>. Todos los derechos reservados.</p>
    <div class="mt-2 flex justify-center gap-4 text-sm">
        <a href="<?= site_url('/') ?>" class="hover:text-primary">Inicio</a>
        <a href="<?= site_url('catalogo') ?>" class="hover:text-primary">Catálogo</a>
        <a href="<?= site_url('contacto') ?>" class="hover:text-primary">Contacto</a>
        <a href="<?= site_url('politicas') ?>" class="hover:text-primary">Políticas</a>
    </div>
</footer>
