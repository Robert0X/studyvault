/* StudyVault — app.js */

/* Escapa texto antes de insertarlo con innerHTML (defensa XSS para datos de APIs externas) */
function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

/* ============================
   Modo oscuro
   ============================ */
(function () {
    if (localStorage.getItem('sv-theme') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
})();

function toggleDarkMode() {
    const root = document.documentElement;
    if (root.getAttribute('data-theme') === 'dark') {
        root.removeAttribute('data-theme');
        localStorage.setItem('sv-theme', 'light');
    } else {
        root.setAttribute('data-theme', 'dark');
        localStorage.setItem('sv-theme', 'dark');
    }
    updateDarkIcon();
}

function updateDarkIcon() {
    const btn = document.getElementById('darkToggle');
    if (!btn) return;
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    btn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
}

/* ============================
   Diccionario (sidebar) + guardar tarjeta
   ============================ */
let __lastDict = null;

(function () {
    const searchInput = document.getElementById('dictSearch');
    const searchBtn   = document.getElementById('dictBtn');
    const resultDiv   = document.getElementById('dictResult');
    if (!searchInput || !searchBtn || !resultDiv) return;

    function lookupWord() {
        const word = searchInput.value.trim();
        if (!word) return;
        resultDiv.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

        fetch(BASE_URL + 'api/dictionary.php?word=' + encodeURIComponent(word))
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    resultDiv.innerHTML = `<div class="text-danger small mt-1">${data.message}</div>`;
                    return;
                }
                const firstDef = (data.meanings[0] && data.meanings[0].definitions[0]) || {};
                __lastDict = {
                    word: data.word,
                    back: firstDef.definition || '',
                    example: firstDef.example || '',
                    phonetic: data.phonetic || '',
                    audio: data.audio || '',
                    collocations: ''
                };

                let html = `<div class="sv-dict-result mt-1">`;
                html += `<div class="d-flex align-items-center gap-2 flex-wrap">`;
                html += `<span class="sv-dict-word">${esc(data.word)}</span>`;
                if (data.phonetic) html += `<span class="sv-dict-phonetic">${esc(data.phonetic)}</span>`;
                if (data.audio) html += `<button class="btn btn-sm btn-link p-0" onclick="playAudio('${data.audio}')" title="Escuchar"><i class="fa-solid fa-volume-high text-primary"></i></button>`;
                html += `</div>`;

                data.meanings.forEach(m => {
                    html += `<div class="sv-dict-pos mt-1">${esc(m.partOfSpeech)}</div>`;
                    m.definitions.slice(0, 2).forEach(d => {
                        html += `<div class="sv-dict-def">• ${esc(d.definition)}</div>`;
                        if (d.example) html += `<div class="sv-dict-def text-muted fst-italic small">"${esc(d.example)}"</div>`;
                    });
                });
                html += `<div id="dictColloc"></div>`;
                html += `<div id="dictExamples"></div>`;
                html += `<button class="btn btn-sm btn-outline-success w-100 mt-2" onclick="svSaveWord()"><i class="fa-solid fa-plus me-1"></i>Guardar como tarjeta</button>`;
                html += `</div>`;
                resultDiv.innerHTML = html;

                // Enriquecer con colocaciones (Datamuse) — usos reales de la palabra
                fetch(BASE_URL + 'api/datamuse.php?word=' + encodeURIComponent(word))
                    .then(r => r.json())
                    .then(dm => {
                        if (dm.success && dm.collocations && dm.collocations.length) {
                            __lastDict.collocations = dm.collocations.join(', ');
                            const el = document.getElementById('dictColloc');
                            if (el) el.innerHTML = `<div class="sv-dict-def small mt-1"><strong>Se usa con:</strong> ${dm.collocations.slice(0, 8).map(esc).join(', ')}</div>`;
                        }
                    })
                    .catch(() => {});

                // Frases de ejemplo reales (Tatoeba)
                fetch(BASE_URL + 'api/tatoeba.php?word=' + encodeURIComponent(word))
                    .then(r => r.json())
                    .then(tt => {
                        if (tt.success && tt.sentences && tt.sentences.length) {
                            const el = document.getElementById('dictExamples');
                            if (el) el.innerHTML = '<div class="sv-dict-def small mt-1"><strong>Ejemplos:</strong></div>' +
                                tt.sentences.map(s => `<div class="sv-dict-def text-muted fst-italic small">"${esc(s)}"</div>`).join('');
                        }
                    })
                    .catch(() => {});
            })
            .catch(() => { resultDiv.innerHTML = '<div class="text-danger small mt-1">Error de conexión.</div>'; });
    }

    searchBtn.addEventListener('click', lookupWord);
    searchInput.addEventListener('keydown', e => { if (e.key === 'Enter') lookupWord(); });
})();

function svSaveWord() {
    if (!__lastDict) return;
    const body = new FormData();
    body.append('_action', 'store_dict');
    body.append('csrf_token', CSRF_TOKEN);
    body.append('word', __lastDict.word);
    body.append('back', __lastDict.back);
    body.append('example', __lastDict.example);
    body.append('phonetic', __lastDict.phonetic);
    body.append('collocations', __lastDict.collocations || '');
    body.append('audio', __lastDict.audio || '');

    fetch(BASE_URL + '?page=flashcards', { method: 'POST', body })
        .then(r => r.json())
        .then(d => {
            const res = document.getElementById('dictResult');
            const note = document.createElement('div');
            note.className = (d.success ? 'alert alert-success' : 'alert alert-danger') + ' py-1 px-2 small mt-2 mb-0';
            note.textContent = d.message;
            res.appendChild(note);
        });
}

function playAudio(url) {
    if (!url) return;
    new Audio(url).play().catch(() => {});
}

/* ============================
   Tooltips + init
   ============================ */
document.addEventListener('DOMContentLoaded', function () {
    updateDarkIcon();
    document.querySelectorAll('[title]').forEach(el => {
        if (el.closest('.sv-sidebar') || el.closest('table')) {
            new bootstrap.Tooltip(el, { trigger: 'hover', placement: 'top' });
        }
    });
});
