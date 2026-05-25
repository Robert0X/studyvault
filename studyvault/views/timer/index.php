<?php
$pageTitle = 'Temporizador';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-clock me-2"></i>Temporizador de estudio</h2>
        <p class="text-muted mb-0">Pomodoro 25/5 con registro real de tiempo</p>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4"><div class="card sv-stat-card border-0 h-100"><div class="card-body d-flex align-items-center gap-3">
        <div class="sv-stat-icon bg-primary bg-opacity-15 text-primary"><i class="fa-solid fa-hourglass-half"></i></div>
        <div><div class="sv-stat-number" id="statToday"><?= (int)$today ?></div><div class="sv-stat-label">min hoy</div></div>
    </div></div></div>
    <div class="col-6 col-lg-4"><div class="card sv-stat-card border-0 h-100"><div class="card-body d-flex align-items-center gap-3">
        <div class="sv-stat-icon bg-info bg-opacity-15 text-info"><i class="fa-solid fa-calendar-week"></i></div>
        <div><div class="sv-stat-number"><?= (int)$week ?></div><div class="sv-stat-label">min últimos 7 días</div></div>
    </div></div></div>
    <div class="col-12 col-lg-4"><div class="card sv-stat-card border-0 h-100"><div class="card-body d-flex align-items-center gap-3">
        <div class="sv-stat-icon bg-warning bg-opacity-15 text-warning"><i class="fa-solid fa-fire"></i></div>
        <div><div class="sv-stat-number" id="statStreak"><?= (int)$streak ?></div><div class="sv-stat-label">días de racha</div></div>
    </div></div></div>
</div>

<div class="row g-4">
    <!-- Pomodoro -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm text-center"><div class="card-body p-4">
            <div class="text-muted mb-2" id="pomoPhase">Trabajo</div>
            <div class="display-1 fw-bold" id="pomoDisplay">25:00</div>
            <div class="my-3">
                <button class="btn btn-success px-4" id="pomoStart" onclick="pomoToggle()"><i class="fa-solid fa-play me-1"></i>Iniciar</button>
                <button class="btn btn-outline-secondary" onclick="pomoReset()"><i class="fa-solid fa-rotate-left"></i></button>
            </div>
            <div class="text-start">
                <label class="form-label small text-muted mb-1">¿Qué estás estudiando? (opcional)</label>
                <select id="pomoResource" class="form-select form-select-sm">
                    <option value="0">— Sin recurso —</option>
                    <?php foreach ($resources as $r): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['title']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <hr>
            <div class="d-flex justify-content-center align-items-center gap-2">
                <span class="small text-muted">Registrar manual:</span>
                <input type="number" id="manualMin" class="form-control form-control-sm" value="25" style="max-width:80px">
                <span class="small text-muted">min</span>
                <button class="btn btn-sm btn-outline-primary" onclick="logManual()">Registrar</button>
            </div>
        </div></div>
    </div>

    <!-- Heatmap + recientes -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-3"><div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-table-cells me-1"></i>Consistencia (últimos 84 días)</h6>
            <div class="d-flex flex-wrap" style="gap:3px">
                <?php
                for ($i = 83; $i >= 0; $i--):
                    $d = date('Y-m-d', strtotime("-$i day"));
                    $m = (int)($byDay[$d] ?? 0);
                    $bg = $m === 0 ? '#e9ecef' : ($m < 25 ? '#a5d6a7' : ($m < 60 ? '#66bb6a' : '#2e7d32'));
                ?>
                    <div title="<?= $d ?>: <?= $m ?> min" style="width:13px;height:13px;border-radius:3px;background:<?= $bg ?>"></div>
                <?php endfor; ?>
            </div>
            <div class="small text-muted mt-2">Menos <span style="display:inline-block;width:11px;height:11px;background:#e9ecef;border-radius:2px"></span>
                <span style="display:inline-block;width:11px;height:11px;background:#a5d6a7;border-radius:2px"></span>
                <span style="display:inline-block;width:11px;height:11px;background:#66bb6a;border-radius:2px"></span>
                <span style="display:inline-block;width:11px;height:11px;background:#2e7d32;border-radius:2px"></span> Más</div>
        </div></div>

        <div class="card border-0 shadow-sm"><div class="card-body">
            <h6 class="fw-semibold mb-2"><i class="fa-solid fa-clock-rotate-left me-1"></i>Sesiones recientes</h6>
            <?php if (empty($recent)): ?>
                <p class="text-muted small mb-0">Aún no registras tiempo. Usa el Pomodoro.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush" id="recentList">
                    <?php foreach ($recent as $s): ?>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span><?= htmlspecialchars($s['resource_title'] ?? 'Estudio general') ?></span>
                            <span class="text-muted small"><?= (int)$s['minutes'] ?> min · <?= htmlspecialchars(substr($s['started_at'], 0, 16)) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<script>
let pomoSecs = 25 * 60, pomoTimer = null, pomoRunning = false, pomoWork = true;
function pomoRender() {
    const m = String(Math.floor(pomoSecs / 60)).padStart(2, '0');
    const s = String(pomoSecs % 60).padStart(2, '0');
    document.getElementById('pomoDisplay').textContent = `${m}:${s}`;
}
function pomoToggle() {
    if (pomoRunning) { clearInterval(pomoTimer); pomoRunning = false; document.getElementById('pomoStart').innerHTML = '<i class="fa-solid fa-play me-1"></i>Reanudar'; return; }
    pomoRunning = true; document.getElementById('pomoStart').innerHTML = '<i class="fa-solid fa-pause me-1"></i>Pausar';
    pomoTimer = setInterval(() => {
        pomoSecs--;
        if (pomoSecs <= 0) {
            clearInterval(pomoTimer); pomoRunning = false;
            if (pomoWork) { logSession(25, 'pomodoro'); alert('¡Pomodoro completado! Toma un descanso de 5 min.'); pomoWork = false; pomoSecs = 5 * 60; document.getElementById('pomoPhase').textContent = 'Descanso'; }
            else { alert('Descanso terminado. ¡A trabajar!'); pomoWork = true; pomoSecs = 25 * 60; document.getElementById('pomoPhase').textContent = 'Trabajo'; }
            document.getElementById('pomoStart').innerHTML = '<i class="fa-solid fa-play me-1"></i>Iniciar';
        }
        pomoRender();
    }, 1000);
}
function pomoReset() { clearInterval(pomoTimer); pomoRunning = false; pomoWork = true; pomoSecs = 25 * 60; document.getElementById('pomoPhase').textContent = 'Trabajo'; document.getElementById('pomoStart').innerHTML = '<i class="fa-solid fa-play me-1"></i>Iniciar'; pomoRender(); }
function logSession(minutes, technique) {
    const b = new FormData(); b.append('_action', 'log'); b.append('csrf_token', CSRF_TOKEN);
    b.append('minutes', minutes); b.append('technique', technique); b.append('resource_id', document.getElementById('pomoResource').value);
    fetch(BASE_URL + '?page=timer', { method: 'POST', body: b }).then(r => r.json()).then(d => {
        if (d.success) { document.getElementById('statToday').textContent = d.todayMinutes; document.getElementById('statStreak').textContent = d.streak; }
    });
}
function logManual() { const m = parseInt(document.getElementById('manualMin').value); if (!m || m < 1) { alert('Minutos inválidos'); return; } logSession(m, 'read'); setTimeout(() => location.reload(), 600); }
pomoRender();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
