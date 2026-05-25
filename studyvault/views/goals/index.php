<?php
$pageTitle = 'Metas';
require __DIR__ . '/../partials/header.php';
$statusInfo = ['active' => ['Activa', 'primary'], 'paused' => ['Pausada', 'secondary'], 'done' => ['Terminada', 'success']];
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-bullseye me-2"></i>Metas</h2>
        <p class="text-muted mb-0">Planes que agrupan varios recursos y miden tu avance real</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#goalModal" onclick="resetGoal()">
        <i class="fa-solid fa-plus me-1"></i>Nueva meta
    </button>
</div>

<?php if (empty($goals)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-bullseye fa-3x mb-3 opacity-50"></i>
        <h5>Aún no tienes metas</h5>
        <p>Crea una (ej. "Inglés B2 para diciembre") y agrégale tus libros, cursos y prácticas.</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($goals as $g): [$sl, $scl] = $statusInfo[$g['status']] ?? ['—', 'secondary']; $pace = $g['pace']; ?>
            <div class="col-12 col-md-6 col-xl-4" id="goal-<?= $g['id'] ?>">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-1"><?= htmlspecialchars($g['title']) ?></h5>
                            <span class="badge bg-<?= $scl ?> bg-opacity-15 text-<?= $scl ?>"><?= $sl ?></span>
                        </div>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($g['description'] ?: 'Sin descripción') ?></p>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-semibold"><?= (int)$g['pct'] ?>% completado</span>
                            <?php if ($pace['has_target']): ?>
                                <span class="badge bg-<?= $pace['on_track'] ? 'success' : 'danger' ?> bg-opacity-15 text-<?= $pace['on_track'] ? 'success' : 'danger' ?>">
                                    <?= htmlspecialchars($pace['label']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="progress sv-progress mb-2">
                            <div class="progress-bar bg-primary" style="width:<?= (int)$g['pct'] ?>%"></div>
                        </div>
                        <?php if ($pace['has_target']): ?>
                            <div class="small text-muted mb-2">
                                <i class="fa-regular fa-calendar me-1"></i><?= htmlspecialchars($g['target_date']) ?>
                                · <?= $pace['days_left'] >= 0 ? $pace['days_left'] . ' días restantes' : 'vencida' ?>
                                · esperado <?= $pace['expected_pct'] ?>%
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?= BASE_URL ?>?page=goals&action=show&id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-diagram-project me-1"></i>Ver recursos
                            </a>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary" onclick='editGoal(<?= json_encode($g, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Editar"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn btn-outline-danger" onclick="deleteGoal(<?= $g['id'] ?>)" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="modal fade" id="goalModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="goalModalTitle">Nueva meta</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div id="goalAlert"></div>
            <input type="hidden" id="goalId">
            <div class="mb-3"><label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                <input type="text" id="goalTitle" class="form-control" placeholder="ej. Inglés B2 para diciembre"></div>
            <div class="mb-3"><label class="form-label fw-semibold">Descripción</label>
                <textarea id="goalDesc" class="form-control" rows="2"></textarea></div>
            <div class="row g-2">
                <div class="col-7"><label class="form-label fw-semibold">Fecha objetivo</label><input type="date" id="goalDate" class="form-control"></div>
                <div class="col-5" id="goalStatusWrap" style="display:none"><label class="form-label fw-semibold">Estado</label>
                    <select id="goalStatus" class="form-select"><option value="active">Activa</option><option value="paused">Pausada</option><option value="done">Terminada</option></select></div>
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" onclick="saveGoal()"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button></div>
    </div></div>
</div>

<script>
function gPost(action, fields) {
    const b = new FormData(); b.append('_action', action); b.append('csrf_token', CSRF_TOKEN);
    for (const k in fields) b.append(k, fields[k]);
    return fetch(BASE_URL + '?page=goals', { method: 'POST', body: b }).then(r => r.json());
}
function resetGoal() {
    document.getElementById('goalId').value = ''; document.getElementById('goalTitle').value = '';
    document.getElementById('goalDesc').value = ''; document.getElementById('goalDate').value = '';
    document.getElementById('goalModalTitle').textContent = 'Nueva meta';
    document.getElementById('goalStatusWrap').style.display = 'none'; document.getElementById('goalAlert').innerHTML = '';
}
function editGoal(g) {
    document.getElementById('goalId').value = g.id; document.getElementById('goalTitle').value = g.title;
    document.getElementById('goalDesc').value = g.description || ''; document.getElementById('goalDate').value = g.target_date || '';
    document.getElementById('goalStatus').value = g.status; document.getElementById('goalStatusWrap').style.display = '';
    document.getElementById('goalModalTitle').textContent = 'Editar meta'; document.getElementById('goalAlert').innerHTML = '';
    new bootstrap.Modal(document.getElementById('goalModal')).show();
}
function saveGoal() {
    const id = document.getElementById('goalId').value;
    const title = document.getElementById('goalTitle').value.trim();
    if (!title) { document.getElementById('goalAlert').innerHTML = '<div class="alert alert-danger py-2">El título es obligatorio.</div>'; return; }
    const f = { title, description: document.getElementById('goalDesc').value.trim(), target_date: document.getElementById('goalDate').value };
    if (id) { f.id = id; f.status = document.getElementById('goalStatus').value; }
    gPost(id ? 'update' : 'store', f).then(d => { if (d.success) location.reload(); else document.getElementById('goalAlert').innerHTML = `<div class="alert alert-danger py-2">${d.message}</div>`; });
}
function deleteGoal(id) { if (!confirm('¿Eliminar esta meta? (no borra los recursos)')) return; gPost('destroy', { id }).then(d => { if (d.success) document.getElementById('goal-' + id)?.remove(); else alert(d.message); }); }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
