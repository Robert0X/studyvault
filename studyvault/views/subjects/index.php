<?php
$pageTitle = 'Materias';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-layer-group me-2"></i>Materias</h2>
        <p class="text-muted mb-0">Organiza tu material por área de estudio</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subjectModal" onclick="resetSubjectForm()">
        <i class="fa-solid fa-plus me-1"></i>Nueva materia
    </button>
</div>

<?php if (empty($subjects)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-layer-group fa-3x mb-3 text-primary opacity-50"></i>
        <h5>Aún no tienes materias</h5>
        <p>Crea tu primera materia para organizar tus recursos de estudio.</p>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subjectModal" onclick="resetSubjectForm()">
            <i class="fa-solid fa-plus me-1"></i>Crear materia
        </button>
    </div>
<?php else: ?>
    <div class="row g-3" id="subjectsGrid">
        <?php foreach ($subjects as $s):
            $total     = (int)($s['total_resources'] ?? 0);
            $completed = (int)($s['completed'] ?? 0);
            $pct       = $total > 0 ? round($completed / $total * 100) : 0;
        ?>
            <div class="col-12 col-sm-6 col-xl-4" id="subject-card-<?= $s['id'] ?>">
                <div class="card sv-subject-card border-0 shadow-sm h-100">
                    <div class="sv-subject-banner" style="background:<?= htmlspecialchars($s['color']) ?>">
                        <i class="fa-solid <?= htmlspecialchars($s['icon']) ?> fa-2x text-white opacity-75"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-1"><?= htmlspecialchars($s['name']) ?></h5>
                        <p class="card-text text-muted small mb-3">
                            <?= htmlspecialchars($s['description'] ?: 'Sin descripción') ?>
                        </p>
                        <!-- Progreso -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span><?= $completed ?>/<?= $total ?> completados</span>
                                <span><?= $pct ?>%</span>
                            </div>
                            <div class="progress sv-progress">
                                <div class="progress-bar" style="width:<?= $pct ?>%; background-color:<?= htmlspecialchars($s['color']) ?>"></div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <a href="<?= BASE_URL ?>?page=resources&subject=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-box-archive me-1"></i><?= $total ?> recursos
                            </a>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" title="Editar"
                                        onclick='editSubject(<?= json_encode($s) ?>)'>
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn btn-outline-danger" title="Eliminar"
                                        onclick="deleteSubject(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['name'])) ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Materia (crear/editar) -->
<div class="modal fade" id="subjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subjectModalTitle">Nueva materia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="subjectAlert"></div>
                <input type="hidden" id="subjectId">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="subjectName" class="form-control" placeholder="ej. Programación Competitiva" maxlength="100">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción</label>
                    <textarea id="subjectDesc" class="form-control" rows="2" placeholder="Breve descripción..."></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Ícono</label>
                        <select id="subjectIcon" class="form-select">
                            <option value="fa-book">📚 Libro</option>
                            <option value="fa-code">💻 Código</option>
                            <option value="fa-language">🌐 Idioma</option>
                            <option value="fa-calculator">🧮 Matemáticas</option>
                            <option value="fa-flask">🔬 Ciencia</option>
                            <option value="fa-music">🎵 Música</option>
                            <option value="fa-globe">🌍 Web</option>
                            <option value="fa-brain">🧠 General</option>
                            <option value="fa-trophy">🏆 Competitivo</option>
                            <option value="fa-microchip">⚙️ Hardware</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Color</label>
                        <input type="color" id="subjectColor" class="form-control form-control-color w-100" value="#4361ee">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="subjectSaveBtn" onclick="saveSubject()">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function resetSubjectForm() {
    document.getElementById('subjectId').value = '';
    document.getElementById('subjectName').value = '';
    document.getElementById('subjectDesc').value = '';
    document.getElementById('subjectIcon').value = 'fa-book';
    document.getElementById('subjectColor').value = '#4361ee';
    document.getElementById('subjectModalTitle').textContent = 'Nueva materia';
    document.getElementById('subjectAlert').innerHTML = '';
}

function editSubject(s) {
    document.getElementById('subjectId').value = s.id;
    document.getElementById('subjectName').value = s.name;
    document.getElementById('subjectDesc').value = s.description || '';
    document.getElementById('subjectIcon').value = s.icon;
    document.getElementById('subjectColor').value = s.color;
    document.getElementById('subjectModalTitle').textContent = 'Editar materia';
    document.getElementById('subjectAlert').innerHTML = '';
    new bootstrap.Modal(document.getElementById('subjectModal')).show();
}

function saveSubject() {
    const id    = document.getElementById('subjectId').value;
    const name  = document.getElementById('subjectName').value.trim();
    const desc  = document.getElementById('subjectDesc').value.trim();
    const icon  = document.getElementById('subjectIcon').value;
    const color = document.getElementById('subjectColor').value;

    if (!name) {
        document.getElementById('subjectAlert').innerHTML =
            '<div class="alert alert-danger py-2">El nombre es obligatorio.</div>';
        return;
    }

    const action = id ? 'update' : 'store';
    const body   = new FormData();
    body.append('_action', action);
    body.append('csrf_token', CSRF_TOKEN);
    if (id) body.append('id', id);
    body.append('name', name);
    body.append('description', desc);
    body.append('icon', icon);
    body.append('color', color);

    fetch(BASE_URL + '?page=subjects', { method: 'POST', body })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('subjectModal')).hide();
                location.reload();
            } else {
                document.getElementById('subjectAlert').innerHTML =
                    `<div class="alert alert-danger py-2">${data.message}</div>`;
            }
        });
}

function deleteSubject(id, name) {
    if (!confirm(`¿Eliminar la materia "${name}"? También se eliminarán sus recursos.`)) return;
    const body = new FormData();
    body.append('_action', 'destroy');
    body.append('csrf_token', CSRF_TOKEN);
    body.append('id', id);
    fetch(BASE_URL + '?page=subjects', { method: 'POST', body })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('subject-card-' + id)?.remove();
            } else {
                alert(data.message);
            }
        });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
