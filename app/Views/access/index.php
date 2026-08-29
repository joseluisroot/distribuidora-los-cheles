<?= $this->extend('layouts/app') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/vendor/datatables/dataTables.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/sweetalert2/sweetalert2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/access.css') ?>">
<?php
$allowedCount = count(array_filter($effective, static fn ($result) => $result['allowed']));
$effectLabels = ['allow' => 'Conceder', 'deny' => 'Denegar'];
?>
<main class="access-page">
<header class="access-heading"><div><p class="access-eyebrow">ADMINISTRACIÓN / SEGURIDAD</p>
<h1>Permisos efectivos y auditoría</h1>
<p>Consulta quién puede hacer qué y revisa los cambios de acceso.</p></div>
<a class="access-link" href="<?= site_url('dashboard') ?>">Volver a mi panel →</a></header>
<aside class="access-notice"><strong>Reglas de seguridad</strong><p>Una denegación explícita prevalece. Retirar un permiso del rol no elimina concesiones individuales. No puedes modificar tus propios permisos ni los de tu rol.</p></aside>
<div class="access-summary" aria-label="Resumen de la consulta">
<div><span>Permisos evaluados</span><strong><?= count($effective) ?></strong></div>
<div><span>Permitidos</span><strong><?= $allowedCount ?></strong></div>
<div><span>Denegados</span><strong><?= count($effective) - $allowedCount ?></strong></div>
</div>
<section class="access-card" aria-labelledby="access-query-title">
<h2 id="access-query-title">Consultar permisos</h2><p class="access-muted">Los resultados corresponden al usuario y ámbito de la última consulta enviada.</p>
<form method="get" class="access-query">
    <label>Usuario <select name="user_id"><?php foreach ($users as $user): ?>
        <option value="<?= (int) $user['id'] ?>" <?= $selectedId === (int) $user['id'] ? 'selected' : '' ?>><?= esc($user['name']) ?></option>
    <?php endforeach ?></select></label>
    <label>Ámbito <input name="scope" value="<?= esc($scope) ?>" placeholder="global o sucursal:1" required></label>
    <button class="access-button">Consultar</button>
</form>
<div class="access-table-wrap" tabindex="0" role="region" aria-label="Permisos efectivos, tabla desplazable">
<table><caption>Usuario #<?= (int) $selectedId ?> · Ámbito: <?= esc($scope) ?></caption><thead><tr><th scope="col">Permiso</th><th scope="col">Resultado</th><th scope="col">Origen</th></tr></thead><tbody>
<?php foreach ($effective as $code => $result): ?>
<tr class="<?= $result['allowed'] ? 'access-allowed' : 'access-denied' ?>"><td><?= esc($catalog[$code]) ?></td><td><?= $result['allowed'] ? 'Permitido' : 'Denegado' ?></td>
<td><?php foreach ($result['grants'] as $grant): ?>
<?= esc(($grant['subject_type'] === 'user' ? 'Usuario' : 'Rol').' #'.$grant['subject_id'].' · '.$grant['scope'].' · '.($effectLabels[$grant['effect']] ?? $grant['effect'])) ?><br>
<?php endforeach ?><?php if (!$result['grants']): ?><span class="access-muted">Sin regla aplicable</span><?php endif ?></td></tr>
<?php endforeach ?>
</tbody></table>
</div></section>
<section class="access-card" aria-labelledby="access-edit-title">
<h2 id="access-edit-title">Modificar acceso</h2><p class="access-muted">Cada cambio requiere un motivo y queda registrado en la auditoría.</p>
<form method="post" class="access-edit" action="<?= site_url('admin/accesos') ?>">
<?= csrf_field() ?>
<label>Tipo <select name="subject_type"><option value="user">Usuario</option><option value="role">Rol</option></select></label>
<label>ID destino <input type="number" min="1" name="subject_id" required></label>
<details class="access-directory"><summary>Consultar identificadores de usuarios y roles</summary>
<p>Usuarios: <?php foreach ($users as $u): ?>#<?= (int) $u['id'] ?> — <?= esc($u['name']) ?>; <?php endforeach ?></p>
<p>Roles: <?php foreach ($roles as $r): ?>#<?= (int) $r['id'] ?> — <?= esc($r['name']) ?>; <?php endforeach ?></p></details>
<label>Permiso <select name="permission"><?php foreach ($catalog as $code => $label): ?><option value="<?= esc($code) ?>"><?= esc($label) ?></option><?php endforeach ?></select></label>
<label>Ámbito <input name="scope" value="global" required></label>
<label>Acción <select name="effect"><option value="allow">Conceder</option><option value="deny">Denegar</option><option value="remove">Retirar regla</option></select></label>
<label class="access-wide">Motivo <textarea name="reason" maxlength="500" rows="3" placeholder="Explica por qué se requiere este cambio" required></textarea></label>
<div class="access-wide"><p class="access-muted">Retirar regla elimina solo esa asignación; no equivale a denegar el permiso.</p><button class="access-button">Guardar cambio</button></div>
</form>
</section>
<section class="access-card" aria-labelledby="access-audit-title">
<h2 id="access-audit-title">Historial de cambios</h2><p class="access-muted">Últimos 50 registros, del más reciente al más antiguo.</p>
<div class="access-table-wrap" tabindex="0" role="region" aria-label="Auditoría, tabla desplazable">
<table class="access-audit"><thead><tr><th scope="col">Fecha / actor</th><th scope="col">Destino / ámbito</th><th scope="col">Permiso / cambio</th><th scope="col">Motivo</th></tr></thead><tbody>
<?php foreach ($audit as $row): ?><tr>
<td><?= esc($row['created_at']) ?><br><span class="access-muted"><?= $row['actor_id'] === null ? 'Inicialización por consola' : 'Usuario #' . (int) $row['actor_id'] ?></span></td>
<td><?= esc(($row['subject_type'] === 'user' ? 'Usuario' : 'Rol').' #'.$row['subject_id']) ?><br><span class="access-muted"><?= esc($row['scope']) ?></span></td>
<td><?= esc($catalog[$row['permission']] ?? $row['permission']) ?><br><span class="access-muted"><?= esc(($effectLabels[$row['before_effect'] ?? ''] ?? 'Sin regla').' → '.($effectLabels[$row['after_effect'] ?? ''] ?? 'Sin regla')) ?></span></td>
<td><?= esc($row['reason']) ?></td></tr><?php endforeach ?>
</tbody></table>
<?php if (!$audit): ?><p class="access-empty">Todavía no hay cambios registrados.</p><?php endif ?>
</div></section></main>
<script src="<?= base_url('assets/vendor/datatables/dataTables.min.js') ?>" defer></script>
<script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.min.js') ?>" defer></script>
<script src="<?= base_url('assets/js/access-ui.js') ?>" defer></script>
<?= $this->endSection() ?>
