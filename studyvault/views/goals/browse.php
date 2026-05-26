<?php
$pageTitle = 'Plantillas públicas';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-globe me-2"></i>Plantillas públicas</h2>
        <p class="text-muted mb-0">Clona planes de estudio compartidos por otros usuarios</p>
    </div>
    <a href="<?= BASE_URL ?>?page=goals" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Mis metas</a>
</div>

<?php if (empty($templates)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-globe fa-3x mb-3 opacity-50"></i>
        <h5>Aún no hay plantillas públicas</h5>
        <p>Cuando alguien publique una meta como plantilla, aparecerá aquí. Publica las tuyas con el candado en "Metas".</p>
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
                        · <i class="fa-solid fa-box-archive me-1"></i><?= (int)$t['resource_count'] ?> recursos
                        <?php if ($t['target_date']): ?> · <i class="fa-regular fa-calendar me-1"></i><?= htmlspecialchars($t['target_date']) ?><?php endif; ?>
                    </div>
                    <button class="btn btn-primary mt-auto" onclick="cloneTpl(<?= $t['id'] ?>, this)"><i class="fa-solid fa-clone me-1"></i>Clonar a mis metas</button>
                </div></div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

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

<?php require __DIR__ . '/../partials/footer.php'; ?>
