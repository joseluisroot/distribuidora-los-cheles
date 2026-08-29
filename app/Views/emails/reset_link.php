<p>Hola <?= esc($name) ?>:</p>
<p>Solicitaste restablecer tu contraseña.</p>
<p><a href="<?= esc($resetUrl, 'attr') ?>">Elegir una nueva contraseña</a></p>
<p>El enlace es de un solo uso y vence en una hora. Si no lo solicitaste, ignora este mensaje.</p>
