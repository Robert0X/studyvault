<?php
$pageTitle = 'Notificaciones';
require __DIR__ . '/../partials/header.php';
?>

<div class="sv-page-header">
    <div>
        <h2 class="mb-0"><i class="fa-solid fa-bell me-2"></i>Notificaciones</h2>
        <p class="text-muted mb-0">Recordatorios automáticos de tu progreso</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" id="enablePushBtn" onclick="enableBrowserNotifications()">
            <i class="fa-regular fa-bell me-1"></i>Activar avisos del navegador
        </button>
        <button class="btn btn-outline-primary" onclick="markAllRead()">
            <i class="fa-solid fa-check-double me-1"></i>Marcar todas como leídas
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-regular fa-bell fa-3x mb-3 opacity-50"></i>
                <h5>Sin notificaciones</h5>
                <p class="mb-0">Cuando tengas repasos pendientes, metas atrasadas o tu racha esté en riesgo, aparecerán aquí.</p>
            </div>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                    <li class="list-group-item d-flex align-items-start gap-3 <?= empty($n['read_at']) ? 'sv-notif-unread' : '' ?>" id="notif-<?= (int) $n['id'] ?>">
                        <div class="sv-stat-icon bg-primary bg-opacity-15 text-primary" style="width:38px;height:38px;font-size:1rem">
                            <i class="fa-solid <?= htmlspecialchars($n['icon']) ?>"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between gap-2">
                                <strong class="text-truncate"><?= htmlspecialchars($n['title']) ?></strong>
                                <small class="text-muted text-nowrap"><?= htmlspecialchars($n['created_at']) ?></small>
                            </div>
                            <?php if (!empty($n['body'])): ?>
                                <p class="mb-1 small text-muted"><?= htmlspecialchars($n['body']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($n['link'])): ?>
                                <a href="<?= htmlspecialchars($n['link']) ?>" class="small text-decoration-none">Ir <i class="fa-solid fa-arrow-right fa-xs"></i></a>
                            <?php endif; ?>
                        </div>
                        <div class="text-end d-flex flex-column gap-1">
                            <?php if (empty($n['read_at'])): ?>
                                <button class="btn btn-sm btn-outline-secondary" onclick="markRead(<?= (int) $n['id'] ?>)" title="Marcar como leída"><i class="fa-solid fa-check"></i></button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteNotif(<?= (int) $n['id'] ?>)" title="Eliminar"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<script>
function notifPost(action, fields) {
    const body = new FormData();
    body.append('_action', action);
    body.append('csrf_token', CSRF_TOKEN);
    for (const k in fields) body.append(k, fields[k]);
    return fetch(BASE_URL + '?page=notifications', { method: 'POST', body }).then(r => r.json());
}
function markRead(id) {
    notifPost('mark_read', { id }).then(d => { if (d.success) document.getElementById('notif-' + id)?.classList.remove('sv-notif-unread'); });
}
function markAllRead() { notifPost('mark_all', {}).then(() => location.reload()); }
function deleteNotif(id) {
    if (!confirm('¿Eliminar esta notificación?')) return;
    notifPost('destroy', { id }).then(d => { if (d.success) document.getElementById('notif-' + id)?.remove(); });
}

function enableBrowserNotifications() {
    if (!('Notification' in window)) { alert('Tu navegador no soporta notificaciones.'); return; }
    Notification.requestPermission().then(p => {
        if (p === 'granted') {
            localStorage.setItem('sv-notif-enabled', '1');
            new Notification('StudyVault', { body: 'Te avisaremos cuando tengas repasos o metas atrasadas.' });
        }
    });
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
