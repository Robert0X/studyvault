<?php
$pageTitle = 'Estadísticas';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-chart-line me-2"></i>Estadísticas</h2>
        <p class="text-muted mb-0">Visualiza tu progreso real con gráficas</p>
    </div>
    <a href="<?= BASE_URL ?>?page=report" class="btn btn-outline-secondary">
        <i class="fa-solid fa-file-lines me-1"></i>Reporte semanal
    </a>
</div>

<div id="statsAlert"></div>

<!--
    NOTA TÉCNICA: cada canvas vive dentro de un <div> con altura fija.
    Chart.js v4 con responsive:true + maintainAspectRatio:false
    se ajusta al alto del contenedor padre. Sin esto, en algunos layouts
    el canvas se renderiza con altura 0 y la gráfica no se ve.
-->
<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-clock me-2 text-primary"></i>Minutos de estudio (últimos 30 días)</h6>
            <div class="sv-chart-box" style="position:relative;height:260px">
                <canvas id="chartStudy"></canvas>
            </div>
        </div></div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-clone me-2 text-info"></i>Tarjetas por estado (SM-2)</h6>
            <div class="sv-chart-box" style="position:relative;height:260px">
                <canvas id="chartCards"></canvas>
            </div>
        </div></div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-language me-2 text-success"></i>Vocabulario por nivel CEFR</h6>
            <div class="sv-chart-box" style="position:relative;height:260px">
                <canvas id="chartLevels"></canvas>
            </div>
        </div></div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-chart-pie me-2 text-warning"></i>Problemas CP por tag (debilidades/fortalezas)</h6>
            <div class="sv-chart-box" style="position:relative;height:260px">
                <canvas id="chartTags"></canvas>
            </div>
        </div></div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-arrow-trend-up me-2 text-danger"></i>Rating de Codeforces</h6>
            <div class="sv-chart-box" style="position:relative;height:240px">
                <canvas id="chartRating"></canvas>
            </div>
            <p class="small text-muted mt-2 mb-0">El histórico crece a medida que sincronizas tu handle de Codeforces.</p>
        </div></div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="fa-solid fa-trophy me-2 text-dark"></i>Problemas resueltos por dificultad (rating)</h6>
            <div class="sv-chart-box" style="position:relative;height:260px">
                <canvas id="chartSolved"></canvas>
            </div>
        </div></div>
    </div>
</div>

<!--
    Chart.js servido localmente desde assets/vendor/ para no depender de CDN
    (algunos navegadores con ad-blocker o redes restringidas bloquean jsdelivr).
-->
<script src="<?= BASE_URL ?>assets/vendor/chart.umd.min.js"
        onerror="document.getElementById('statsAlert').innerHTML='<div class=\'alert alert-danger\'>No se pudo cargar Chart.js (revisa que exista <code>assets/vendor/chart.umd.min.js</code>).</div>'"></script>
<script>
(function () {
    const alertBox = document.getElementById('statsAlert');

    function showAlert(level, msg) {
        console.error('[Stats] ' + level + ': ' + msg);
        const div = document.createElement('div');
        div.className = 'alert alert-' + (level === 'error' ? 'danger' : 'warning') + ' my-2';
        div.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i>' + msg;
        if (alertBox) alertBox.appendChild(div);
    }

    function init() {
        if (typeof Chart === 'undefined') {
            showAlert('error', 'Chart.js no está disponible. Revisa la consola del navegador (F12) y verifica que <code>assets/vendor/chart.umd.min.js</code> exista.');
            return;
        }
        console.log('[Stats] Chart.js v' + (Chart.version || '?') + ' cargado. Pidiendo datos...');

        // Forzar tamaño basado en el contenedor padre (.sv-chart-box tiene altura fija)
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;
        Chart.defaults.font.family = 'Inter, Segoe UI, system-ui, sans-serif';

        const PALETTE = {
            primary: '#4f46e5', success: '#16a34a', warning: '#f59e0b',
            danger:  '#dc2626', info:    '#0ea5e9', secondary: '#64748b',
            dark:    '#1f2937',
        };

        function makeChart(canvasId, config, name) {
            try {
                const el = document.getElementById(canvasId);
                if (!el) { console.error('[Stats] canvas no encontrado: ' + canvasId); return; }
                new Chart(el, config);
                console.log('[Stats] OK: ' + name);
            } catch (e) {
                console.error('[Stats] Error renderizando "' + name + '":', e);
                showAlert('warning', 'No se pudo dibujar "' + name + '": ' + e.message);
            }
        }

        fetch(BASE_URL + '?page=stats&action=feed', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(r => {
                console.log('[Stats] HTTP', r.status, r.statusText);
                if (!r.ok) throw new Error('HTTP ' + r.status + ' (¿sesión expirada?)');
                return r.text().then(t => {
                    try { return JSON.parse(t); }
                    catch (e) { throw new Error('Respuesta no es JSON. Primeros 100 chars: ' + t.substring(0, 100)); }
                });
            })
            .then(d => {
                console.log('[Stats] Datos recibidos:', d);
                if (!d || !d.success) { showAlert('error', 'El servidor respondió error al cargar los datos.'); return; }

                makeChart('chartStudy', {
                    type: 'bar',
                    data: { labels: d.studyByDay.labels,
                        datasets: [{ label: 'Minutos', data: d.studyByDay.data, backgroundColor: PALETTE.primary, borderRadius: 4 }] },
                    options: { plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                }, 'Minutos por día');

                makeChart('chartCards', {
                    type: 'doughnut',
                    data: { labels: d.cardsByStatus.labels,
                        datasets: [{ data: d.cardsByStatus.data,
                            backgroundColor: [PALETTE.secondary, PALETTE.info, PALETTE.success, PALETTE.warning] }] },
                    options: { plugins: { legend: { position: 'bottom' } } }
                }, 'Tarjetas por estado');

                makeChart('chartLevels', {
                    type: 'bar',
                    data: { labels: d.cardsByLevel.labels,
                        datasets: [{ label: 'Tarjetas', data: d.cardsByLevel.data, backgroundColor: PALETTE.success, borderRadius: 4 }] },
                    options: { indexAxis: 'y', plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
                }, 'Vocabulario por CEFR');

                const hasTags = d.cpByTag.labels.length > 0;
                makeChart('chartTags', {
                    type: 'radar',
                    data: { labels: hasTags ? d.cpByTag.labels : ['(sin datos)'],
                        datasets: [{ label: 'Resueltos', data: hasTags ? d.cpByTag.data : [0],
                            borderColor: PALETTE.warning, backgroundColor: 'rgba(245,158,11,0.25)',
                            pointBackgroundColor: PALETTE.warning }] },
                    options: { plugins: { legend: { display: false } },
                        scales: { r: { ticks: { precision: 0 } } } }
                }, 'CP por tag');

                const hasRating = d.cpRatingHistory.labels.length > 0;
                makeChart('chartRating', {
                    type: 'line',
                    data: { labels: hasRating ? d.cpRatingHistory.labels : ['(sin sincronizar)'],
                        datasets: [{ label: 'Rating', data: hasRating ? d.cpRatingHistory.data : [0],
                            borderColor: PALETTE.danger, backgroundColor: 'rgba(220,38,38,0.15)',
                            tension: 0.3, fill: true }] },
                    options: { plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } } }
                }, 'Rating CF');

                makeChart('chartSolved', {
                    type: 'bar',
                    data: { labels: d.cpSolvedRating.labels,
                        datasets: [{ label: 'Resueltos', data: d.cpSolvedRating.data, backgroundColor: PALETTE.dark, borderRadius: 4 }] },
                    options: { plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                }, 'Solved por rating');

                // Diagnóstico: todo a cero ⇒ nota informativa
                const totals =
                    d.studyByDay.data.reduce((a,b)=>a+b,0) +
                    d.cardsByStatus.data.reduce((a,b)=>a+b,0) +
                    d.cpByTag.data.reduce((a,b)=>a+b,0) +
                    d.cpSolvedRating.data.reduce((a,b)=>a+b,0);
                if (totals === 0) {
                    showAlert('warning', 'Aún no hay datos para esta cuenta. Agrega flashcards, sesiones de estudio o sincroniza Codeforces (o reimporta <code>studyvault_completo.sql</code> que ya trae datos demo).');
                }
            })
            .catch(err => {
                console.error('[Stats] Fallo en fetch o procesamiento:', err);
                showAlert('error', 'No se pudieron cargar los datos: ' + err.message);
            });
    }

    // Esperar a que la página y los scripts terminen de cargar antes de inicializar.
    if (document.readyState === 'complete') init();
    else window.addEventListener('load', init);
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
