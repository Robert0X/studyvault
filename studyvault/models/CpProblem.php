<?php
require_once __DIR__ . '/../config/database.php';

/** Problemas de programación competitiva. */
class CpProblem {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByUser(int $userId, ?string $status = null, string $search = ''): array {
        $sql = "SELECT * FROM cp_problems WHERE user_id = ? AND deleted_at IS NULL";
        $params = [$userId];
        if ($status && in_array($status, ['todo', 'attempted', 'solved', 'upsolved'], true)) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        if ($search !== '') {
            $sql .= " AND (name LIKE ? OR tags LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $sql .= " ORDER BY (rating IS NULL), rating DESC, created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $userId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM cp_problems WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    public function existsByUrl(int $userId, string $url): int|false {
        $stmt = $this->db->prepare("SELECT id FROM cp_problems WHERE user_id = ? AND problem_url = ? AND deleted_at IS NULL");
        $stmt->execute([$userId, $url]);
        $id = $stmt->fetchColumn();
        return $id === false ? false : (int) $id;
    }

    public function create(array $d): int {
        $stmt = $this->db->prepare(
            "INSERT INTO cp_problems (user_id, platform, problem_url, name, rating, tags, status, solved_at, editorial_note)
             VALUES (:user_id, :platform, :url, :name, :rating, :tags, :status, :solved_at, :note)"
        );
        $stmt->execute([
            ':user_id'  => $d['user_id'],
            ':platform' => $d['platform'] ?? 'codeforces',
            ':url'      => $d['problem_url'] ?? null,
            ':name'     => $d['name'] ?? null,
            ':rating'   => $d['rating'] ?? null,
            ':tags'     => $d['tags'] ?? null,
            ':status'   => $d['status'] ?? 'todo',
            ':solved_at'=> ($d['status'] ?? '') === 'solved' ? date('Y-m-d H:i:s') : null,
            ':note'     => $d['editorial_note'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** Inserta o marca como resuelto (usado por la sincronización con Codeforces). */
    public function upsertSolved(int $userId, array $d): string {
        $existing = $this->existsByUrl($userId, $d['problem_url']);
        if ($existing !== false) {
            $stmt = $this->db->prepare(
                "UPDATE cp_problems SET status = 'solved', solved_at = COALESCE(solved_at, NOW()),
                 rating = COALESCE(rating, ?), tags = COALESCE(tags, ?) WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([$d['rating'] ?? null, $d['tags'] ?? null, $existing, $userId]);
            return 'updated';
        }
        $this->create($d + ['user_id' => $userId, 'status' => 'solved']);
        return 'inserted';
    }

    public function setStatus(int $id, int $userId, string $status): bool {
        if (!in_array($status, ['todo', 'attempted', 'solved', 'upsolved'], true)) {
            return false;
        }
        $solvedAt = in_array($status, ['solved', 'upsolved'], true) ? 'COALESCE(solved_at, NOW())' : 'solved_at';
        $stmt = $this->db->prepare("UPDATE cp_problems SET status = ?, solved_at = {$solvedAt} WHERE id = ? AND user_id = ?");
        return $stmt->execute([$status, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE cp_problems SET deleted_at = NOW() WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    public function getStats(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ('solved','upsolved') THEN 1 ELSE 0 END) AS solved,
                SUM(CASE WHEN status='attempted' THEN 1 ELSE 0 END) AS attempted,
                SUM(CASE WHEN status='todo' THEN 1 ELSE 0 END) AS todo
             FROM cp_problems WHERE user_id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: [];
    }

    /** Conteo de problemas resueltos por tag (análisis de fortalezas/debilidades). */
    public function tagBreakdown(int $userId, int $limit = 12): array {
        $rows = $this->db->prepare(
            "SELECT tags FROM cp_problems WHERE user_id = ? AND deleted_at IS NULL
             AND status IN ('solved','upsolved') AND tags IS NOT NULL AND tags <> ''"
        );
        $rows->execute([$userId]);
        $counts = [];
        foreach ($rows->fetchAll(PDO::FETCH_COLUMN) as $tagStr) {
            foreach (array_filter(array_map('trim', explode(',', $tagStr))) as $tag) {
                $counts[$tag] = ($counts[$tag] ?? 0) + 1;
            }
        }
        arsort($counts);
        return array_slice($counts, 0, $limit, true);
    }
}
