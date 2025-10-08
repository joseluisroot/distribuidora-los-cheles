<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>

<div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="flex flex-col items-center justify-center mb-6">
                <img
                        src="<?= base_url('assets/Logo_LosCheles.PNG') ?>"
                        alt="Distribuidora Los Cheles"
                        class="w-44 h-auto drop-shadow-sm"
                />
                <p class="text-xs mt-2 text-slate-500 font-medium tracking-wide uppercase">
                    Sistema de pedidos e inventario
                </p>
            </div>
            <h1 class="mt-3 text-2xl font-bold text-gray-800">Recuperar contraseña</h1>
            <p class="text-sm text-muted">Ingresa tu correo y te enviaremos instrucciones para restablecerla.</p>
        </div>

        <?php // mensajes flash
        if (session('message')): ?>
            <div class="alert alert-success mb-4"><?= esc(session('message')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-error mb-4"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <div class="card p-6">
            <form method="post" action="<?= site_url('forgot') ?>" class="space-y-4" novalidate>
                <?= csrf_field() ?>

                <div>
                    <label for="email" class="block text-sm mb-1 text-gray-700">Correo</label>
                    <input
                            id="email"
                            name="email"
                            type="email"
                            class="input <?= isset(session('errors')['email']) ? 'border-red-400' : '' ?>"
                            value="<?= old('email') ?>"
                            autocomplete="email"
                            required
                            placeholder="tucorreo@empresa.com"
                    >
                    <?php if (isset(session('errors')['email'])): ?>
                        <p class="mt-1 text-xs text-red-600"><?= esc(session('errors')['email']) ?></p>
                    <?php endif; ?>
                </div>

                <button class="btn btn-primary w-full" id="btn-submit">
                    <span id="btn-text">Enviar instrucciones</span>
                    <svg id="btn-spinner" class="animate-spin h-5 w-5 ml-2 hidden" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"></circle>
                        <path class="opacity-75" d="M4 12a8 8 0 018-8v8z" fill="currentColor"></path>
                    </svg>
                </button>

                <div class="flex items-center justify-between text-sm">
                    <a href="<?= site_url('login') ?>" class="text-primary hover:underline">Volver a Ingresar</a>
                    <a href="<?= site_url('forgot/resend') ?>" class="text-muted hover:text-primary">Reenviar correo</a>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            * Por seguridad, si el correo no está registrado en <span class="font-semibold text-primary">Distribuidora Los Cheles</span>, no indicaremos si existe o no.
        </p>
    </div>
</div>

<script>
    // feedback al enviar
    const form = document.querySelector('form');
    form?.addEventListener('submit', () => {
        const btn = document.getElementById('btn-submit');
        const txt = document.getElementById('btn-text');
        const spn = document.getElementById('btn-spinner');
        btn.disabled = true;
        btn.classList.add('opacity-80','cursor-not-allowed');
        txt.textContent = 'Enviando...';
        spn.classList.remove('hidden');
    });
</script>

<?= $this->endSection() ?>
