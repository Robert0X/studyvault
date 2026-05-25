<?php
require_once __DIR__ . '/../config/database.php';

class Subject {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT s.*, u.name as owner_name FROM subjects s JOIN users u ON s.user_id = u.id ORDER BY s.name");
        return $stmt->fetchAll();
    }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM subjects WHERE user_id = ? ORDER BY name");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(string $name, string $description, string $icon, string $color, int $userId): int {
        $stmt = $this->db->prepare(
            "INSERT INTO subjects (name, description, icon, color, user_id) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $description, $icon, $color, $userId]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $name, string $description, string $icon, string $color, int $userId): bool {
        $stmt = $this->db->prepare(
            "UPDATE subjects SET name=?, description=?, icon=?, color=? WHERE id=? AND user_id=?"
        );
        return $stmt->execute([$name, $description, $icon, $color, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM subjects WHERE id=? AND user_id=?");
        return $stmt->execute([$id, $userId]);
    }

    public function getWithStats(int $userId, bool $isAdmin = false): array {
        if ($isAdmin) {
            $sql = "SELECT s.*,
                        COUNT(r.id) as total_resources,
                        SUM(CASE WHEN r.status='completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN r.status='in_progress' THEN 1 ELSE 0 END) as in_progress,
                        SUM(CASE WHEN r.status='pending' THEN 1 ELSE 0 END) as pending
                    FROM subjects s
                    LEFT JOIN resources r ON r.subject_id = s.id
                    WHERE s.user_id = ?
                    GROUP BY s.id ORDER BY s.name";
        } else {
            $sql = "SELECT s.*,
                        COUNT(r.id) as total_resources,
                        SUM(CASE WHEN r.status='completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN r.status='in_progress' THEN 1 ELSE 0 END) as in_progress,
                        SUM(CASE WHEN r.status='pending' THEN 1 ELSE 0 END) as pending
                    FROM subjects s
                    LEFT JOIN resources r ON r.subject_id = s.id AND r.user_id = ?
                    WHERE s.user_id = ?
                    GROUP BY s.id ORDER BY s.name";
        }
        $stmt = $this->db->prepare($sql);
        if ($isAdmin) {
            $stmt->execute([$userId]);
        } else {
            $stmt->execute([$userId, $userId]);
        }
        return $stmt->fetchAll();
    }
}
