<?php
$pageTitle = 'Reporte semanal';
require __DIR__ . '/../partials/header.php';
$delta = $week - $prevWeek;
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-chart-line me-2"></i>Reporte semanal</h2>
        <p class="text-muted mb-0">Tu actividad de los últimos 7 días</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="fa-solid fa-file-pdf me-1"></i>Exportar PDF
        </button>
        <a href="<?= BASE_URL ?>?page=dashboard" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card sv-stat-card border-0 h-100"><div class="card-body">
        <div class="sv-stat-number"><?= (int)$week ?> <small class="fs-6 text-muted">min</small></div>
        <div class="sv-stat-label">Estudio esta semana</div>
        <div class="small mt-1 <?= $delta >= 0 ? 'text-success' : 'text-danger' ?>">
            <i class="fa-solid fa-arrow-<?= $delta >= 0 ? 'up' : 'down' ?>"></i> <?= abs($delta) ?> min vs semana previa
        </div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card sv-stat-card border-0 h-100"><div class="card-body">
        <div class="sv-stat-number"><?= (int)$reviewed ?></div><div class="sv-stat-label">Tarjetas repasadas</div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card sv-stat-card border-0 h-100"><div class="card-body">
        <div class="sv-stat-number"><?= (int)$solved ?></div><div class="sv-stat-label">Problemas resueltos</div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card sv-stat-card border-0 h-100"><div class="card-body">
        <div class="sv-stat-number"><i class="fa-solid fa-fire text-warning"></i> <?= (int)$streak ?></div><div class="sv-stat-label">Días de racha</div>
    </div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-chart-column me-2 text-primary"></i>Minutos por día (7 días)</h6>
            <?php
            $maxM = 1;
            $days7 = [];
            for ($i = 6; $i >= 0; $i--) { $d = date('Y-m-d', strtotime("-$i day")); $m = (int)($byDay[$d] ?? 0); $days7[$d] = $m; $maxM = max($maxM, $m); }
            foreach ($days7 as $d => $m): ?>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="small text-muted" style="width:42px"><?= date('D', strtotime($d)) ?></span>
                    <div class="progress flex-grow-1 sv-progress"><div class="progress-bar bg-primary" style="width:<?= round($m / $maxM * 100) ?>%"></div></div>
                    <span class="small text-muted" style="width:48px"><?= $m ?> min</span>
                </div>
            <?php endforeach; ?>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-bullseye me-2 text-primary"></i>Estado de tus metas</h6>
            <?php if (empty($goals)): ?>
                <p class="text-muted small mb-0">Sin metas. <a href="<?= BASE_URL ?>?page=goals">Crea una</a>.</p>
            <?php else: foreach ($goals as $g): ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold"><?= htmlspecialchars($g['title']) ?></span>
                        <span><?= (int)$g['pct'] ?>%<?php if ($g['pace']['has_target']): ?> <span class="badge bg-<?= $g['pace']['on_track'] ? 'success' : 'danger' ?> bg-opacity-15 text-<?= $g['pace']['on_track'] ? 'success' : 'danger' ?>"><?= htmlspecialchars($g['pace']['label']) ?></span><?php endif; ?></span>
                    </div>
                    <div class="progress sv-progress"><div class="progress-bar bg-primary" style="width:<?= (int)$g['pct'] ?>%"></div></div>
                </div>
            <?php endforeach; endif; ?>
        </div></div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
