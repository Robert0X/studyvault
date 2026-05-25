<?php
$pageTitle = 'Meta';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-bullseye me-2"></i><?= htmlspecialchars($goal['title']) ?></h2>
        <p class="text-muted mb-0"><?= htmlspecialchars($goal['description'] ?: '') ?></p>
    </div>
    <a href="<?= BASE_URL ?>?page=goals" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Volver</a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="display-4 fw-bold text-primary"><?= $pct ?>%</div>
                <div class="text-muted mb-3">progreso de la meta</div>
                <div class="progress sv-progress mb-3"><div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div></div>
                <?php if ($pace['has_target']): ?>
                    <div class="alert alert-<?= $pace['on_track'] ? 'success' : 'warning' ?> py-2 mb-0">
                        <strong><?= htmlspecialchars($pace['label']) ?></strong><br>
                        <small>
                            Fecha: <?= htmlspecialchars($goal['target_date']) ?> ·
                            <?= $pace['days_left'] >= 0 ? $pace['days_left'] . ' días restantes' : 'vencida' ?><br>
                            Deberías ir al <?= $pace['expected_pct'] ?>% · vas al <?= $pct ?>%
                        </small>
                    </div>
                <?php else: ?>
                    <p class="text-muted small mb-0">Sin fecha límite. Edita la meta para fijar una y medir tu ritmo.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0"><i class="fa-solid fa-plus me-1"></i>Agregar recurso</h6></div>
            <div class="card-body">
                <?php if (empty($available)): ?>
                    <p class="text-muted small mb-0">No hay más recursos disponibles. Crea recursos primero.</p>
                <?php else: ?>
                    <div class="input-group">
                        <select id="resSelect" class="form-select">
                            <?php foreach ($available as $r): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['title']) ?></option><?php endforeach; ?>
                        </select>
                        <input type="number" id="resWeight" class="form-control" value="1" min="1" style="max-width:80px" title="Peso">
                        <button class="btn btn-primary" onclick="attachRes()">Agregar</button>
                    </div>
                    <small class="text-muted">El <strong>peso</strong> indica cuánto aporta el recurso al progreso de la meta.</small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pt-3"><h6 class="mb-0"><i class="fa-solid fa-diagram-project me-1"></i>Recursos de la meta</h6></div>
            <div class="card-body p-0">
                <?php if (empty($resources)): ?>
                    <div class="text-center py-4 text-muted">Sin recursos. Agrega algunos a la izquierda.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush" id="resList">
                        <?php foreach ($resources as $r): ?>
                            <li class="list-group-item" id="gres-<?= $r['id'] ?>">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold"><?= htmlspecialchars($r['title']) ?></span>
                                    <span>
                                        <span class="badge bg-light text-dark border me-1" title="Peso">×<?= $r['weight'] ?></span>
                                        <span class="text-muted small me-2"><?= $r['pct'] ?>%</span>
                                        <button class="btn btn-sm btn-outline-danger" onclick="detachRes(<?= $r['id'] ?>)"><i class="fa-solid fa-xmark"></i></button>
                                    </span>
                                </div>
                                <div class="progress sv-progress"><div class="progress-bar" style="width:<?= $r['pct'] ?>%"></div></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const GID = <?= (int)$goal['id'] ?>;
function gPost(action, fields) {
    const b = new FormData(); b.append('_action', action); b.append('csrf_token', CSRF_TOKEN); b.append('goal_id', GID);
    for (const k in fields) b.append(k, fields[k]);
    return fetch(BASE_URL + '?page=goals', { method: 'POST', body: b }).then(r => r.json());
}
function attachRes() {
    gPost('attach', { resource_id: document.getElementById('resSelect').value, weight: document.getElementById('resWeight').value })
        .then(d => { if (d.success) location.reload(); else alert(d.message); });
}
function detachRes(id) { gPost('detach', { resource_id: id }).then(d => { if (d.success) location.reload(); else alert(d.message); }); }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
