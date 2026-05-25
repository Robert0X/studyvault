<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(string $name, string $email, string $password, string $role = 'student'): int {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$name, $email, $hash, $role]);
        return (int) $this->db->lastInsertId();
    }

    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }

    public function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getCfInfo(int $id): array {
        $stmt = $this->db->prepare("SELECT cf_handle, cf_rating, cf_synced_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: ['cf_handle' => null, 'cf_rating' => null, 'cf_synced_at' => null];
    }

    public function setCfHandle(int $id, string $handle): bool {
        $stmt = $this->db->prepare("UPDATE users SET cf_handle = ? WHERE id = ?");
        return $stmt->execute([$handle, $id]);
    }

    public function setCfSync(int $id, ?int $rating): bool {
        $stmt = $this->db->prepare("UPDATE users SET cf_rating = ?, cf_synced_at = NOW() WHERE id = ?");
        return $stmt->execute([$rating, $id]);
    }
}
