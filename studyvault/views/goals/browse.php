<?php
$pageTitle = 'Plantillas públicas';
require __DIR__ . '/../partials/header.php';
$isGuest = $isGuest ?? empty($_SESSION['user_id']);
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-globe me-2"></i>Plantillas públicas</h2>
        <p class="text-muted mb-0">Explora planes de estudio compartidos por la comunidad</p>
    </div>
    <?php if ($isGuest): ?>
        <a href="<?= BASE_URL ?>?page=login" class="btn btn-outline-secondary"><i class="fa-solid fa-right-to-bracket me-1"></i>Iniciar sesión</a>
    <?php else: ?>
        <a href="<?= BASE_URL ?>?page=goals" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Mis metas</a>
    <?php endif; ?>
</div>

<?php if ($isGuest): ?>
    <div class="alert alert-info d-flex align-items-center gap-3 mb-4">
        <i class="fa-solid fa-eye fa-2x"></i>
        <div>
            <strong>Modo invitado</strong> — estás viendo las plantillas como visitante.
            <a href="<?= BASE_URL ?>?page=login#registerTab" class="alert-link">Regístrate</a>
            para clonarlas a tu cuenta y empezar a estudiar con repetición espaciada, metas y métricas reales.
        </div>
    </div>
<?php endif; ?>

<?php if (empty($templates)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-globe fa-3x mb-3 opacity-50"></i>
        <h5>Aún no hay plantillas públicas</h5>
        <p>Cuando un usuario publique una meta como plantilla, aparecerá aquí.</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($templates as $t): ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-1"><?= htmlspecialchars($t['title']) ?></h5>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($t['description'] ?: 'Sin descripción') ?></p>
                    <div class="small text-muted mb-3">
                        <i class="fa-solid fa-user me-1"></i><?= htmlspecialchars($t['owner']) ?>
                        <?php if (!$isGuest && (int)$t['owner_id'] === (int)$_SESSION['user_id']): ?>
                            <span class="badge bg-secondary ms-1">Tuya</span>
                        <?php endif; ?>
                        · <i class="fa-solid fa-box-archive me-1"></i><?= (int)$t['resource_count'] ?> recursos
                        <?php if ($t['target_date']): ?> · <i class="fa-regular fa-calendar me-1"></i><?= htmlspecialchars($t['target_date']) ?><?php endif; ?>
                    </div>
                    <?php if ($isGuest): ?>
                        <a href="<?= BASE_URL ?>?page=login#registerTab" class="btn btn-outline-primary mt-auto">
                            <i class="fa-solid fa-user-plus me-1"></i>Crear cuenta para clonar
                        </a>
                    <?php else: ?>
                        <button class="btn btn-primary mt-auto" onclick="cloneTpl(<?= $t['id'] ?>, this)">
                            <i class="fa-solid fa-clone me-1"></i>Clonar a mis metas
                        </button>
                    <?php endif; ?>
                </div></div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!$isGuest): ?>
<script>
function cloneTpl(id, btn) {
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Clonando...';
    const b = new FormData(); b.append('_action', 'clone'); b.append('csrf_token', CSRF_TOKEN); b.append('id', id);
    fetch(BASE_URL + '?page=goals', { method: 'POST', body: b }).then(r => r.json()).then(d => {
        if (d.success) window.location = BASE_URL + '?page=goals&action=show&id=' + d.id;
        else { alert(d.message); btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-clone me-1"></i>Clonar a mis metas'; }
    });
}
</script>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
