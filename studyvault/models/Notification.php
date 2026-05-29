<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Centro de notificaciones in-app.
 * Generadas por el sistema (repasos pendientes, metas atrasadas, racha en riesgo).
 */
class Notification {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(int $userId, string $type, string $title, string $body = '', ?string $link = null, string $icon = 'fa-bell'): int {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, type, title, body, link, icon)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $type, $title, $body, $link, $icon]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Crea una notificación sólo si no existe otra del mismo tipo sin leer en las
     * últimas 24h. Evita inundar al usuario con repetidos del mismo aviso.
     */
    public function createOncePerDay(int $userId, string $type, string $title, string $body = '', ?string $link = null, string $icon = 'fa-bell'): ?int {
        $stmt = $this->db->prepare(
            "SELECT id FROM notifications
             WHERE user_id = ? AND type = ?
               AND created_at >= (NOW() - INTERVAL 1 DAY)
             LIMIT 1"
        );
        $stmt->execute([$userId, $type]);
        if ($stmt->fetchColumn()) {
            return null;
        }
        return $this->create($userId, $type, $title, $body, $link, $icon);
    }

    public function getByUser(int $userId, int $limit = 20, bool $onlyUnread = false): array {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        if ($onlyUnread) {
            $sql .= " AND read_at IS NULL";
        }
        $sql .= " ORDER BY created_at DESC LIMIT " . (int) $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markRead(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET read_at = NOW()
             WHERE id = ? AND user_id = ? AND read_at IS NULL"
        );
        return $stmt->execute([$id, $userId]);
    }

    public function markAllRead(int $userId): int {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL"
        );
        $stmt->execute([$userId]);
        return $stmt->rowCount();
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM notifications WHERE id = ? AND user_id = ?"
        );
        return $stmt->execute([$id, $userId]);
    }
}
