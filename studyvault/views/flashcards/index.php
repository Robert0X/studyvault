<?php
$pageTitle = 'Flashcards';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-clone me-2"></i>Flashcards</h2>
        <p class="text-muted mb-0">Repaso con repetición espaciada (SM-2)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>?page=flashcards&action=study" class="btn btn-success">
            <i class="fa-solid fa-graduation-cap me-1"></i>Estudiar
            <?php if ((int)($stats['due'] ?? 0) > 0): ?>
                <span class="badge bg-light text-success ms-1"><?= (int)$stats['due'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>?page=flashcards&action=export" class="btn btn-outline-secondary" title="Exportar a CSV (compatible con Anki)">
            <i class="fa-solid fa-file-export me-1"></i>Exportar
        </a>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importModal" title="Importar CSV">
            <i class="fa-solid fa-file-import me-1"></i>Importar
        </button>
        <div class="btn-group">
            <button class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" title="Estudiar por nivel CEFR">
                <i class="fa-solid fa-layer-group me-1"></i>Por nivel
            </button>
            <ul class="dropdown-menu">
                <?php foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $lv): ?>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>?page=flashcards&action=study&level=<?= $lv ?>">Nivel <?= $lv ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cardModal">
            <i class="fa-solid fa-plus me-1"></i>Nueva tarjeta
        </button>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php
    $cards_stat = [
        ['due',      'Por repasar hoy', 'fa-bell',          'danger'],
        ['total',    'Total',           'fa-clone',         'primary'],
        ['learning', 'Aprendiendo',     'fa-seedling',      'info'],
        ['mature',   'Maduras',         'fa-tree',          'success'],
        ['mastered', 'Dominadas',       'fa-crown',         'warning'],
    ];
    foreach ($cards_stat as [$key, $label, $icon, $color]):
    ?>
        <div class="col-6 col-lg">
            <div class="card sv-stat-card border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="sv-stat-icon bg-<?= $color ?> bg-opacity-15 text-<?= $color ?>">
                        <i class="fa-solid <?= $icon ?>"></i>
                    </div>
                    <div>
                        <div class="sv-stat-number"><?= (int)($stats[$key] ?? 0) ?></div>
                        <div class="sv-stat-label"><?= $label ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Lista -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($cards)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-clone fa-3x mb-3 opacity-50"></i>
                <h5>No tienes tarjetas</h5>
                <p>Créalas aquí o guarda palabras desde el diccionario (barra lateral).</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Anverso</th>
                            <th class="d-none d-md-table-cell">Reverso</th>
                            <th>Mazo</th>
                            <th>Estado</th>
                            <th class="d-none d-sm-table-cell">Próx. repaso</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $statusInfo = [
                            'new'      => ['Nueva',     'secondary'],
                            'learning' => ['Aprendiendo','info'],
                            'mature'   => ['Madura',    'success'],
                            'mastered' => ['Dominada',  'warning'],
                        ];
                        foreach ($cards as $c):
                            [$slabel, $sclass] = $statusInfo[$c['status']] ?? ['—', 'secondary'];
                        ?>
                            <tr id="card-<?= $c['id'] ?>">
                                <td class="fw-semibold"><?= htmlspecialchars($c['front']) ?></td>
                                <td class="d-none d-md-table-cell text-muted">
                                    <?= htmlspecialchars(mb_substr($c['back'], 0, 60)) ?><?= mb_strlen($c['back']) > 60 ? '…' : '' ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $c['deck'] === 'cp' ? 'dark' : 'primary' ?> bg-opacity-15 text-<?= $c['deck'] === 'cp' ? 'dark' : 'primary' ?>">
                                        <i class="fa-solid <?= $c['deck'] === 'cp' ? 'fa-code' : 'fa-language' ?> me-1"></i>
                                        <?= $c['deck'] === 'cp' ? 'CP' : 'Vocab' ?>
                                    </span>
                                </td>
                                <td><span class="badge bg-<?= $sclass ?> bg-opacity-15 text-<?= $sclass ?>"><?= $slabel ?></span></td>
                                <td class="d-none d-sm-table-cell small text-muted"><?= htmlspecialchars($c['due_date']) ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCard(<?= $c['id'] ?>)" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal nueva tarjeta -->
<div class="modal fade" id="cardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva tarjeta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="cardAlert"></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mazo</label>
                    <select id="cardDeck" class="form-select">
                        <option value="vocab">Vocabulario (inglés)</option>
                        <option value="cp">Programación competitiva</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Anverso (palabra/pregunta) <span class="text-danger">*</span></label>
                    <input type="text" id="cardFront" class="form-control" placeholder="ej. although">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reverso (significado/respuesta) <span class="text-danger">*</span></label>
                    <textarea id="cardBack" class="form-control" rows="2" placeholder="ej. aunque / a pesar de"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Ejemplo de uso</label>
                    <input type="text" id="cardExample" class="form-control" placeholder="Although it was raining, we went out.">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nivel CEFR (opcional)</label>
                    <select id="cardLevel" class="form-select">
                        <option value="">— Sin nivel —</option>
                        <option>A1</option><option>A2</option><option>B1</option><option>B2</option><option>C1</option><option>C2</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveCard()">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal importar CSV -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Importar tarjetas (CSV)</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div id="importAlert"></div>
            <p class="small text-muted">Formato: <code>front,back,example,extra,deck,level</code> (el mismo de "Exportar"; compatible con Anki).</p>
            <input type="file" id="importFile" class="form-control" accept=".csv,text/csv">
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" onclick="importCards()"><i class="fa-solid fa-file-import me-1"></i>Importar</button>
        </div>
    </div></div>
</div>

<script>
function importCards() {
    const f = document.getElementById('importFile').files[0];
    if (!f) { document.getElementById('importAlert').innerHTML = '<div class="alert alert-danger py-2">Selecciona un archivo CSV.</div>'; return; }
    const body = new FormData();
    body.append('_action', 'import'); body.append('csrf_token', CSRF_TOKEN); body.append('file', f);
    fetch(BASE_URL + '?page=flashcards', { method: 'POST', body })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); else document.getElementById('importAlert').innerHTML = `<div class="alert alert-danger py-2">${d.message}</div>`; });
}
function saveCard() {
    const front = document.getElementById('cardFront').value.trim();
    const back  = document.getElementById('cardBack').value.trim();
    if (!front || !back) {
        document.getElementById('cardAlert').innerHTML =
            '<div class="alert alert-danger py-2">Anverso y reverso son obligatorios.</div>';
        return;
    }
    const body = new FormData();
    body.append('_action', 'store');
    body.append('csrf_token', CSRF_TOKEN);
    body.append('deck', document.getElementById('cardDeck').value);
    body.append('front', front);
    body.append('back', back);
    body.append('example', document.getElementById('cardExample').value.trim());
    body.append('cefr_level', document.getElementById('cardLevel').value);

    fetch(BASE_URL + '?page=flashcards', { method: 'POST', body })
        .then(r => r.json())
        .then(d => {
            if (d.success) { location.reload(); }
            else { document.getElementById('cardAlert').innerHTML = `<div class="alert alert-danger py-2">${d.message}</div>`; }
        });
}

function deleteCard(id) {
    if (!confirm('¿Eliminar esta tarjeta?')) return;
    const body = new FormData();
    body.append('_action', 'destroy');
    body.append('csrf_token', CSRF_TOKEN);
    body.append('id', id);
    fetch(BASE_URL + '?page=flashcards', { method: 'POST', body })
        .then(r => r.json())
        .then(d => { if (d.success) document.getElementById('card-' + id)?.remove(); else alert(d.message); });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
