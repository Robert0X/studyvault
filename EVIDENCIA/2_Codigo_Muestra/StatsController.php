<?php
require_once __DIR__ . '/../models/StudySession.php';
require_once __DIR__ . '/../models/Flashcard.php';
require_once __DIR__ . '/../models/CpProblem.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Gráficas estadísticas (Chart.js).
 * - index(): renderiza la vista con los canvas.
 * - feed(): JSON con todos los datasets que consume la página.
 */
class StatsController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index(): void {
        requireLogin();
        require __DIR__ . '/../views/stats/index.php';
    }

    public function feed(): void {
        requireLogin();
        $userId = (int) $_SESSION['user_id'];

        json_response([
            'success'        => true,
            'studyByDay'     => $this->studyByDay($userId, 30),
            'cardsByStatus'  => $this->cardsByStatus($userId),
            'cardsByLevel'   => $this->cardsByLevel($userId),
            'cpByTag'        => $this->cpByTag($userId, 8),
            'cpRatingHistory'=> $this->cpRatingHistory($userId),
            'cpSolvedRating' => $this->cpSolvedByRating($userId),
        ]);
    }

    /** Minutos estudiados por día (últimos N días, relleno con 0). */
    private function studyByDay(int $userId, int $days): array {
        $rows = (new StudySession())->minutesByDay($userId, $days);
        $labels = [];
        $data   = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i day"));
            $labels[] = date('d/m', strtotime($d));
            $data[]   = (int) ($rows[$d] ?? 0);
        }
        return ['labels' => $labels, 'data' => $data];
    }

    /** Tarjetas por estado del SM-2 (new / learning / mature / mastered). */
    private function cardsByStatus(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT status, COUNT(*) AS n FROM flashcards
             WHERE user_id = ? AND deleted_at IS NULL
             GROUP BY status"
        );
        $stmt->execute([$userId]);
        $map = ['new' => 0, 'learning' => 0, 'mature' => 0, 'mastered' => 0];
        foreach ($stmt->fetchAll() as $r) {
            $map[$r['status']] = (int) $r['n'];
        }
        return [
            'labels' => ['Nuevas', 'Aprendiendo', 'Maduras', 'Dominadas'],
            'data'   => [$map['new'], $map['learning'], $map['mature'], $map['mastered']],
        ];
    }

    /** Vocabulario por nivel CEFR (A1..C2). */
    private function cardsByLevel(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT cefr_level, COUNT(*) AS n FROM flashcards
             WHERE user_id = ? AND deleted_at IS NULL
             GROUP BY cefr_level"
        );
        $stmt->execute([$userId]);
        $levels = ['A1' => 0, 'A2' => 0, 'B1' => 0, 'B2' => 0, 'C1' => 0, 'C2' => 0, 'Sin nivel' => 0];
        foreach ($stmt->fetchAll() as $r) {
            $k = $r['cefr_level'] ?: 'Sin nivel';
            $levels[$k] = (int) $r['n'];
        }
        return [
            'labels' => array_keys($levels),
            'data'   => array_values($levels),
        ];
    }

    /** Problemas CP resueltos por tag (top N). */
    private function cpByTag(int $userId, int $limit): array {
        $counts = (new CpProblem())->tagBreakdown($userId, $limit);
        return [
            'labels' => array_keys($counts),
            'data'   => array_values($counts),
        ];
    }

    /**
     * Curva de rating de Codeforces.
     * Como sólo guardamos el rating actual, dibujamos una línea con el último valor;
     * deja la base lista para acumular un histórico si en el futuro se persiste.
     */
    private function cpRatingHistory(int $userId): array {
        $stmt = $this->db->prepare("SELECT cf_rating, cf_synced_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        if (!$row || $row['cf_rating'] === null) {
            return ['labels' => [], 'data' => []];
        }
        $when = $row['cf_synced_at'] ?: date('Y-m-d');
        return [
            'labels' => ['Inicio', date('d/m', strtotime($when))],
            'data'   => [0, (int) $row['cf_rating']],
        ];
    }

    /** Distribución por rating de problemas resueltos (histograma). */
    private function cpSolvedByRating(int $userId): array {
        $stmt = $this->db->prepare(
            "SELECT rating FROM cp_problems
             WHERE user_id = ? AND deleted_at IS NULL
               AND status IN ('solved','upsolved') AND rating IS NOT NULL"
        );
        $stmt->execute([$userId]);
        $buckets = [];
        for ($r = 800; $r <= 3500; $r += 200) {
            $buckets[$r] = 0;
        }
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $rating) {
            $b = (int) (floor($rating / 200) * 200);
            $b = max(800, min(3500, $b));
            $buckets[$b] = ($buckets[$b] ?? 0) + 1;
        }
        return [
            'labels' => array_map(fn($r) => $r . '+', array_keys($buckets)),
            'data'   => array_values($buckets),
        ];
    }
}
