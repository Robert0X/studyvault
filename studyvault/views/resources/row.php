<tr id="row-<?= $r['id'] ?>">
    <td>
        <div class="d-flex align-items-center gap-2">
            <div class="sv-resource-icon"
                 style="background-color:<?= htmlspecialchars($r['subject_color']) ?>20; color:<?= htmlspecialchars($r['subject_color']) ?>">
                <i class="fa-solid <?= $typeIcons[$r['type']] ?? 'fa-file' ?>"></i>
            </div>
            <div>
                <div class="fw-semibold"><?= htmlspecialchars($r['title']) ?></div>
                <?php if (!empty($r['description'])): ?>
                    <small class="text-muted">
                        <?= htmlspecialchars(mb_substr($r['description'], 0, 60)) ?><?= mb_strlen($r['description']) > 60 ? '…' : '' ?>
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </td>
    <td class="d-none d-md-table-cell">
        <span class="badge rounded-pill"
              style="background-color:<?= htmlspecialchars($r['subject_color']) ?>20; color:<?= htmlspecialchars($r['subject_color']) ?>; border:1px solid <?= htmlspecialchars($r['subject_color']) ?>40">
            <i class="fa-solid <?= htmlspecialchars($r['subject_icon']) ?> me-1"></i>
            <?= htmlspecialchars($r['subject_name']) ?>
        </span>
    </td>
    <td class="d-none d-sm-table-cell">
        <span class="badge bg-secondary bg-opacity-15 text-secondary">
            <i class="fa-solid <?= $typeIcons[$r['type']] ?? 'fa-file' ?> me-1"></i>
            <?= htmlspecialchars($typeLabels[$r['type']] ?? $r['type']) ?>
        </span>
    </td>
    <td>
        <select class="form-select form-select-sm status-select w-auto"
                onchange="changeStatus(<?= $r['id'] ?>, this.value)"
                style="border-color: var(--bs-<?= $statusBadge[$r['status']]['class'] ?>)">
            <option value="pending"     <?= $r['status'] === 'pending'     ? 'selected' : '' ?>>○ Pendiente</option>
            <option value="in_progress" <?= $r['status'] === 'in_progress' ? 'selected' : '' ?>>⟳ En progreso</option>
            <option value="completed"   <?= $r['status'] === 'completed'   ? 'selected' : '' ?>>✓ Completado</option>
        </select>
    </td>
    <td class="text-end">
        <?php if (!empty($r['url'])): ?>
            <a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" rel="noopener"
               class="btn btn-sm btn-outline-primary me-1" title="Abrir enlace">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </a>
        <?php endif; ?>
        <?php if (!empty($r['file_path'])): ?>
            <a href="<?= BASE_URL ?>assets/uploads/<?= htmlspecialchars($r['file_path']) ?>" target="_blank"
               class="btn btn-sm btn-outline-info me-1" title="Ver archivo">
                <i class="fa-solid fa-file-arrow-down"></i>
            </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>?page=resources&action=edit&id=<?= $r['id'] ?>"
           class="btn btn-sm btn-outline-warning me-1" title="Editar">
            <i class="fa-solid fa-pen"></i>
        </a>
        <button class="btn btn-sm btn-outline-danger"
                onclick="deleteResource(<?= $r['id'] ?>)" title="Eliminar">
            <i class="fa-solid fa-trash"></i>
        </button>
    </td>
</tr>
