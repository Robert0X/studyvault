<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Tarjetas de estudio con repetición espaciada (algoritmo SM-2).
 * Genérica: sirve para vocabulario (deck='vocab') y CP (deck='cp').
 */
class Flashcard {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $d): int {
        $stmt = $this->db->prepare(
            "INSERT INTO flashcards (user_id, deck, front, back, example, extra, source, cefr_level, due_date)
             VALUES (:user_id, :deck, :front, :back, :example, :extra, :source, :cefr_level, CURRENT_DATE)"
        );
        $stmt->execute([
            ':user_id'   => $d['user_id'],
            ':deck'      => $d['deck'] ?? 'vocab',
            ':front'     => $d['front'],
            ':back'      => $d['back'],
            ':example'   => $d['example'] ?? null,
            ':extra'     => $d['extra'] ?? null,
            ':source'    => $d['source'] ?? 'manual',
            ':cefr_level'=> $d['cefr_level'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getByUser(int $userId, ?string $deck = null): array {
        $sql = "SELECT * FROM flashcards WHERE user_id = ? AND deleted_at IS NULL";
        $params = [$userId];
        if ($deck) {
            $sql .= " AND deck = ?";
            $params[] = $deck;
        }
        $sql .= " ORDER BY due_date ASC, created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id, int $userId): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM flashcards WHERE id = ? AND user_id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$id, $userId]);
        return $stmt->fetch();
    }

    /** Tarjetas que tocan repasar hoy o antes. */
    public function getDue(int $userId, ?string $deck = null, ?string $level = null, int $limit = 50): array {
        $sql = "SELECT * FROM flashcards
                WHERE user_id = ? AND deleted_at IS NULL AND due_date <= CURRENT_DATE";
        $params = [$userId];
        if ($deck) {
            $sql .= " AND deck = ?";
            $params[] = $deck;
        }
        if ($level) {
            $sql .= " AND cefr_level = ?";
            $params[] = $level;
        }
        $sql .= " ORDER BY due_date ASC LIMIT " . (int) $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countDue(int $userId): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM flashcards
             WHERE user_id = ? AND deleted_at IS NULL AND due_date <= CURRENT_DATE"
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Procesa un repaso con el algoritmo SM-2.
     * $quality: 0-5 (Otra vez=2, Difícil=3, Bien=4, Fácil=5).
     */
    public function review(int $id, int $userId, int $quality): bool {
        $card = $this->findById($id, $userId);
        if (!$card) {
            return false;
        }
        $r = self::sm2((float) $card['ease_factor'], (int) $card['repetitions'], (int) $card['interval_days'], $quality);
        $dueDate = date('Y-m-d', strtotime("+{$r['interval_days']} days"));

        $stmt = $this->db->prepare(
            "UPDATE flashcards
             SET ease_factor = ?, interval_days = ?, repetitions = ?,
                 due_date = ?, status = ?, last_reviewed_at = NOW()
             WHERE id = ? AND user_id = ?"
        );
        return $stmt->execute([$r['ease_factor'], $r['interval_days'], $r['repetitions'], $dueDate, $r['status'], $id, $userId]);
    }

    /**
     * Algoritmo SM-2 (puro, sin BD). Calcula los nuevos parámetros de repaso.
     * @param int $quality 0-5 (Otra vez<3, Difícil=3, Bien=4, Fácil=5)
     * @return array{ease_factor:float,repetitions:int,interval_days:int,status:string}
     */
    public static function sm2(float $ef, int $reps, int $interval, int $quality): array {
        $quality = max(0, min(5, $quality));
        if ($quality < 3) {
            $reps = 0;
            $interval = 1;
        } else {
            if ($reps === 0)     $interval = 1;
            elseif ($reps === 1) $interval = 6;
            else                 $interval = (int) round($interval * $ef);
            $reps++;
        }
        $ef = $ef + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        if ($ef < 1.3) {
            $ef = 1.3;
        }
        $status = $interval >= 90 ? 'mastered' : ($interval >= 21 ? 'mature' : 'learning');
        return ['ease_factor' => $ef, 'repetitions' => $reps, 'interval_days' => $interval, 'status' => $status];
    }

    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare(
            "UPDATE flashcards SET deleted_at = NOW() WHERE id = ? AND user_id = ?"
        );
        return $stmt->execute([$id, $userId]);
    }

    public function reviewedSince(int $userId, int $days): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM flashcards WHERE user_id=? AND last_reviewed_at >= (NOW() - INTERVAL ? DAY)");
        $stmt->execute([$userId, $days]);
        return (int) $stmt->fetchColumn();
    }

    public function getStats(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status='new' THEN 1 ELSE 0 END) AS news,
                SUM(CASE WHEN status='learning' THEN 1 ELSE 0 END) AS learning,
                SUM(CASE WHEN status='mature' THEN 1 ELSE 0 END) AS mature,
                SUM(CASE WHEN status='mastered' THEN 1 ELSE 0 END) AS mastered,
                SUM(CASE WHEN due_date <= CURRENT_DATE THEN 1 ELSE 0 END) AS due
             FROM flashcards WHERE user_id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: [];
    }
}
