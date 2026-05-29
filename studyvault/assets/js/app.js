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
   Centro de notificaciones (navbar)
   ============================ */
function svRefreshNotifications() {
    const list  = document.getElementById('notifList');
    const badge = document.getElementById('notifBadge');
    if (!list) return; // no estás logueado
    const emptyHtml = '<div class="text-center text-muted small py-3">Sin notificaciones</div>';
    const errHtml   = '<div class="text-center text-muted small py-3"><i class="fa-solid fa-triangle-exclamation me-1"></i>No se pudo cargar</div>';
    fetch(BASE_URL + '?page=notifications&action=feed', { headers: { 'Accept': 'application/json' } })
        .then(r => r.ok ? r.json() : Promise.reject(new Error('HTTP ' + r.status)))
        .then(d => {
            if (!d.success) { list.innerHTML = errHtml; return; }
            // Badge
            if (d.unread > 0) {
                badge.textContent = d.unread > 99 ? '99+' : d.unread;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }
            // Avisos del navegador (si el usuario activó el permiso)
            if (localStorage.getItem('sv-notif-enabled') === '1' && 'Notification' in window && Notification.permission === 'granted') {
                const shown = JSON.parse(localStorage.getItem('sv-notif-shown') || '[]');
                const newShown = shown.slice(-50);
                d.items.forEach(it => {
                    if (!it.read && !shown.includes(it.id)) {
                        try { new Notification('StudyVault', { body: it.title }); } catch (e) {}
                        newShown.push(it.id);
                    }
                });
                localStorage.setItem('sv-notif-shown', JSON.stringify(newShown.slice(-50)));
            }
            // Lista en el dropdown
            if (!d.items.length) {
                list.innerHTML = emptyHtml;
                return;
            }
            list.innerHTML = d.items.map(it => {
                const link = it.link ? esc(it.link) : '#';
                const dot  = it.read ? '' : '<span class="sv-notif-dot"></span>';
                return `<a href="${link}" class="dropdown-item sv-notif-item d-flex gap-2 py-2 ${it.read ? '' : 'sv-notif-unread'}">
                    <i class="fa-solid ${esc(it.icon || 'fa-bell')} text-primary mt-1"></i>
                    <div class="flex-grow-1 min-w-0">
                        <div class="small fw-semibold text-truncate">${esc(it.title)}</div>
                        <div class="small text-muted text-truncate">${esc(it.body || '')}</div>
                    </div>${dot}
                </a>`;
            }).join('');
        })
        .catch(() => { list.innerHTML = errHtml; });
}

/* ============================
   DataTables: aplica ordenamiento a cualquier <table data-sv-sortable>
   Mantiene compat con búsquedas/paginación server-side: si la tabla
   tiene data-sv-sort-only, se desactiva paginación/buscador propios.
   ============================ */
function svInitDataTables() {
    if (typeof jQuery === 'undefined' || !jQuery.fn.DataTable) return;
    jQuery('table[data-sv-sortable]').each(function () {
        const $t = jQuery(this);
        // Si ya hay un DataTable montado (p. ej. tras un re-render AJAX), destrúyelo primero.
        if (jQuery.fn.DataTable.isDataTable(this)) {
            $t.DataTable().destroy();
        }
        const sortOnly = $t.is('[data-sv-sort-only]');
        const opts = {
            paging:    !sortOnly,
            searching: !sortOnly,
            info:      !sortOnly,
            order:     [],
            language: {
                emptyTable:     'Sin datos',
                zeroRecords:    'Sin coincidencias',
                lengthMenu:     'Mostrar _MENU_',
                search:         'Buscar:',
                info:           '_START_–_END_ de _TOTAL_',
                infoEmpty:      '0 registros',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' },
            },
        };
        // Permite excluir columnas por data-sv-no-sort en <th>
        opts.columnDefs = [{ targets: '[data-sv-no-sort]', orderable: false, searchable: false }];
        $t.DataTable(opts);
    });
}

/* ============================
   Tooltips + init
   Se ejecuta tras DOMContentLoaded o inmediatamente si la página ya cargó
   (evita el problema de registrar el listener cuando el evento ya pasó).
   ============================ */
function svBootstrap() {
    updateDarkIcon();
    document.querySelectorAll('[title]').forEach(el => {
        if (el.closest('.sv-sidebar') || el.closest('table')) {
            new bootstrap.Tooltip(el, { trigger: 'hover', placement: 'top' });
        }
    });
    // Notificaciones (sólo si el usuario está logueado: existe el badge)
    if (document.getElementById('notifBadge')) {
        svRefreshNotifications();
        setInterval(svRefreshNotifications, 60000);
        // Refrescar también cuando el usuario abre el dropdown (no esperar al poll).
        const btn = document.getElementById('notifBtn');
        if (btn) btn.addEventListener('click', svRefreshNotifications);
    }
    // DataTables sobre tablas marcadas
    svInitDataTables();
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', svBootstrap);
} else {
    svBootstrap();
}
