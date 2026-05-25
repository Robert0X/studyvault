<?php
$pageTitle = 'Plantillas CP';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-code me-2"></i>Plantillas / snippets</h2>
        <p class="text-muted mb-0">Tu biblioteca de código reutilizable (DSU, segment tree, etc.)</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tplModal"><i class="fa-solid fa-plus me-1"></i>Nueva plantilla</button>
        <a href="<?= BASE_URL ?>?page=cp" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<?php if (empty($templates)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-code fa-3x mb-3 opacity-50"></i>
        <h5>Sin plantillas</h5>
        <p>Guarda tus estructuras y trucos para copiarlos rápido en concursos.</p>
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($templates as $t): ?>
            <div class="col-12 col-lg-6" id="tpl-<?= $t['id'] ?>">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <span class="fw-semibold"><?= htmlspecialchars($t['title']) ?> <span class="badge bg-secondary bg-opacity-15 text-secondary ms-1"><?= htmlspecialchars($t['language']) ?></span></span>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" onclick="copyTpl(<?= $t['id'] ?>)" title="Copiar"><i class="fa-solid fa-copy"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="delTpl(<?= $t['id'] ?>)" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <pre class="m-0 p-3" style="max-height:280px;overflow:auto"><code id="code-<?= $t['id'] ?>"><?= htmlspecialchars($t['code']) ?></code></pre>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="modal fade" id="tplModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Nueva plantilla</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div id="tplAlert"></div>
            <div class="row g-2 mb-2">
                <div class="col-8"><label class="form-label fw-semibold">Título <span class="text-danger">*</span></label><input type="text" id="tplTitle" class="form-control" placeholder="ej. DSU (Union-Find)"></div>
                <div class="col-4"><label class="form-label fw-semibold">Lenguaje</label><input type="text" id="tplLang" class="form-control" value="cpp"></div>
            </div>
            <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
            <textarea id="tplCode" class="form-control font-monospace" rows="12" spellcheck="false"></textarea>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" onclick="saveTpl()"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button></div>
    </div></div>
</div>

<script>
function tplPost(action, fields) {
    const b = new FormData(); b.append('_action', action); b.append('csrf_token', CSRF_TOKEN);
    for (const k in fields) b.append(k, fields[k]);
    return fetch(BASE_URL + '?page=cp', { method: 'POST', body: b }).then(r => r.json());
}
function saveTpl() {
    const title = document.getElementById('tplTitle').value.trim();
    const code = document.getElementById('tplCode').value;
    if (!title || !code.trim()) { document.getElementById('tplAlert').innerHTML = '<div class="alert alert-danger py-2">Título y código son obligatorios.</div>'; return; }
    tplPost('template_store', { title, language: document.getElementById('tplLang').value.trim(), code })
        .then(d => { if (d.success) location.reload(); else document.getElementById('tplAlert').innerHTML = `<div class="alert alert-danger py-2">${d.message}</div>`; });
}
function delTpl(id) { if (!confirm('¿Eliminar plantilla?')) return; tplPost('template_destroy', { id }).then(d => { if (d.success) document.getElementById('tpl-' + id)?.remove(); }); }
function copyTpl(id) { navigator.clipboard.writeText(document.getElementById('code-' + id).textContent).then(() => {}); }
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
