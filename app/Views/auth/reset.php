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
            <h1 class="mt-3 text-2xl font-bold text-gray-800">Nueva contraseña</h1>
            <p class="text-sm text-muted">Crea una contraseña segura para tu cuenta.</p>
        </div>

        <?php if (session('message')): ?>
            <div class="alert alert-success mb-4"><?= esc(session('message')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-error mb-4"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <div class="card p-6">
            <form method="post" action="<?= site_url('reset') ?>" class="space-y-4" novalidate id="reset-form">
                <?= csrf_field() ?>

                <input type="hidden" name="token" value="<?= esc($token ?? '') ?>">
                <input type="hidden" name="email" value="<?= esc($email ?? '') ?>">

                <div>
                    <label for="password" class="block text-sm mb-1 text-gray-700">Contraseña</label>
                    <div class="relative">
                        <input
                                id="password"
                                name="password"
                                type="password"
                                class="input pr-10 <?= isset(session('errors')['password']) ? 'border-red-400' : '' ?>"
                                required minlength="8" autocomplete="new-password"
                                placeholder="Mínimo 8 caracteres"
                        >
                        <button type="button" class="absolute inset-y-0 right-0 px-3 text-sm text-muted hover:text-gray-700"
                                aria-label="Mostrar u ocultar contraseña" onclick="toggle('password')">👁</button>
                    </div>
                    <?php if (isset(session('errors')['password'])): ?>
                        <p class="mt-1 text-xs text-red-600"><?= esc(session('errors')['password']) ?></p>
                    <?php endif; ?>

                    <!-- Indicador fuerza -->
                    <div class="mt-2" aria-live="polite">
                        <div id="meter" class="h-2 rounded bg-slate-200 overflow-hidden">
                            <div id="bar" class="h-2 w-0 bg-green-500 transition-all"></div>
                        </div>
                        <p id="meter-text" class="mt-1 text-xs text-muted">Fuerza: —</p>
                    </div>

                    <!-- Requisitos -->
                    <ul class="mt-2 text-xs text-slate-500 space-y-1">
                        <li>• Mínimo 8 caracteres</li>
                        <li>• Recomendado: mayúsculas, minúsculas, número y símbolo</li>
                    </ul>
                </div>

                <div>
                    <label for="password_confirm" class="block text-sm mb-1 text-gray-700">Confirmar contraseña</label>
                    <div class="relative">
                        <input
                                id="password_confirm"
                                name="password_confirm"
                                type="password"
                                class="input pr-10 <?= isset(session('errors')['password_confirm']) ? 'border-red-400' : '' ?>"
                                required minlength="8" autocomplete="new-password"
                                placeholder="Repite la contraseña"
                        >
                        <button type="button" class="absolute inset-y-0 right-0 px-3 text-sm text-muted hover:text-gray-700"
                                aria-label="Mostrar u ocultar confirmación" onclick="toggle('password_confirm')">👁</button>
                    </div>
                    <p id="match-help" class="mt-1 text-xs text-red-600 hidden">Las contraseñas no coinciden.</p>
                    <?php if (isset(session('errors')['password_confirm'])): ?>
                        <p class="mt-1 text-xs text-red-600"><?= esc(session('errors')['password_confirm']) ?></p>
                    <?php endif; ?>
                </div>

                <button class="btn btn-primary w-full" id="btn-submit">
                    <span id="btn-text">Actualizar</span>
                    <svg id="btn-spinner" class="animate-spin h-5 w-5 ml-2 hidden" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"></circle>
                        <path class="opacity-75" d="M4 12a8 8 0 018-8v8z" fill="currentColor"></path>
                    </svg>
                </button>

                <p class="text-xs text-center text-muted">
                    Si el enlace expiró, solicita uno nuevo en <a class="text-primary hover:underline" href="<?= site_url('forgot') ?>">Recuperar contraseña</a>.
                </p>
            </form>
        </div>
    </div>
</div>

<script>
    function toggle(id){
        const el = document.getElementById(id);
        el.type = el.type === 'password' ? 'text' : 'password';
    }

    // Medidor sencillo de fuerza
    const pwd = document.getElementById('password');
    const bar = document.getElementById('bar');
    const meterText = document.getElementById('meter-text');
    pwd?.addEventListener('input', () => {
        const v = pwd.value || '';
        let score = 0;
        if (v.length >= 8) score++;
        if (/[A-Z]/.test(v)) score++;
        if (/[a-z]/.test(v)) score++;
        if (/\d/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;

        const pct = Math.min(score * 20, 100);
        bar.style.width = pct + '%';
        meterText.textContent = 'Fuerza: ' + (pct < 40 ? 'Débil' : pct < 80 ? 'Media' : 'Fuerte');
    });

    // Coincidencia de confirmación
    const pwd2 = document.getElementById('password_confirm');
    const help = document.getElementById('match-help');
    function checkMatch(){
        const ok = pwd.value === pwd2.value;
        help.classList.toggle('hidden', ok);
        return ok;
    }
    pwd?.addEventListener('input', checkMatch);
    pwd2?.addEventListener('input', checkMatch);

    // Feedback al enviar
    const form = document.getElementById('reset-form');
    form?.addEventListener('submit', (e) => {
        if (!checkMatch()) {
            e.preventDefault();
            pwd2.focus();
            return;
        }
        const btn = document.getElementById('btn-submit');
        const txt = document.getElementById('btn-text');
        const spn = document.getElementById('btn-spinner');
        btn.disabled = true;
        btn.classList.add('opacity-80','cursor-not-allowed');
        txt.textContent = 'Actualizando...';
        spn.classList.remove('hidden');
    });
</script>

<?= $this->endSection() ?>
