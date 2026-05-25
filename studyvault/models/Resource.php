<?php
require_once __DIR__ . '/../config/database.php';

class Resource {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByUser(int $userId, ?int $subjectId = null, ?string $type = null, ?string $status = null, string $search = ''): array {
        $sql = "SELECT r.*, s.name as subject_name, s.color as subject_color, s.icon as subject_icon
                FROM resources r
                JOIN subjects s ON r.subject_id = s.id
                WHERE r.user_id = ?";
        $params = [$userId];

        if ($subjectId) {
            $sql .= " AND r.subject_id = ?";
            $params[] = $subjectId;
        }
        if ($type) {
            $sql .= " AND r.type = ?";
            $params[] = $type;
        }
        if ($status) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }
        if ($search !== '') {
            $sql .= " AND (r.title LIKE ? OR r.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $userId): array|false {
        $stmt = $this->db->prepare(
            "SELECT r.*, s.name as subject_name FROM resources r JOIN subjects s ON r.subject_id = s.id WHERE r.id=? AND r.user_id=?"
        );
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO resources (title, description, url, type, file_path, subject_id, user_id, status)
             VALUES (:title, :description, :url, :type, :file_path, :subject_id, :user_id, :status)"
        );
        $stmt->execute([
            ':title'      => $data['title'],
            ':description'=> $data['description'] ?? '',
            ':url'        => $data['url'] ?? null,
            ':type'       => $data['type'],
            ':file_path'  => $data['file_path'] ?? null,
            ':subject_id' => $data['subject_id'],
            ':user_id'    => $data['user_id'],
            ':status'     => $data['status'] ?? 'pending',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data, int $userId): bool {
        $stmt = $this->db->prepare(
            "UPDATE resources SET title=:title, description=:description, url=:url, type=:type,
             file_path=:file_path, subject_id=:subject_id, status=:status
             WHERE id=:id AND user_id=:user_id"
        );
        return $stmt->execute([
            ':title'      => $data['title'],
            ':description'=> $data['description'] ?? '',
            ':url'        => $data['url'] ?? null,
            ':type'       => $data['type'],
            ':file_path'  => $data['file_path'] ?? null,
            ':subject_id' => $data['subject_id'],
            ':status'     => $data['status'],
            ':id'         => $id,
            ':user_id'    => $userId,
        ]);
    }

    public function delete(int $id, int $userId): array|false {
        $resource = $this->findById($id, $userId);
        if (!$resource) return false;
        $stmt = $this->db->prepare("DELETE FROM resources WHERE id=? AND user_id=?");
        $stmt->execute([$id, $userId]);
        return $resource;
    }

    public function changeStatus(int $id, string $status, int $userId): bool {
        $allowed = ['pending', 'in_progress', 'completed'];
        if (!in_array($status, $allowed)) return false;
        $stmt = $this->db->prepare("UPDATE resources SET status=? WHERE id=? AND user_id=?");
        return $stmt->execute([$status, $id, $userId]);
    }

    public function getStats(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status='in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending
             FROM resources WHERE user_id=?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function getRecent(int $userId, int $limit = 5): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, s.name as subject_name, s.color as subject_color, s.icon as subject_icon
             FROM resources r JOIN subjects s ON r.subject_id = s.id
             WHERE r.user_id=? ORDER BY r.created_at DESC LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }
}
