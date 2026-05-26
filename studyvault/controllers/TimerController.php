<?php
require_once __DIR__ . '/../models/StudySession.php';
require_once __DIR__ . '/../models/Resource.php';
require_once __DIR__ . '/../models/User.php';

class TimerController {
    private StudySession $model;

    public function __construct() {
        $this->model = new StudySession();
    }

    public function index(): void {
        requireLogin();
        $userId    = (int) $_SESSION['user_id'];
        $resModel  = new Resource();
        $resources = $resModel->getByUser($userId);
        $today     = $this->model->todayMinutes($userId);
        $week      = $this->model->rangeMinutes($userId, 7);
        $streak    = $this->model->streak($userId);
        $byDay     = $this->model->minutesByDay($userId, 84);
        $recent    = $this->model->recent($userId, 6);
        $dailyGoal = (new User())->getDailyGoal($userId);
        require __DIR__ . '/../views/timer/index.php';
    }

    public function setDailyGoal(): void {
        requireLogin();
        csrf_verify();
        $ok = (new User())->setDailyGoal((int) $_SESSION['user_id'], (int) ($_POST['minutes'] ?? 0));
        json_response(['success' => $ok, 'message' => $ok ? 'Meta diaria guardada.' : 'Error.']);
    }

    public function log(): void {
        requireLogin();
        csrf_verify();
        $userId  = (int) $_SESSION['user_id'];
        $minutes = (int) ($_POST['minutes'] ?? 0);
        if ($minutes < 1) {
            json_response(['success' => false, 'message' => 'Minutos inválidos.']);
        }
        $resourceId = ($v = (int) ($_POST['resource_id'] ?? 0)) > 0 ? $v : null;
        $id = $this->model->create($userId, $resourceId, null, $minutes, $_POST['technique'] ?? 'pomodoro');
        log_activity('session.log', 'study_session', $id);
        json_response([
            'success'      => true,
            'message'      => "Sesión registrada (+{$minutes} min).",
            'todayMinutes' => $this->model->todayMinutes($userId),
            'streak'       => $this->model->streak($userId),
        ]);
    }
}
