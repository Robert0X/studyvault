<?php
require_once __DIR__ . '/../config/database.php';

/** Unidades (capítulos/lecciones) dentro de un recurso. Resuelve el progreso granular del "libro enorme". */
class Unit {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Porcentaje completado (puro, sin BD). */
    public static function progressPct(int $completed, int $total): int {
        if ($total <= 0) {
            return 0;
        }
        return (int) round($completed / $total * 100);
    }

    /** Devuelve el recurso si pertenece al usuario y no está borrado, o false. */
    public function resourceOwned(int $resourceId, int $userId): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM resources WHERE id = ? AND user_id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$resourceId, $userId]);
        return $stmt->fetch();
    }

    /** Devuelve la unidad si su recurso pertenece al usuario, o false. */
    public function unitOwned(int $unitId, int $userId): array|false {
        $stmt = $this->db->prepare(
            "SELECT u.* FROM units u
             JOIN resources r ON u.resource_id = r.id
             WHERE u.id = ? AND r.user_id = ? AND r.deleted_at IS NULL"
        );
        $stmt->execute([$unitId, $userId]);
        return $stmt->fetch();
    }

    public function getByResource(int $resourceId): array {
        $stmt = $this->db->prepare("SELECT * FROM units WHERE resource_id = ? ORDER BY order_index, id");
        $stmt->execute([$resourceId]);
        return $stmt->fetchAll();
    }

    /** [total, completed, pct] del recurso. */
    public function progress(int $resourceId): array {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed
             FROM units WHERE resource_id = ?"
        );
        $stmt->execute([$resourceId]);
        $row = $stmt->fetch() ?: ['total' => 0, 'completed' => 0];
        $total = (int) $row['total'];
        $completed = (int) $row['completed'];
        return ['total' => $total, 'completed' => $completed, 'pct' => self::progressPct($completed, $total)];
    }

    public function create(int $resourceId, string $title, ?int $pageFrom = null, ?int $pageTo = null): int {
        $order = (int) $this->db->query("SELECT COALESCE(MAX(order_index), 0) + 1 FROM units WHERE resource_id = " . (int) $resourceId)->fetchColumn();
        $stmt = $this->db->prepare(
            "INSERT INTO units (resource_id, title, order_index, page_from, page_to) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$resourceId, $title, $order, $pageFrom, $pageTo]);
        $this->syncCount($resourceId);
        return (int) $this->db->lastInsertId();
    }

    /** Genera N unidades (p. ej. "Capítulo 1..N") de una vez. */
    public function bulkCreate(int $resourceId, int $count, string $prefix = 'Capítulo'): int {
        $count = max(1, min(200, $count));
        $start = (int) $this->db->query("SELECT COALESCE(MAX(order_index), 0) FROM units WHERE resource_id = " . (int) $resourceId)->fetchColumn();
        $stmt = $this->db->prepare("INSERT INTO units (resource_id, title, order_index) VALUES (?, ?, ?)");
        for ($i = 1; $i <= $count; $i++) {
            $stmt->execute([$resourceId, "{$prefix} {$i}", $start + $i]);
        }
        $this->syncCount($resourceId);
        return $count;
    }

    public function setStatus(int $unitId, string $status): bool {
        if (!in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            return false;
        }
        $completedAt = $status === 'completed' ? 'NOW()' : 'NULL';
        $stmt = $this->db->prepare("UPDATE units SET status = ?, completed_at = {$completedAt} WHERE id = ?");
        return $stmt->execute([$status, $unitId]);
    }

    public function delete(int $unitId, int $resourceId): bool {
        $stmt = $this->db->prepare("DELETE FROM units WHERE id = ?");
        $ok = $stmt->execute([$unitId]);
        $this->syncCount($resourceId);
        return $ok;
    }

    /** Mantiene resources.total_units sincronizado para mostrar progreso en listados. */
    private function syncCount(int $resourceId): void {
        $stmt = $this->db->prepare(
            "UPDATE resources SET total_units = (SELECT COUNT(*) FROM units WHERE resource_id = ?) WHERE id = ?"
        );
        $stmt->execute([$resourceId, $resourceId]);
    }
}
