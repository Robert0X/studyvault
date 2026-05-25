<?php
$pageTitle = 'Competitiva';
require __DIR__ . '/../partials/header.php';

$statusInfo = [
    'todo'      => ['Por hacer',  'secondary'],
    'attempted' => ['Intentado',  'warning'],
    'solved'    => ['Resuelto',   'success'],
    'upsolved'  => ['Upsolved',   'info'],
];
$handle = $cf['cf_handle'] ?? '';
$rating = $cf['cf_rating'] ?? null;
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-trophy me-2"></i>Programación Competitiva</h2>
        <p class="text-muted mb-0">Sincroniza tu progreso real desde Codeforces</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#problemModal">
        <i class="fa-solid fa-plus me-1"></i>Agregar problema
    </button>
</div>

<!-- Panel Codeforces -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small text-muted mb-1">Handle de Codeforces</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-brands fa-codeforces"></i></span>
                    <input type="text" id="cfHandle" class="form-control" value="<?= htmlspecialchars($handle) ?>" placeholder="ej. tourist">
                    <button class="btn btn-outline-secondary" onclick="saveHandle()">Guardar</button>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-success w-100" id="syncBtn" onclick="syncCf()">
                    <i class="fa-solid fa-rotate me-1"></i>Sincronizar
                </button>
            </div>
            <div class="col-6 col-md-4 text-md-end">
                <div class="small text-muted">Rating actual</div>
                <div class="h3 mb-0" id="cfRating"><?= $rating !== null ? (int)$rating : '—' ?></div>
            </div>
        </div>
        <div id="syncResult" class="mt-2"></div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php foreach ([
        ['solved',    'Resueltos',  'fa-circle-check', 'success'],
        ['total',     'Rastreados', 'fa-list-check',   'primary'],
        ['attempted', 'Intentados', 'fa-hourglass',    'warning'],
        ['todo',      'Por hacer',  'fa-clipboard',    'secondary'],
    ] as [$k, $label, $icon, $color]): ?>
        <div class="col-6 col-lg-3">
            <div class="card sv-stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="sv-stat-icon bg-<?= $color ?> bg-opacity-15 text-<?= $color ?>"><i class="fa-solid <?= $icon ?>"></i></div>
                    <div>
                        <div class="sv-stat-number"><?= (int)($stats[$k] ?? 0) ?></div>
                        <div class="sv-stat-label"><?= $label ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <!-- Problemas -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($problems)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-trophy fa-3x mb-3 opacity-50"></i>
                        <h5>Sin problemas todavía</h5>
                        <p>Configura tu handle y pulsa <strong>Sincronizar</strong>, o agrega uno manualmente.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Problema</th>
                                    <th class="d-none d-md-table-cell">Rating</th>
                                    <th class="d-none d-lg-table-cell">Tags</th>
                                    <th>Estado</th>
                                    <th class="text-end">—</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($problems as $p):
                                    [$slabel, $sclass] = $statusInfo[$p['status']] ?? ['—', 'secondary']; ?>
                                    <tr id="prob-<?= $p['id'] ?>">
                                        <td>
                                            <?php if (!empty($p['problem_url'])): ?>
                                                <a href="<?= htmlspecialchars($p['problem_url']) ?>" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">
                                                    <?= htmlspecialchars($p['name']) ?> <i class="fa-solid fa-arrow-up-right-from-square fa-xs text-muted"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="fw-semibold"><?= htmlspecialchars($p['name']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($p['editorial_note'])): ?>
                                                <div class="small text-muted"><?= htmlspecialchars(mb_substr($p['editorial_note'], 0, 70)) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <?php if ($p['rating']): ?><span class="badge bg-dark"><?= (int)$p['rating'] ?></span><?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <?php foreach (array_slice(array_filter(array_map('trim', explode(',', $p['tags'] ?? ''))), 0, 3) as $t): ?>
                                                <span class="badge bg-secondary bg-opacity-15 text-secondary"><?= htmlspecialchars($t) ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm w-auto" onchange="setStatus(<?= $p['id'] ?>, this.value)">
                                                <?php foreach ($statusInfo as $sv => $si): ?>
                                                    <option value="<?= $sv ?>" <?= $p['status'] === $sv ? 'selected' : '' ?>><?= $si[0] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteProblem(<?= $p['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Debilidades por tag -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3">
                <h5 class="mb-0"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Resueltos por tema</h5>
            </div>
            <div class="card-body">
                <?php if (empty($tags)): ?>
                    <p class="text-muted small">Sincroniza para ver tus temas más resueltos.</p>
                <?php else: $max = max($tags); foreach ($tags as $tag => $count): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span><?= htmlspecialchars($tag) ?></span><span class="text-muted"><?= $count ?></span>
                        </div>
                        <div class="progress sv-progress"><div class="progress-bar bg-primary" style="width:<?= round($count / $max * 100) ?>%"></div></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal agregar problema -->
<div class="modal fade" id="problemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Agregar problema</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div id="probAlert"></div>
                <div class="mb-3"><label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label><input type="text" id="probName" class="form-control"></div>
                <div class="mb-3"><label class="form-label fw-semibold">URL</label><input type="url" id="probUrl" class="form-control" placeholder="https://codeforces.com/..."></div>
                <div class="row g-2">
                    <div class="col-6"><label class="form-label fw-semibold">Rating</label><input type="number" id="probRating" class="form-control" placeholder="1500"></div>
                    <div class="col-6"><label class="form-label fw-semibold">Estado</label>
                        <select id="probStatus" class="form-select">
                            <option value="todo">Por hacer</option><option value="attempted">Intentado</option>
                            <option value="solved">Resuelto</option><option value="upsolved">Upsolved</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 mt-2"><label class="form-label fw-semibold">Tags (separados por coma)</label><input type="text" id="probTags" class="form-control" placeholder="dp, graphs"></div>
                <div class="mb-2"><label class="form-label fw-semibold">Nota / idea clave</label><textarea id="probNote" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" onclick="saveProblem()"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
function cpPost(action, fields) {
    const body = new FormData();
    body.append('_action', action);
    body.append('csrf_token', CSRF_TOKEN);
    for (const k in fields) body.append(k, fields[k]);
    return fetch(BASE_URL + '?page=cp', { method: 'POST', body }).then(r => r.json());
}

function saveHandle() {
    cpPost('save_handle', { cf_handle: document.getElementById('cfHandle').value.trim() })
        .then(d => { document.getElementById('syncResult').innerHTML =
            `<div class="alert alert-${d.success ? 'success' : 'danger'} py-1 px-2 small mb-0">${d.message}</div>`; });
}

function syncCf() {
    const btn = document.getElementById('syncBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sincronizando...';
    cpPost('sync', {}).then(d => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-rotate me-1"></i>Sincronizar';
        document.getElementById('syncResult').innerHTML =
            `<div class="alert alert-${d.success ? 'success' : 'danger'} py-1 px-2 small mb-0">${d.message}</div>`;
        if (d.success) {
            if (d.rating != null) document.getElementById('cfRating').textContent = d.rating;
            setTimeout(() => location.reload(), 1200);
        }
    });
}

function saveProblem() {
    const name = document.getElementById('probName').value.trim();
    if (!name) { document.getElementById('probAlert').innerHTML = '<div class="alert alert-danger py-2">El nombre es obligatorio.</div>'; return; }
    cpPost('store', {
        name, problem_url: document.getElementById('probUrl').value.trim(),
        rating: document.getElementById('probRating').value,
        tags: document.getElementById('probTags').value.trim(),
        status: document.getElementById('probStatus').value,
        editorial_note: document.getElementById('probNote').value.trim(),
    }).then(d => { if (d.success) location.reload(); else document.getElementById('probAlert').innerHTML = `<div class="alert alert-danger py-2">${d.message}</div>`; });
}

function setStatus(id, status) { cpPost('status', { id, status }); }
function deleteProblem(id) {
    if (!confirm('¿Eliminar este problema?')) return;
    cpPost('destroy', { id }).then(d => { if (d.success) document.getElementById('prob-' + id)?.remove(); });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
