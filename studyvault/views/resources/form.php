<?php
$pageTitle = ($editing ?? false) ? 'Editar recurso' : 'Nuevo recurso';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0">
            <i class="fa-solid <?= ($editing ?? false) ? 'fa-pen' : 'fa-plus-circle' ?> me-2"></i>
            <?= ($editing ?? false) ? 'Editar recurso' : 'Nuevo recurso' ?>
        </h2>
    </div>
    <a href="<?= BASE_URL ?>?page=resources" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <i class="fa-solid fa-circle-exclamation me-1"></i><?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <?php
                $action = ($editing ?? false)
                    ? BASE_URL . '?page=resources'
                    : BASE_URL . '?page=resources';
                $actionVal = ($editing ?? false) ? 'update' : 'store';
                ?>
                <form method="POST" action="<?= $action ?>" enctype="multipart/form-data" novalidate id="resourceForm">
                    <input type="hidden" name="_action" value="<?= $actionVal ?>">
                    <?= csrf_field() ?>
                    <?php if ($editing ?? false): ?>
                        <input type="hidden" name="id" value="<?= (int)$resource['id'] ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <!-- Título -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                   value="<?= htmlspecialchars($resource['title'] ?? $_POST['title'] ?? '') ?>"
                                   placeholder="ej. CP-Algorithms - Referencia de algoritmos"
                                   required maxlength="200">
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="Breve descripción del recurso..."><?= htmlspecialchars($resource['description'] ?? $_POST['description'] ?? '') ?></textarea>
                        </div>

                        <!-- Tipo y Materia -->
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" id="typeSelect" onchange="toggleFileField()">
                                <option value="link"  <?= (($resource['type'] ?? $_POST['type'] ?? '') === 'link')  ? 'selected' : '' ?>>🔗 Enlace web</option>
                                <option value="pdf"   <?= (($resource['type'] ?? $_POST['type'] ?? '') === 'pdf')   ? 'selected' : '' ?>>📄 PDF / Archivo</option>
                                <option value="note"  <?= (($resource['type'] ?? $_POST['type'] ?? '') === 'note')  ? 'selected' : '' ?>>📝 Nota</option>
                                <option value="video" <?= (($resource['type'] ?? $_POST['type'] ?? '') === 'video') ? 'selected' : '' ?>>🎥 Video</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Materia <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select" required>
                                <option value="">Selecciona una materia</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= $s['id'] ?>"
                                        <?= (int)($resource['subject_id'] ?? $_POST['subject_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- URL -->
                        <div class="col-12" id="urlField">
                            <label class="form-label fw-semibold">URL del recurso</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
                                <input type="url" name="url" class="form-control"
                                       value="<?= htmlspecialchars($resource['url'] ?? $_POST['url'] ?? '') ?>"
                                       placeholder="https://...">
                            </div>
                        </div>

                        <!-- Archivo -->
                        <div class="col-12" id="fileField" style="display:none">
                            <label class="form-label fw-semibold">Archivo (PDF o imagen)</label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                            <?php if (!empty($resource['file_path'])): ?>
                                <div class="mt-2 small text-muted">
                                    <i class="fa-solid fa-paperclip me-1"></i>Archivo actual:
                                    <a href="<?= BASE_URL ?>assets/uploads/<?= htmlspecialchars($resource['file_path']) ?>" target="_blank">
                                        <?= htmlspecialchars($resource['file_path']) ?>
                                    </a>
                                    (subir nuevo para reemplazar)
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Máximo 5MB. Formatos: PDF, JPG, PNG, GIF, WEBP</div>
                        </div>

                        <!-- Estado -->
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="status" class="form-select">
                                <option value="pending"     <?= (($resource['status'] ?? $_POST['status'] ?? 'pending') === 'pending')     ? 'selected' : '' ?>>○ Pendiente</option>
                                <option value="in_progress" <?= (($resource['status'] ?? $_POST['status'] ?? '') === 'in_progress') ? 'selected' : '' ?>>⟳ En progreso</option>
                                <option value="completed"   <?= (($resource['status'] ?? $_POST['status'] ?? '') === 'completed')   ? 'selected' : '' ?>>✓ Completado</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-floppy-disk me-1"></i>
                            <?= ($editing ?? false) ? 'Actualizar' : 'Guardar' ?> recurso
                        </button>
                        <a href="<?= BASE_URL ?>?page=resources" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFileField() {
    const type = document.getElementById('typeSelect').value;
    const showFile = type === 'pdf';
    document.getElementById('fileField').style.display = showFile ? '' : 'none';
    document.getElementById('urlField').style.display  = !showFile ? '' : 'none';
}
// Inicializar al cargar
toggleFileField();

// Validación cliente
document.getElementById('resourceForm').addEventListener('submit', function(e) {
    const title     = this.querySelector('[name="title"]').value.trim();
    const subjectId = this.querySelector('[name="subject_id"]').value;
    const fileInput = this.querySelector('[name="file"]');
    if (fileInput && fileInput.files[0] && fileInput.files[0].size > 5 * 1024 * 1024) {
        e.preventDefault();
        alert('El archivo supera 5 MB. Sube uno más pequeño.');
        return;
    }
    if (!title) {
        e.preventDefault();
        alert('El título es obligatorio.');
        return;
    }
    if (!subjectId) {
        e.preventDefault();
        alert('Selecciona una materia.');
    }
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
