<?php
$pageTitle = 'Unidades';
require __DIR__ . '/../partials/header.php';
$statusInfo = ['pending' => ['Pendiente', 'warning'], 'in_progress' => ['En progreso', 'info'], 'completed' => ['Completado', 'success']];
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-list-check me-2"></i>Unidades</h2>
        <p class="text-muted mb-0"><?= htmlspecialchars($resource['title']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>?page=resources" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Volver</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-1">
            <span class="fw-semibold">Progreso del recurso</span>
            <span class="text-muted"><?= $progress['completed'] ?>/<?= $progress['total'] ?> (<?= $progress['pct'] ?>%)</span>
        </div>
        <div class="progress sv-progress"><div class="progress-bar bg-success" style="width:<?= $progress['pct'] ?>%"></div></div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-semibold"><i class="fa-solid fa-plus me-1"></i>Agregar unidad</h6>
            <div class="input-group">
                <input type="text" id="uTitle" class="form-control" placeholder="ej. Capítulo 1: Present Simple">
                <button class="btn btn-primary" onclick="addUnit()">Agregar</button>
            </div>
            <div class="row g-2 mt-1">
                <div class="col"><input type="number" id="uFrom" class="form-control form-control-sm" placeholder="Pág. desde"></div>
                <div class="col"><input type="number" id="uTo" class="form-control form-control-sm" placeholder="Pág. hasta"></div>
            </div>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-semibold"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Generar varias</h6>
            <div class="input-group">
                <input type="text" id="bPrefix" class="form-control" value="Capítulo">
                <input type="number" id="bCount" class="form-control" placeholder="N" style="max-width:90px">
                <button class="btn btn-outline-primary" onclick="bulkUnits()">Generar</button>
            </div>
            <small class="text-muted">Crea N unidades numeradas de golpe (ideal para un libro grande).</small>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm"><div class="card-body p-0">
    <?php if (empty($units)): ?>
        <div class="text-center py-5 text-muted"><i class="fa-solid fa-list fa-2x mb-2 opacity-50"></i><p>Sin unidades. Agrega o genera capítulos arriba.</p></div>
    <?php else: ?>
        <ul class="list-group list-group-flush">
            <?php foreach ($units as $u): [$sl, $sc] = $statusInfo[$u['status']] ?? ['—', 'secondary']; ?>
                <li class="list-group-item d-flex align-items-center gap-2" id="unit-<?= $u['id'] ?>">
                    <span class="text-muted small"><?= (int)$u['order_index'] ?>.</span>
                    <div class="flex-grow-1">
                        <span class="fw-semibold"><?= htmlspecialchars($u['title']) ?></span>
                        <?php if ($u['page_from'] || $u['page_to']): ?>
                            <small class="text-muted ms-2">págs. <?= (int)$u['page_from'] ?>–<?= (int)$u['page_to'] ?></small>
                        <?php endif; ?>
                    </div>
                    <select class="form-select form-select-sm w-auto" onchange="setUnitStatus(<?= $u['id'] ?>, this.value)">
                        <?php foreach ($statusInfo as $sv => $si): ?>
                            <option value="<?= $sv ?>" <?= $u['status'] === $sv ? 'selected' : '' ?>><?= $si[0] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-outline-danger" onclick="delUnit(<?= $u['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div></div>

<script>
const RID = <?= (int)$resource['id'] ?>;
function uPost(action, fields) {
    const b = new FormData();
    b.append('_action', action); b.append('csrf_token', CSRF_TOKEN);
    for (const k in fields) b.append(k, fields[k]);
    return fetch(BASE_URL + '?page=units', { method: 'POST', body: b }).then(r => r.json());
}
function addUnit() {
    const t = document.getElementById('uTitle').value.trim();
    if (!t) { alert('El título es obligatorio'); return; }
    uPost('store', { resource_id: RID, title: t, page_from: document.getElementById('uFrom').value, page_to: document.getElementById('uTo').value })
        .then(d => { if (d.success) location.reload(); else alert(d.message); });
}
function bulkUnits() {
    const c = parseInt(document.getElementById('bCount').value);
    if (!c) { alert('Indica cuántas unidades'); return; }
    uPost('bulk', { resource_id: RID, count: c, prefix: document.getElementById('bPrefix').value })
        .then(d => { if (d.success) location.reload(); else alert(d.message); });
}
function setUnitStatus(id, status) { uPost('status', { id, status }).then(() => location.reload()); }
function delUnit(id) { if (!confirm('¿Eliminar esta unidad?')) return; uPost('destroy', { id }).then(d => { if (d.success) location.reload(); else alert(d.message); }); }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
