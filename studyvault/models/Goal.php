<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Unit.php';

/** Metas / planes de estudio que agrupan varios recursos con peso. */
class Goal {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Progreso ponderado (puro). $items = [['pct'=>int,'weight'=>int], ...] */
    public static function weightedProgress(array $items): int {
        $totalWeight = 0;
        $acc = 0;
        foreach ($items as $it) {
            $w = (int) ($it['weight'] ?? 1);
            if ($w <= 0) {
                continue;
            }
            $totalWeight += $w;
            $acc += $w * (int) ($it['pct'] ?? 0);
        }
        return $totalWeight > 0 ? (int) round($acc / $totalWeight) : 0;
    }

    /** Ritmo hacia la meta (puro). Devuelve si vas a tiempo según la fecha objetivo. */
    public static function pace(?string $createdAt, ?string $targetDate, int $currentPct, string $today): array {
        if (!$targetDate) {
            return ['has_target' => false, 'expected_pct' => 0, 'on_track' => true, 'days_left' => 0, 'label' => 'Sin fecha límite'];
        }
        $start = $createdAt ? strtotime(substr($createdAt, 0, 10)) : strtotime($today);
        $end   = strtotime($targetDate);
        $now   = strtotime($today);
        $total = max(1, $end - $start);
        $elapsed = min($total, max(0, $now - $start));
        $expected = max(0, min(100, (int) round($elapsed / $total * 100)));
        $daysLeft = (int) ceil(($end - $now) / 86400);
        $onTrack = $currentPct >= $expected;
        if ($currentPct >= 100)      $label = 'Completada';
        elseif ($daysLeft < 0)       $label = 'Fecha vencida';
        elseif ($onTrack)            $label = 'En camino';
        else                         $label = 'Atrasado';
        return ['has_target' => true, 'expected_pct' => $expected, 'on_track' => $onTrack, 'days_left' => $daysLeft, 'label' => $label];
    }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM goals WHERE user_id = ? AND deleted_at IS NULL ORDER BY status, target_date IS NULL, target_date");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $userId): array|false {
        $stmt = $this->db->prepare("SELECT * FROM goals WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    public function create(int $userId, string $title, string $desc, ?string $targetDate): int {
        $stmt = $this->db->prepare("INSERT INTO goals (user_id, title, description, target_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $desc, $targetDate ?: null]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, string $title, string $desc, ?string $targetDate, string $status): bool {
        if (!in_array($status, ['active', 'paused', 'done'], true)) {
            $status = 'active';
        }
        $stmt = $this->db->prepare("UPDATE goals SET title=?, description=?, target_date=?, status=? WHERE id=? AND user_id=?");
        return $stmt->execute([$title, $desc, $targetDate ?: null, $status, $id, $userId]);
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("UPDATE goals SET deleted_at = NOW() WHERE id=? AND user_id=?");
        return $stmt->execute([$id, $userId]);
    }

    public function attachResource(int $goalId, int $resourceId, int $weight): bool {
        $stmt = $this->db->prepare("REPLACE INTO goal_resources (goal_id, resource_id, weight) VALUES (?, ?, ?)");
        return $stmt->execute([$goalId, $resourceId, max(1, $weight)]);
    }

    public function detachResource(int $goalId, int $resourceId): bool {
        $stmt = $this->db->prepare("DELETE FROM goal_resources WHERE goal_id=? AND resource_id=?");
        return $stmt->execute([$goalId, $resourceId]);
    }

    /** Recursos de la meta con su % calculado (unidades si las hay, si no por estado). */
    public function resourcesWithProgress(int $goalId): array {
        $stmt = $this->db->prepare(
            "SELECT r.id, r.title, r.status, r.total_units, gr.weight,
                    (SELECT COUNT(*) FROM units u WHERE u.resource_id = r.id AND u.status='completed') AS done
             FROM goal_resources gr
             JOIN resources r ON gr.resource_id = r.id
             WHERE gr.goal_id = ? AND r.deleted_at IS NULL"
        );
        $stmt->execute([$goalId]);
        $statusPct = ['completed' => 100, 'in_progress' => 50, 'pending' => 0];
        $out = [];
        foreach ($stmt->fetchAll() as $r) {
            $pct = (int) $r['total_units'] > 0
                ? Unit::progressPct((int) $r['done'], (int) $r['total_units'])
                : ($statusPct[$r['status']] ?? 0);
            $out[] = ['id' => (int) $r['id'], 'title' => $r['title'], 'weight' => (int) $r['weight'], 'pct' => $pct];
        }
        return $out;
    }

    public function progress(int $goalId): int {
        return self::weightedProgress($this->resourcesWithProgress($goalId));
    }

    /** Metas del usuario con % y pace, listas para el dashboard / listado. */
    public function listWithProgress(int $userId, string $today): array {
        $goals = $this->getByUser($userId);
        foreach ($goals as &$g) {
            $g['pct']  = $this->progress((int) $g['id']);
            $g['pace'] = self::pace($g['created_at'] ?? null, $g['target_date'] ?? null, $g['pct'], $today);
        }
        return $goals;
    }

    /** Recursos del usuario que aún no están en la meta. */
    public function availableResources(int $userId, int $goalId): array {
        $stmt = $this->db->prepare(
            "SELECT id, title FROM resources
             WHERE user_id = ? AND deleted_at IS NULL
               AND id NOT IN (SELECT resource_id FROM goal_resources WHERE goal_id = ?)
             ORDER BY title"
        );
        $stmt->execute([$userId, $goalId]);
        return $stmt->fetchAll();
    }

    // ── Compartir (B7): plantillas públicas + clonado ──
    public function setPublic(int $id, int $userId, bool $public): bool {
        $stmt = $this->db->prepare("UPDATE goals SET is_public = ? WHERE id = ? AND user_id = ?");
        return $stmt->execute([$public ? 1 : 0, $id, $userId]);
    }

    public function findPublic(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM goals WHERE id = ? AND is_public = 1 AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Plantillas públicas de otros usuarios. */
    public function getPublicTemplates(int $currentUserId = 0, int $limit = 40): array {
        $stmt = $this->db->prepare(
            "SELECT g.id, g.title, g.description, g.target_date, g.user_id AS owner_id, u.name AS owner,
                    (SELECT COUNT(*) FROM goal_resources gr WHERE gr.goal_id = g.id) AS resource_count
             FROM goals g JOIN users u ON g.user_id = u.id
             WHERE g.is_public = 1 AND g.deleted_at IS NULL
             ORDER BY g.created_at DESC LIMIT " . (int) $limit
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Clona una plantilla pública en la cuenta del usuario (copia meta + recursos como propios). */
    public function cloneForUser(int $goalId, int $userId): int|false {
        $src = $this->findPublic($goalId);
        if (!$src) {
            return false;
        }
        $this->db->beginTransaction();
        try {
            $newGoalId = $this->create($userId, $src['title'], (string) ($src['description'] ?? ''), $src['target_date'] ?: null);
            $this->db->prepare("UPDATE goals SET cloned_from = ? WHERE id = ?")->execute([$goalId, $newGoalId]);

            // Materia destino "Importado" del usuario (crear si no existe)
            $q = $this->db->prepare("SELECT id FROM subjects WHERE user_id = ? AND name = 'Importado' AND deleted_at IS NULL LIMIT 1");
            $q->execute([$userId]);
            $subjectId = $q->fetchColumn();
            if (!$subjectId) {
                $this->db->prepare("INSERT INTO subjects (name, description, icon, color, user_id) VALUES ('Importado','Recursos de plantillas clonadas','fa-download','#64748b',?)")->execute([$userId]);
                $subjectId = (int) $this->db->lastInsertId();
            }

            // Copiar recursos del origen como recursos propios (sin archivos)
            $rs = $this->db->prepare(
                "SELECT r.title, r.url, r.type, r.description, gr.weight
                 FROM goal_resources gr JOIN resources r ON gr.resource_id = r.id
                 WHERE gr.goal_id = ? AND r.deleted_at IS NULL"
            );
            $rs->execute([$goalId]);
            $insR = $this->db->prepare("INSERT INTO resources (title, description, url, type, subject_id, user_id, status) VALUES (?,?,?,?,?,?, 'pending')");
            $attach = $this->db->prepare("INSERT INTO goal_resources (goal_id, resource_id, weight) VALUES (?,?,?)");
            foreach ($rs->fetchAll() as $r) {
                $insR->execute([$r['title'], $r['description'], $r['url'], $r['type'] ?: 'link', $subjectId, $userId]);
                $attach->execute([$newGoalId, (int) $this->db->lastInsertId(), max(1, (int) $r['weight'])]);
            }
            $this->db->commit();
            return $newGoalId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('[goal.clone] ' . $e->getMessage());
            return false;
        }
    }
}
