<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-gauge-high me-2"></i>Dashboard</h2>
        <p class="text-muted mb-0">Bienvenido, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
    </div>
    <a href="<?= BASE_URL ?>?page=resources&action=create" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i>Nuevo recurso
    </a>
</div>

<!-- Stats cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card sv-stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="sv-stat-icon bg-primary bg-opacity-15 text-primary">
                    <i class="fa-solid fa-box-archive"></i>
                </div>
                <div>
                    <div class="sv-stat-number"><?= (int)($stats['total'] ?? 0) ?></div>
                    <div class="sv-stat-label">Total recursos</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card sv-stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="sv-stat-icon bg-success bg-opacity-15 text-success">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div class="sv-stat-number"><?= (int)($stats['completed'] ?? 0) ?></div>
                    <div class="sv-stat-label">Completados</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card sv-stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="sv-stat-icon bg-info bg-opacity-15 text-info">
                    <i class="fa-solid fa-spinner"></i>
                </div>
                <div>
                    <div class="sv-stat-number"><?= (int)($stats['in_progress'] ?? 0) ?></div>
                    <div class="sv-stat-label">En progreso</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card sv-stat-card border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="sv-stat-icon bg-warning bg-opacity-15 text-warning">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <div class="sv-stat-number"><?= (int)($stats['pending'] ?? 0) ?></div>
                    <div class="sv-stat-label">Pendientes</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Progreso por materia -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                <h5 class="mb-0"><i class="fa-solid fa-chart-bar me-2 text-primary"></i>Progreso por materia</h5>
                <a href="<?= BASE_URL ?>?page=subjects" class="btn btn-sm btn-outline-primary">Ver todas</a>
            </div>
            <div class="card-body">
                <?php if (empty($subjectStats)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-layer-group fa-2x mb-2"></i>
                        <p>Aún no tienes materias. <a href="<?= BASE_URL ?>?page=subjects">Crea una</a></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($subjectStats as $s): ?>
                        <?php
                        $total     = (int)($s['total_resources'] ?? 0);
                        $completed = (int)($s['completed'] ?? 0);
                        $pct       = $total > 0 ? round($completed / $total * 100) : 0;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold">
                                    <i class="fa-solid <?= htmlspecialchars($s['icon']) ?> me-1"
                                       style="color:<?= htmlspecialchars($s['color']) ?>"></i>
                                    <?= htmlspecialchars($s['name']) ?>
                                </span>
                                <small class="text-muted"><?= $completed ?>/<?= $total ?> (<?= $pct ?>%)</small>
                            </div>
                            <div class="progress sv-progress">
                                <div class="progress-bar" role="progressbar"
                                     style="width:<?= $pct ?>%; background-color:<?= htmlspecialchars($s['color']) ?>"
                                     aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-1">
                                <span class="badge bg-success bg-opacity-15 text-success small">
                                    ✓ <?= $completed ?> completados
                                </span>
                                <span class="badge bg-info bg-opacity-15 text-info small">
                                    ⟳ <?= (int)($s['in_progress'] ?? 0) ?> en progreso
                                </span>
                                <span class="badge bg-warning bg-opacity-15 text-warning small">
                                    ○ <?= (int)($s['pending'] ?? 0) ?> pendientes
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recursos recientes -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3">
                <h5 class="mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Añadidos recientemente</h5>
                <a href="<?= BASE_URL ?>?page=resources" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-box-open fa-2x mb-2"></i>
                        <p class="px-3">Aún no tienes recursos. <a href="<?= BASE_URL ?>?page=resources&action=create">Añade uno</a></p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php
                        $typeIcons = ['link' => 'fa-link', 'pdf' => 'fa-file-pdf', 'note' => 'fa-sticky-note', 'video' => 'fa-video'];
                        $statusClasses = ['pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success'];
                        foreach ($recent as $r):
                        ?>
                            <li class="list-group-item px-3 py-2 d-flex align-items-center gap-2">
                                <div class="sv-resource-icon" style="background-color:<?= htmlspecialchars($r['subject_color']) ?>20; color:<?= htmlspecialchars($r['subject_color']) ?>">
                                    <i class="fa-solid <?= $typeIcons[$r['type']] ?? 'fa-file' ?>"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate"><?= htmlspecialchars($r['title']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($r['subject_name']) ?></small>
                                </div>
                                <span class="badge bg-<?= $statusClasses[$r['status']] ?> bg-opacity-15 text-<?= $statusClasses[$r['status']] ?> ms-auto">
                                    <?= match($r['status']) { 'completed' => '✓', 'in_progress' => '⟳', default => '○' } ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
