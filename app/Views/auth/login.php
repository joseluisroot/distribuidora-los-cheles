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
            <h1 class="mt-3 text-2xl font-bold text-gray-800">Ingresar</h1>
            <p class="text-sm text-muted">Sistema de pedidos e inventario para clientes</p>
        </div>

        <div class="card p-6">
            <?php if (session('error')): ?>
                <div class="alert alert-error mb-4"><?= esc(session('error')) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= site_url('login') ?>" class="space-y-4" novalidate>
                <?= csrf_field() ?>

                <!-- Email -->
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
                    >
                    <?php if (isset(session('errors')['email'])): ?>
                        <p class="mt-1 text-xs text-red-600"><?= esc(session('errors')['email']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm mb-1 text-gray-700">Contraseña</label>
                    <div class="relative">
                        <input
                                id="password"
                                name="password"
                                type="password"
                                class="input pr-10 <?= isset(session('errors')['password']) ? 'border-red-400' : '' ?>"
                                autocomplete="current-password"
                                required
                        >
                        <button type="button" class="absolute inset-y-0 right-0 px-3 text-sm text-muted hover:text-gray-700"
                                aria-label="Mostrar u ocultar contraseña" onclick="togglePassword()">
                            👁
                        </button>
                    </div>
                    <?php if (isset(session('errors')['password'])): ?>
                        <p class="mt-1 text-xs text-red-600"><?= esc(session('errors')['password']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                        Recuérdame
                    </label>
                    <a href="<?= site_url('forgot') ?>" class="text-sm text-primary hover:underline">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <!-- Submit -->
                <button class="btn btn-primary w-full" id="btn-submit">
                    <span id="btn-text">Entrar</span>
                    <svg id="btn-spinner" class="animate-spin h-5 w-5 ml-2 hidden" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"></circle>
                        <path class="opacity-75" d="M4 12a8 8 0 018-8v8z" fill="currentColor"></path>
                    </svg>
                </button>

                <!-- Separador opcional
        <div class="flex items-center gap-3">
          <div class="flex-1 h-px bg-slate-200"></div>
          <span class="text-xs text-muted">o</span>
          <div class="flex-1 h-px bg-slate-200"></div>
        </div>

        <div class="grid grid-cols-1 gap-2">
          <a href="<?= site_url('auth/google') ?>" class="btn btn-outline w-full justify-center">Continuar con Google</a>
        </div>
        -->

                <p class="text-xs text-center text-muted">
                    ¿No tienes cuenta? <a href="<?= site_url('register') ?>" class="text-primary hover:underline">Regístrate</a>
                </p>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            Protegido por medidas de seguridad. Nunca compartas tu contraseña.
        </p>
    </div>
</div>

<script>
    function togglePassword(){
        const input = document.getElementById('password');
        input.type = input.type === 'password' ? 'text' : 'password';
    }
    // feedback al enviar
    const form = document.querySelector('form');
    form?.addEventListener('submit', () => {
        const btn = document.getElementById('btn-submit');
        const txt = document.getElementById('btn-text');
        const spn = document.getElementById('btn-spinner');
        btn.disabled = true;
        btn.classList.add('opacity-80','cursor-not-allowed');
        txt.textContent = 'Ingresando...';
        spn.classList.remove('hidden');
    });
</script>

<?= $this->endSection() ?>
