<?php
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Flashcard.php';
require_once __DIR__ . '/../models/CpProblem.php';
require_once __DIR__ . '/../models/Goal.php';
require_once __DIR__ . '/../models/StudySession.php';
require_once __DIR__ . '/../models/User.php';

/**
 * Centro de notificaciones.
 * - index(): vista con todas las notificaciones.
 * - feed(): JSON para el badge/dropdown del navbar.
 * - generate(): genera recordatorios derivados del estado actual
 *   (repasos de hoy, metas atrasadas, racha en riesgo, meta diaria sin cubrir).
 */
class NotificationController {
    private Notification $model;

    public function __construct() {
        $this->model = new Notification();
    }

    public function index(): void {
        requireLogin();
        $userId = (int) $_SESSION['user_id'];
        $this->generate($userId);
        $notifications = $this->model->getByUser($userId, 50);
        require __DIR__ . '/../views/notifications/index.php';
    }

    /** Endpoint JSON usado por el badge del navbar (AJAX). */
    public function feed(): void {
        requireLogin();
        $userId = (int) $_SESSION['user_id'];
        $this->generate($userId);
        $items = $this->model->getByUser($userId, 8);
        json_response([
            'success' => true,
            'unread'  => $this->model->unreadCount($userId),
            'items'   => array_map(function ($n) {
                return [
                    'id'        => (int) $n['id'],
                    'type'      => $n['type'],
                    'title'     => $n['title'],
                    'body'      => $n['body'],
                    'link'      => $n['link'],
                    'icon'      => $n['icon'],
                    'read'      => !empty($n['read_at']),
                    'created'   => $n['created_at'],
                ];
            }, $items),
        ]);
    }

    public function markRead(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);
        $ok     = $this->model->markRead($id, $userId);
        json_response(['success' => $ok]);
    }

    public function markAllRead(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $n      = $this->model->markAllRead($userId);
        json_response(['success' => true, 'count' => $n]);
    }

    public function destroy(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $ok     = $this->model->delete((int) ($_POST['id'] ?? 0), $userId);
        json_response(['success' => $ok]);
    }

    /**
     * Genera notificaciones derivadas del estado del usuario.
     * Idempotente por día: usa createOncePerDay para evitar duplicados.
     */
    public function generate(int $userId): void {
        // 1) Repasos de flashcards pendientes hoy
        $due = (new Flashcard())->countDue($userId);
        if ($due > 0) {
            $this->model->createOncePerDay(
                $userId,
                'reviews_due',
                "Tienes {$due} tarjeta(s) por repasar hoy",
                'La repetición espaciada funciona mejor si repasas al día.',
                BASE_URL . '?page=flashcards&action=study',
                'fa-clone'
            );
        }

        // 2) Repasos de problemas CP pendientes hoy
        $dueCp = (new CpProblem())->countDueReview($userId);
        if ($dueCp > 0) {
            $this->model->createOncePerDay(
                $userId,
                'cp_reviews_due',
                "Tienes {$dueCp} problema(s) de CP por repasar",
                'Reafirma lo aprendido antes de que se olvide.',
                BASE_URL . '?page=cp&action=review',
                'fa-trophy'
            );
        }

        // 3) Metas atrasadas
        $goals = (new Goal())->listWithProgress($userId, date('Y-m-d'));
        foreach ($goals as $g) {
            if (!empty($g['pace']['has_target']) && !$g['pace']['on_track'] && (int) $g['pct'] < 100) {
                $this->model->createOncePerDay(
                    $userId,
                    'goal_behind_' . (int) $g['id'],
                    'Vas atrasado en: ' . $g['title'],
                    'Avance ' . (int) $g['pct'] . '% — esperado ' . (int) ($g['pace']['expected_pct'] ?? 0) . '%.',
                    BASE_URL . '?page=goals&action=show&id=' . (int) $g['id'],
                    'fa-bullseye'
                );
            }
        }

        // 4) Meta diaria de minutos sin cubrir (sólo si el usuario la configuró)
        $daily = (new User())->getDailyGoal($userId);
        if ($daily > 0) {
            $today = (new StudySession())->todayMinutes($userId);
            if ($today < $daily) {
                $faltan = $daily - $today;
                $this->model->createOncePerDay(
                    $userId,
                    'daily_goal',
                    "Te faltan {$faltan} min para tu meta diaria",
                    "Hoy llevas {$today} de {$daily} min.",
                    BASE_URL . '?page=timer',
                    'fa-clock'
                );
            }
        }

        // 5) Racha en riesgo (si llevas racha y aún no estudias hoy)
        $ss     = new StudySession();
        $streak = $ss->streak($userId);
        if ($streak > 0 && $ss->todayMinutes($userId) === 0) {
            $this->model->createOncePerDay(
                $userId,
                'streak_at_risk',
                "Tu racha de {$streak} día(s) está en riesgo",
                'Estudia al menos 1 minuto hoy para mantenerla.',
                BASE_URL . '?page=timer',
                'fa-fire'
            );
        }
    }
}
