<?php
$pageTitle = 'Estudiar';
require __DIR__ . '/../partials/header.php';
$level = (isset($_GET['level']) && in_array($_GET['level'], ['A1','A2','B1','B2','C1','C2'], true)) ? $_GET['level'] : null;
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-graduation-cap me-2"></i>Sesión de estudio<?= $level ? ' · Nivel ' . htmlspecialchars($level) : '' ?></h2>
        <p class="text-muted mb-0">Califica qué tan bien recordaste cada tarjeta</p>
    </div>
    <a href="<?= BASE_URL ?>?page=flashcards" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Volver</a>
</div>

<?php if (empty($cards)): ?>
    <div class="text-center py-5">
        <i class="fa-solid fa-circle-check fa-4x text-success mb-3"></i>
        <h4>¡Todo al día!</h4>
        <p class="text-muted">No tienes tarjetas pendientes<?= $level ? ' de nivel ' . htmlspecialchars($level) : '' ?> por hoy.</p>
        <a href="<?= BASE_URL ?>?page=flashcards" class="btn btn-primary">Volver a las tarjetas</a>
    </div>
<?php else: ?>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <!-- Modo de estudio -->
            <div class="btn-group w-100 mb-3" role="group">
                <button class="btn btn-outline-primary active" id="mode-clasico" onclick="setMode('clasico')">Clásico</button>
                <button class="btn btn-outline-primary" id="mode-cloze" onclick="setMode('cloze')">Cloze (hueco)</button>
                <button class="btn btn-outline-primary" id="mode-produccion" onclick="setMode('produccion')">Producción</button>
                <button class="btn btn-outline-primary" id="mode-escucha" onclick="setMode('escucha')">Escucha</button>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                <span id="studyCounter">Tarjeta 1 de <?= count($cards) ?></span>
                <span id="studyRemaining"><?= count($cards) ?> restantes</span>
            </div>
            <div class="progress sv-progress mb-4"><div class="progress-bar bg-success" id="studyProgress" style="width:0%"></div></div>

            <div class="card sv-flashcard border-0 shadow-sm" id="flashcard" onclick="flipCard()">
                <div class="card-body text-center p-5 w-100">
                    <div class="sv-card-deck mb-3" id="cardDeckBadge"></div>
                    <div id="promptArea"></div>
                    <div id="cardBackBox" class="sv-card-back d-none">
                        <hr class="my-3">
                        <div id="answerLine" class="mb-1"></div>
                        <div id="cardBackText" class="fw-semibold"></div>
                        <div id="cardExampleText" class="text-muted fst-italic mt-2"></div>
                        <div id="cardExtraText" class="small text-muted mt-1"></div>
                    </div>
                    <div class="text-muted small mt-4" id="flipHint"><i class="fa-solid fa-hand-pointer me-1"></i>Clic (o Espacio) para ver la respuesta</div>
                </div>
            </div>

            <div class="row g-2 mt-3 d-none" id="gradeButtons">
                <div class="col-3"><button class="btn btn-danger w-100 py-2" onclick="grade(2)">Otra vez</button></div>
                <div class="col-3"><button class="btn btn-warning w-100 py-2" onclick="grade(3)">Difícil</button></div>
                <div class="col-3"><button class="btn btn-info w-100 py-2 text-white" onclick="grade(4)">Bien</button></div>
                <div class="col-3"><button class="btn btn-success w-100 py-2" onclick="grade(5)">Fácil</button></div>
            </div>
        </div>
    </div>

    <script>
    const CARDS = <?= json_encode(array_map(fn($c) => [
        'id' => $c['id'], 'deck' => $c['deck'], 'front' => $c['front'],
        'back' => $c['back'], 'example' => $c['example'] ?? '', 'extra' => $c['extra'] ?? '',
        'audio' => $c['audio_url'] ?? '',
    ], $cards), JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS) ?>;

    let idx = 0, flipped = false, mode = 'clasico';

    function setMode(m) {
        mode = m;
        ['clasico', 'cloze', 'produccion', 'escucha'].forEach(x => document.getElementById('mode-' + x).classList.toggle('active', x === m));
        renderCard();
    }

    function escapeRe(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

    function buildPrompt(c) {
        if (mode === 'clasico') {
            return `<div class="sv-card-front">${c.front}</div>`;
        }
        if (mode === 'cloze') {
            if (c.example) {
                const blanked = c.example.replace(new RegExp(escapeRe(c.front), 'gi'), '_____');
                return `<div class="fs-5">${blanked}</div><div class="small text-muted mt-2">Completa el hueco</div>`;
            }
            return `<div class="sv-card-front">${c.front}</div><div class="small text-muted mt-2">(sin ejemplo para cloze)</div>`;
        }
        if (mode === 'escucha') {
            const btn = c.audio
                ? `<button class="btn btn-lg btn-primary" onclick="event.stopPropagation(); new Audio('${c.audio}').play().catch(()=>{})"><i class="fa-solid fa-volume-high me-1"></i>Reproducir</button>`
                : `<div class="text-muted">(esta tarjeta no tiene audio; usa otro modo)</div>`;
            return `${btn}<input id="prodInput" class="form-control mt-3 text-center" placeholder="Escribe lo que escuchaste...">`;
        }
        // producción
        return `<div class="h4">${c.back}</div><input id="prodInput" class="form-control mt-3 text-center" placeholder="Escribe la palabra en inglés...">`;
    }

    function renderCard() {
        const c = CARDS[idx];
        document.getElementById('cardDeckBadge').innerHTML = c.deck === 'cp'
            ? '<span class="badge bg-dark"><i class="fa-solid fa-code me-1"></i>CP</span>'
            : '<span class="badge bg-primary"><i class="fa-solid fa-language me-1"></i>Vocab</span>';
        document.getElementById('promptArea').innerHTML = buildPrompt(c);
        document.getElementById('cardBackText').textContent = c.back;
        document.getElementById('cardExampleText').textContent = c.example ? '"' + c.example + '"' : '';
        document.getElementById('cardExtraText').textContent = c.extra || '';
        document.getElementById('answerLine').innerHTML = '';

        flipped = false;
        document.getElementById('cardBackBox').classList.add('d-none');
        document.getElementById('gradeButtons').classList.add('d-none');
        document.getElementById('flipHint').classList.remove('d-none');

        document.getElementById('studyCounter').textContent = `Tarjeta ${idx + 1} de ${CARDS.length}`;
        document.getElementById('studyRemaining').textContent = `${CARDS.length - idx} restantes`;
        document.getElementById('studyProgress').style.width = `${(idx / CARDS.length) * 100}%`;
    }

    function flipCard() {
        if (flipped) return;
        flipped = true;
        const c = CARDS[idx];
        if (mode === 'produccion' || mode === 'escucha') {
            const typed = (document.getElementById('prodInput')?.value || '').trim();
            const ok = typed.toLowerCase() === c.front.toLowerCase();
            document.getElementById('answerLine').innerHTML = ok
                ? `<span class="badge bg-success">✓ Correcto</span> <span class="sv-card-front">${c.front}</span>`
                : `<span class="badge bg-danger">✗</span> Respuesta: <span class="sv-card-front">${c.front}</span>`;
        } else {
            document.getElementById('answerLine').innerHTML = `<span class="sv-card-front">${c.front}</span>`;
        }
        document.getElementById('cardBackBox').classList.remove('d-none');
        document.getElementById('gradeButtons').classList.remove('d-none');
        document.getElementById('flipHint').classList.add('d-none');
    }

    function grade(quality) {
        const c = CARDS[idx];
        const body = new FormData();
        body.append('_action', 'review'); body.append('csrf_token', CSRF_TOKEN);
        body.append('id', c.id); body.append('quality', quality);
        fetch(BASE_URL + '?page=flashcards', { method: 'POST', body }).then(r => r.json()).then(() => {
            idx++;
            if (idx >= CARDS.length) {
                document.getElementById('studyProgress').style.width = '100%';
                document.querySelector('.row.justify-content-center').innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fa-solid fa-circle-check fa-4x text-success mb-3"></i>
                        <h4>¡Sesión completada!</h4>
                        <p class="text-muted">Repasaste ${CARDS.length} tarjetas.</p>
                        <a href="${BASE_URL}?page=flashcards" class="btn btn-primary">Volver</a>
                    </div>`;
            } else { renderCard(); }
        });
    }

    document.addEventListener('keydown', e => {
        if (e.target && e.target.id === 'prodInput' && e.key !== 'Enter') return;
        if (e.code === 'Space' || (e.key === 'Enter' && !flipped)) { e.preventDefault(); flipCard(); }
        else if (flipped && ['1', '2', '3', '4'].includes(e.key)) { grade([2, 3, 4, 5][parseInt(e.key) - 1]); }
    });

    renderCard();
    </script>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
