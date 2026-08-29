(() => {
    const form = document.getElementById('registration-form');
    if (!form) return;
    const password = document.getElementById('register-password');
    const confirmation = document.getElementById('register-confirm');
    const submit = form.querySelector('[type="submit"]');
    const status = document.getElementById('registration-status');
    const checkMatch = () => {
        confirmation.setCustomValidity(confirmation.value && confirmation.value !== password.value
            ? 'Las contraseñas no coinciden.' : '');
    };
    password.addEventListener('input', checkMatch);
    confirmation.addEventListener('input', checkMatch);
    form.querySelectorAll('[data-password-target]').forEach(button => {
        button.hidden = false;
        button.addEventListener('click', () => {
            const field = document.getElementById(button.dataset.passwordTarget);
            const visible = field.type === 'password';
            field.type = visible ? 'text' : 'password';
            button.textContent = visible ? 'Ocultar' : 'Mostrar';
            button.setAttribute('aria-pressed', String(visible));
        });
    });
    form.addEventListener('submit', event => {
        checkMatch();
        if (!form.reportValidity()) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
        status.textContent = 'Creando tu cuenta…';
    });
    window.addEventListener('pageshow', () => {
        submit.disabled = false;
        status.textContent = '';
    });
})();
