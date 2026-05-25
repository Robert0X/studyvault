<?php
$pageTitle = 'Repaso CP';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-rotate me-2"></i>Repaso de problemas</h2>
        <p class="text-muted mb-0">Re-resuelve problemas difíciles antes de olvidar la técnica (SM-2)</p>
    </div>
    <a href="<?= BASE_URL ?>?page=cp" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Volver</a>
</div>

<?php if (empty($problems)): ?>
    <div class="text-center py-5">
        <i class="fa-solid fa-circle-check fa-4x text-success mb-3"></i>
        <h4>¡Sin repasos pendientes!</h4>
        <p class="text-muted">Marca problemas con "Repasar" desde la lista para programarlos.</p>
        <a href="<?= BASE_URL ?>?page=cp" class="btn btn-primary">Volver a problemas</a>
    </div>
<?php else: ?>
    <p class="text-muted"><span id="remaining"><?= count($problems) ?></span> por repasar</p>
    <ul class="list-group" id="reviewList">
        <?php foreach ($problems as $p): ?>
            <li class="list-group-item" id="rev-<?= $p['id'] ?>">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <?php if (!empty($p['problem_url'])): ?>
                            <a href="<?= htmlspecialchars($p['problem_url']) ?>" target="_blank" rel="noopener" class="fw-semibold text-decoration-none"><?= htmlspecialchars($p['name']) ?> <i class="fa-solid fa-arrow-up-right-from-square fa-xs"></i></a>
                        <?php else: ?>
                            <span class="fw-semibold"><?= htmlspecialchars($p['name']) ?></span>
                        <?php endif; ?>
                        <?php if ($p['rating']): ?><span class="badge bg-dark ms-1"><?= (int)$p['rating'] ?></span><?php endif; ?>
                        <?php if (!empty($p['editorial_note'])): ?><div class="small text-muted mt-1"><i class="fa-solid fa-lightbulb me-1"></i><?= htmlspecialchars($p['editorial_note']) ?></div><?php endif; ?>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-danger" onclick="reviewCp(<?= $p['id'] ?>,2)">Otra vez</button>
                        <button class="btn btn-warning" onclick="reviewCp(<?= $p['id'] ?>,3)">Difícil</button>
                        <button class="btn btn-info text-white" onclick="reviewCp(<?= $p['id'] ?>,4)">Bien</button>
                        <button class="btn btn-success" onclick="reviewCp(<?= $p['id'] ?>,5)">Fácil</button>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<script>
function reviewCp(id, quality) {
    const b = new FormData();
    b.append('_action', 'review_submit'); b.append('csrf_token', CSRF_TOKEN);
    b.append('id', id); b.append('quality', quality);
    fetch(BASE_URL + '?page=cp', { method: 'POST', body: b }).then(r => r.json()).then(d => {
        if (d.success) {
            document.getElementById('rev-' + id)?.remove();
            document.getElementById('remaining').textContent = d.remaining;
            if (d.remaining === 0) location.reload();
        }
    });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
