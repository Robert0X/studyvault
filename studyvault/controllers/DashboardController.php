<?php
require_once __DIR__ . '/../models/Resource.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/Flashcard.php';
require_once __DIR__ . '/../models/Goal.php';
require_once __DIR__ . '/../models/StudySession.php';

class DashboardController {
    public function index(): void {
        requireLogin();
        $userId  = (int) $_SESSION['user_id'];
        $isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

        $resourceModel = new Resource();
        $subjectModel  = new Subject();
        $sessionModel  = new StudySession();

        $stats        = $resourceModel->getStats($userId);
        $recent       = $resourceModel->getRecent($userId, 6);
        $subjectStats = $subjectModel->getWithStats($userId, $isAdmin);
        $dueCards     = (new Flashcard())->countDue($userId);
        $goals        = (new Goal())->listWithProgress($userId, date('Y-m-d'));
        $timeToday    = $sessionModel->todayMinutes($userId);
        $timeWeek     = $sessionModel->rangeMinutes($userId, 7);
        $streak       = $sessionModel->streak($userId);

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
