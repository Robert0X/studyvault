<?php
$pageTitle = 'Estudiar';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-graduation-cap me-2"></i>Sesión de estudio</h2>
        <p class="text-muted mb-0">Califica qué tan bien recordaste cada tarjeta</p>
    </div>
    <a href="<?= BASE_URL ?>?page=flashcards" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (empty($cards)): ?>
    <div class="text-center py-5">
        <i class="fa-solid fa-circle-check fa-4x text-success mb-3"></i>
        <h4>¡Todo al día!</h4>
        <p class="text-muted">No tienes tarjetas pendientes de repaso por hoy.</p>
        <a href="<?= BASE_URL ?>?page=flashcards" class="btn btn-primary">Volver a las tarjetas</a>
    </div>
<?php else: ?>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <!-- Progreso -->
            <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                <span id="studyCounter">Tarjeta 1 de <?= count($cards) ?></span>
                <span id="studyRemaining"><?= count($cards) ?> restantes</span>
            </div>
            <div class="progress sv-progress mb-4">
                <div class="progress-bar bg-success" id="studyProgress" style="width:0%"></div>
            </div>

            <!-- Tarjeta -->
            <div class="card sv-flashcard border-0 shadow-sm" id="flashcard" onclick="flipCard()">
                <div class="card-body text-center p-5">
                    <div class="sv-card-deck mb-3" id="cardDeckBadge"></div>
                    <div id="cardFrontText" class="sv-card-front"></div>
                    <div id="cardBackBox" class="sv-card-back d-none">
                        <hr class="my-3">
                        <div id="cardBackText" class="fw-semibold"></div>
                        <div id="cardExampleText" class="text-muted fst-italic mt-2"></div>
                        <div id="cardExtraText" class="small text-muted mt-1"></div>
                    </div>
                    <div class="text-muted small mt-4" id="flipHint">
                        <i class="fa-solid fa-hand-pointer me-1"></i>Clic para ver la respuesta
                    </div>
                </div>
            </div>

            <!-- Botones de calificación (SM-2) -->
            <div class="row g-2 mt-3 d-none" id="gradeButtons">
                <div class="col-3"><button class="btn btn-danger w-100 py-2" onclick="grade(2)">Otra vez<br><small>&lt;1d</small></button></div>
                <div class="col-3"><button class="btn btn-warning w-100 py-2" onclick="grade(3)">Difícil</button></div>
                <div class="col-3"><button class="btn btn-info w-100 py-2 text-white" onclick="grade(4)">Bien</button></div>
                <div class="col-3"><button class="btn btn-success w-100 py-2" onclick="grade(5)">Fácil</button></div>
            </div>
        </div>
    </div>

    <script>
    const CARDS = <?= json_encode(array_map(fn($c) => [
        'id'      => $c['id'],
        'deck'    => $c['deck'],
        'front'   => $c['front'],
        'back'    => $c['back'],
        'example' => $c['example'] ?? '',
        'extra'   => $c['extra'] ?? '',
    ], $cards), JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS) ?>;

    let idx = 0;
    let flipped = false;

    function renderCard() {
        const c = CARDS[idx];
        document.getElementById('cardDeckBadge').innerHTML =
            c.deck === 'cp'
                ? '<span class="badge bg-dark"><i class="fa-solid fa-code me-1"></i>CP</span>'
                : '<span class="badge bg-primary"><i class="fa-solid fa-language me-1"></i>Vocab</span>';
        document.getElementById('cardFrontText').textContent = c.front;
        document.getElementById('cardBackText').textContent = c.back;
        document.getElementById('cardExampleText').textContent = c.example ? '"' + c.example + '"' : '';
        document.getElementById('cardExtraText').textContent = c.extra || '';

        // reset flip
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
        document.getElementById('cardBackBox').classList.remove('d-none');
        document.getElementById('gradeButtons').classList.remove('d-none');
        document.getElementById('flipHint').classList.add('d-none');
    }

    function grade(quality) {
        const c = CARDS[idx];
        const body = new FormData();
        body.append('_action', 'review');
        body.append('csrf_token', CSRF_TOKEN);
        body.append('id', c.id);
        body.append('quality', quality);

        fetch(BASE_URL + '?page=flashcards', { method: 'POST', body })
            .then(r => r.json())
            .then(() => {
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
                } else {
                    renderCard();
                }
            });
    }

    // Atajos de teclado: espacio = voltear, 1-4 = calificar
    document.addEventListener('keydown', e => {
        if (e.code === 'Space') { e.preventDefault(); flipCard(); }
        else if (flipped && ['1','2','3','4'].includes(e.key)) {
            grade([2,3,4,5][parseInt(e.key) - 1]);
        }
    });

    renderCard();
    </script>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
