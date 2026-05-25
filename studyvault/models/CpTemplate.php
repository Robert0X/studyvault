<?php
require_once __DIR__ . '/../config/database.php';

/** Biblioteca de plantillas / snippets de código para CP. */
class CpTemplate {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM cp_templates WHERE user_id=? AND deleted_at IS NULL ORDER BY title");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $title, string $language, string $code): int {
        $stmt = $this->db->prepare("INSERT INTO cp_templates (user_id, title, language, code) VALUES (?,?,?,?)");
        $stmt->execute([$userId, $title, $language ?: 'cpp', $code]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE cp_templates SET deleted_at = NOW() WHERE id=? AND user_id=?");
        return $stmt->execute([$id, $userId]);
    }
}
