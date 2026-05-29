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
        $stmt = $this->db->prepare("SELECT id, name, email, role, active, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function isActive(int $id): bool {
        $stmt = $this->db->prepare("SELECT active FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() === 1;
    }

    public function setActive(int $id, bool $active): bool {
        $stmt = $this->db->prepare("UPDATE users SET active = ? WHERE id = ?");
        return $stmt->execute([$active ? 1 : 0, $id]);
    }

    public function updatePassword(int $id, string $password): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    /** Listado paginado para el panel admin (con búsqueda por nombre/email). */
    public function paginate(int $page = 1, int $perPage = 20, string $q = ''): array {
        $offset = max(0, ($page - 1) * $perPage);
        $where  = '';
        $params = [];
        if ($q !== '') {
            $where = " WHERE name LIKE ? OR email LIKE ?";
            $like  = "%$q%";
            $params = [$like, $like];
        }
        $stmt = $this->db->prepare(
            "SELECT id, name, email, role, active, created_at
             FROM users{$where}
             ORDER BY created_at DESC
             LIMIT " . (int) $perPage . " OFFSET " . (int) $offset
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $cnt = $this->db->prepare("SELECT COUNT(*) FROM users{$where}");
        $cnt->execute($params);
        return ['rows' => $rows, 'total' => (int) $cnt->fetchColumn()];
    }

    /** Totales globales para el admin (uso de la plataforma). */
    public function globalStats(): array {
        $tables = [
            'users'          => 'users',
            'subjects'       => 'subjects',
            'resources'      => 'resources',
            'flashcards'     => 'flashcards',
            'cp_problems'    => 'cp_problems',
            'goals'          => 'goals',
            'study_sessions' => 'study_sessions',
        ];
        $out = [];
        foreach ($tables as $k => $t) {
            $out[$k] = (int) $this->db->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
        }
        $out['active_users']   = (int) $this->db->query("SELECT COUNT(*) FROM users WHERE active = 1")->fetchColumn();
        $out['total_minutes']  = (int) $this->db->query("SELECT COALESCE(SUM(minutes),0) FROM study_sessions")->fetchColumn();
        return $out;
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

    public function setDailyGoal(int $id, int $minutes): bool {
        $stmt = $this->db->prepare("UPDATE users SET daily_goal_minutes = ? WHERE id = ?");
        return $stmt->execute([max(0, min(600, $minutes)), $id]);
    }

    public function getDailyGoal(int $id): int {
        $stmt = $this->db->prepare("SELECT daily_goal_minutes FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }
}
