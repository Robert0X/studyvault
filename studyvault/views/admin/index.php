<?php
$pageTitle = 'Panel administrativo';
require __DIR__ . '/../partials/header.php';
$selfId = (int) ($_SESSION['user_id'] ?? 0);
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-user-shield me-2"></i>Panel administrativo</h2>
        <p class="text-muted mb-0">Gestión de usuarios y uso global de la plataforma</p>
    </div>
</div>

<!-- Totales globales -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['users',          'Usuarios totales',  'fa-users',         'primary'],
        ['active_users',   'Usuarios activos',  'fa-user-check',    'success'],
        ['subjects',       'Materias',          'fa-layer-group',   'info'],
        ['resources',      'Recursos',          'fa-box-archive',   'warning'],
        ['flashcards',     'Flashcards',        'fa-clone',         'danger'],
        ['cp_problems',    'Problemas CP',      'fa-trophy',        'dark'],
        ['goals',          'Metas',             'fa-bullseye',      'secondary'],
        ['total_minutes',  'Minutos estudiados','fa-clock',         'primary'],
    ];
    foreach ($cards as [$key, $label, $icon, $color]):
    ?>
        <div class="col-6 col-md-3">
            <div class="card sv-stat-card border-0 h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="sv-stat-icon bg-<?= $color ?> bg-opacity-15 text-<?= $color ?>"><i class="fa-solid <?= $icon ?>"></i></div>
                <div>
                    <div class="sv-stat-number"><?= number_format((int) ($stats[$key] ?? 0)) ?></div>
                    <div class="sv-stat-label"><?= $label ?></div>
                </div>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Lista de usuarios -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0"><i class="fa-solid fa-users-gear me-2 text-primary"></i>Usuarios (<?= (int) $total ?>)</h5>
        <form method="GET" class="d-flex gap-2" action="<?= BASE_URL ?>">
            <input type="hidden" name="page" value="admin">
            <input type="search" name="q" class="form-control form-control-sm" placeholder="Buscar por nombre o correo"
                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
    <div class="card-body p-0">
        <?php if (empty($users)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-user-slash fa-2x mb-2"></i>
                <p class="mb-0">No hay usuarios que coincidan.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="adminUsersTable" data-sv-sortable data-sv-sort-only>
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Registrado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr id="userrow-<?= (int) $u['id'] ?>">
                                <td class="small text-muted"><?= (int) $u['id'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($u['name']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge bg-warning text-dark">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-15 text-secondary">Estudiante</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int) $u['active'] === 1): ?>
                                        <span class="badge bg-success bg-opacity-15 text-success" data-state="active"><i class="fa-solid fa-circle-check me-1"></i>Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-15 text-danger" data-state="inactive"><i class="fa-solid fa-ban me-1"></i>Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?= htmlspecialchars($u['created_at']) ?></td>
                                <td class="text-end">
                                    <?php if ((int) $u['id'] === $selfId): ?>
                                        <span class="badge bg-light text-muted">Tú</span>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-<?= (int) $u['active'] === 1 ? 'danger' : 'success' ?>"
                                                onclick="adminToggle(<?= (int) $u['id'] ?>, this)">
                                            <i class="fa-solid <?= (int) $u['active'] === 1 ? 'fa-user-slash' : 'fa-user-check' ?> me-1"></i>
                                            <?= (int) $u['active'] === 1 ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (($pages ?? 1) > 1): $qs = $_GET; ?>
                <nav class="p-3"><ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $pages; $i++): $qs['pg'] = $i; ?>
                        <li class="page-item <?= $i == $pageNum ? 'active' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>?<?= htmlspecialchars(http_build_query($qs)) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul></nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function adminToggle(id, btn) {
    btn.disabled = true;
    const body = new FormData();
    body.append('_action', 'toggle_active');
    body.append('csrf_token', CSRF_TOKEN);
    body.append('id', id);
    fetch(BASE_URL + '?page=admin', { method: 'POST', body })
        .then(r => r.json())
        .then(d => {
            btn.disabled = false;
            if (!d.success) { alert(d.message || 'Error'); return; }
            const row = document.getElementById('userrow-' + id);
            const badge = row.querySelector('td:nth-child(5) .badge');
            if (d.active) {
                badge.className = 'badge bg-success bg-opacity-15 text-success';
                badge.dataset.state = 'active';
                badge.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i>Activo';
                btn.className = 'btn btn-sm btn-outline-danger';
                btn.innerHTML = '<i class="fa-solid fa-user-slash me-1"></i>Desactivar';
            } else {
                badge.className = 'badge bg-danger bg-opacity-15 text-danger';
                badge.dataset.state = 'inactive';
                badge.innerHTML = '<i class="fa-solid fa-ban me-1"></i>Inactivo';
                btn.className = 'btn btn-sm btn-outline-success';
                btn.innerHTML = '<i class="fa-solid fa-user-check me-1"></i>Activar';
            }
        });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
