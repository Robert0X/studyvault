<?php
$pageTitle = 'Recursos';
require __DIR__ . '/../partials/header.php';

$typeIcons = ['link' => 'fa-link', 'pdf' => 'fa-file-pdf', 'note' => 'fa-sticky-note', 'video' => 'fa-video'];
$typeLabels = ['link' => 'Enlace', 'pdf' => 'PDF/Archivo', 'note' => 'Nota', 'video' => 'Video'];
$statusBadge = [
    'pending'     => ['label' => 'Pendiente',   'class' => 'warning'],
    'in_progress' => ['label' => 'En progreso', 'class' => 'info'],
    'completed'   => ['label' => 'Completado',  'class' => 'success'],
];
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-box-archive me-2"></i>Recursos</h2>
        <p class="text-muted mb-0"><?= (int)($total ?? count($resources)) ?> recursos · página <?= (int)($pageNum ?? 1) ?> de <?= (int)($pages ?? 1) ?></p>
    </div>
    <a href="<?= BASE_URL ?>?page=resources&action=create" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i>Nuevo recurso
    </a>
</div>

<?php if (!empty($_GET['saved'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-1"></i>Recurso guardado correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filtros AJAX -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small text-muted mb-1">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar por título o descripción..."
                           value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small text-muted mb-1">Materia</label>
                <select id="filterSubject" class="form-select">
                    <option value="">Todas las materias</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>"
                            <?= (isset($_GET['subject']) && $_GET['subject'] == $s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Tipo</label>
                <select id="filterType" class="form-select">
                    <option value="">Todos</option>
                    <option value="link"  <?= ($_GET['type'] ?? '') === 'link'  ? 'selected' : '' ?>>Enlace</option>
                    <option value="pdf"   <?= ($_GET['type'] ?? '') === 'pdf'   ? 'selected' : '' ?>>PDF</option>
                    <option value="note"  <?= ($_GET['type'] ?? '') === 'note'  ? 'selected' : '' ?>>Nota</option>
                    <option value="video" <?= ($_GET['type'] ?? '') === 'video' ? 'selected' : '' ?>>Video</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">Estado</label>
                <select id="filterStatus" class="form-select">
                    <option value="">Todos</option>
                    <option value="pending"     <?= ($_GET['status'] ?? '') === 'pending'     ? 'selected' : '' ?>>Pendiente</option>
                    <option value="in_progress" <?= ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>En progreso</option>
                    <option value="completed"   <?= ($_GET['status'] ?? '') === 'completed'   ? 'selected' : '' ?>>Completado</option>
                </select>
            </div>
            <div class="col-6 col-md-1">
                <button class="btn btn-outline-secondary w-100" onclick="clearFilters()" title="Limpiar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de recursos -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div id="resourcesLoading" class="text-center py-4 d-none">
            <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div id="resourcesContainer">
            <?php if (empty($resources)): ?>
                <div class="text-center py-5 text-muted" id="emptyState">
                    <i class="fa-solid fa-box-open fa-3x mb-3 opacity-50"></i>
                    <h5>No hay recursos</h5>
                    <a href="<?= BASE_URL ?>?page=resources&action=create" class="btn btn-primary mt-2">
                        <i class="fa-solid fa-plus me-1"></i>Añadir recurso
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="resourcesTable" data-sv-sortable data-sv-sort-only>
                        <thead class="table-light">
                            <tr>
                                <th>Recurso</th>
                                <th class="d-none d-md-table-cell">Materia</th>
                                <th class="d-none d-sm-table-cell">Tipo</th>
                                <th>Estado</th>
                                <th class="text-end" data-sv-no-sort>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="resourcesBody">
                            <?php foreach ($resources as $r): ?>
                                <?php include __DIR__ . '/row.php'; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (($pages ?? 1) > 1): $qs = $_GET; ?>
                    <nav class="p-3"><ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $pages; $i++): $qs['pg'] = $i; ?>
                            <li class="page-item <?= $i == ($pageNum ?? 1) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>?<?= htmlspecialchars(http_build_query($qs)) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul></nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let searchTimer;

function doSearch() {
    const q         = document.getElementById('searchInput').value;
    const subjectId = document.getElementById('filterSubject').value;
    const type      = document.getElementById('filterType').value;
    const status    = document.getElementById('filterStatus').value;

    const params = new URLSearchParams({ q, subject_id: subjectId, type, status });

    document.getElementById('resourcesLoading').classList.remove('d-none');
    document.getElementById('resourcesContainer').style.opacity = '0.5';

    fetch(BASE_URL + 'api/search.php?' + params.toString())
        .then(r => r.json())
        .then(data => {
            document.getElementById('resourcesLoading').classList.add('d-none');
            document.getElementById('resourcesContainer').style.opacity = '1';

            if (!data.success) return;

            const container = document.getElementById('resourcesContainer');

            if (data.count === 0) {
                container.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-magnifying-glass fa-3x mb-3 opacity-50"></i>
                        <h5>Sin resultados</h5>
                        <p>Prueba con otros filtros.</p>
                    </div>`;
                return;
            }

            // Las filas las renderiza el servidor con la MISMA plantilla (row.php) → una sola fuente de verdad.
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" data-sv-sortable data-sv-sort-only>
                        <thead class="table-light">
                            <tr>
                                <th>Recurso</th>
                                <th class="d-none d-md-table-cell">Materia</th>
                                <th class="d-none d-sm-table-cell">Tipo</th>
                                <th>Estado</th>
                                <th class="text-end" data-sv-no-sort>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>${data.html}</tbody>
                    </table>
                </div>`;
            // Re-aplica ordenamiento DataTables sobre el nuevo tbody.
            if (typeof svInitDataTables === 'function') svInitDataTables();
        });
}

document.getElementById('searchInput').addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(doSearch, 300);
});
document.getElementById('filterSubject').addEventListener('change', doSearch);
document.getElementById('filterType').addEventListener('change', doSearch);
document.getElementById('filterStatus').addEventListener('change', doSearch);

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterSubject').value = '';
    document.getElementById('filterType').value = '';
    document.getElementById('filterStatus').value = '';
    doSearch();
}

function changeStatus(id, status) {
    const body = new FormData();
    body.append('csrf_token', CSRF_TOKEN);
    body.append('id', id);
    body.append('status', status);
    fetch(BASE_URL + 'api/resource_status.php', { method: 'POST', body });
}

function deleteResource(id) {
    if (!confirm('¿Eliminar este recurso?')) return;
    const body = new FormData();
    body.append('_action', 'destroy');
    body.append('csrf_token', CSRF_TOKEN);
    body.append('id', id);
    fetch(BASE_URL + '?page=resources', { method: 'POST', body })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('row-' + id)?.remove();
            } else {
                alert(data.message);
            }
        });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
