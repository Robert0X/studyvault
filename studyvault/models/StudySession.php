<?php
require_once __DIR__ . '/../config/database.php';

/** Sesiones de estudio (tiempo real registrado, p. ej. desde el Pomodoro). */
class StudySession {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(int $userId, ?int $resourceId, ?int $unitId, int $minutes, string $technique = 'pomodoro'): int {
        if (!in_array($technique, ['read', 'practice', 'review', 'pomodoro'], true)) {
            $technique = 'pomodoro';
        }
        $minutes = max(1, min(600, $minutes));
        $stmt = $this->db->prepare(
            "INSERT INTO study_sessions (user_id, resource_id, unit_id, ended_at, minutes, technique)
             VALUES (?, ?, ?, NOW(), ?, ?)"
        );
        $stmt->execute([$userId, $resourceId ?: null, $unitId ?: null, $minutes, $technique]);
        return (int) $this->db->lastInsertId();
    }

    public function todayMinutes(int $userId): int {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(minutes),0) FROM study_sessions WHERE user_id=? AND DATE(started_at)=CURRENT_DATE");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function rangeMinutes(int $userId, int $days): int {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(minutes),0) FROM study_sessions WHERE user_id=? AND started_at >= (NOW() - INTERVAL ? DAY)");
        $stmt->execute([$userId, $days]);
        return (int) $stmt->fetchColumn();
    }

    /** [ 'YYYY-MM-DD' => minutos ] de los últimos $days días. */
    public function minutesByDay(int $userId, int $days = 84): array {
        $stmt = $this->db->prepare(
            "SELECT DATE(started_at) AS d, SUM(minutes) AS m
             FROM study_sessions WHERE user_id=? AND started_at >= (NOW() - INTERVAL ? DAY)
             GROUP BY DATE(started_at)"
        );
        $stmt->execute([$userId, $days]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[$row['d']] = (int) $row['m'];
        }
        return $out;
    }

    /** Racha de días consecutivos (hasta hoy) con al menos una sesión. */
    public function streak(int $userId): int {
        $byDay = $this->minutesByDay($userId, 400);
        $streak = 0;
        $day = strtotime('today');
        // Si hoy aún no hay estudio, la racha se cuenta desde ayer.
        if (empty($byDay[date('Y-m-d', $day)])) {
            $day = strtotime('-1 day', $day);
        }
        while (!empty($byDay[date('Y-m-d', $day)])) {
            $streak++;
            $day = strtotime('-1 day', $day);
        }
        return $streak;
    }

    public function recent(int $userId, int $limit = 5): array {
        $stmt = $this->db->prepare(
            "SELECT s.*, r.title AS resource_title
             FROM study_sessions s LEFT JOIN resources r ON s.resource_id = r.id
             WHERE s.user_id=? ORDER BY s.started_at DESC LIMIT " . (int) $limit
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
