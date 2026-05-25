<?php
require_once __DIR__ . '/../models/StudySession.php';
require_once __DIR__ . '/../models/Flashcard.php';
require_once __DIR__ . '/../models/CpProblem.php';
require_once __DIR__ . '/../models/Goal.php';

class ReportController {
    public function index(): void {
        requireLogin();
        $userId = (int) $_SESSION['user_id'];

        $ss   = new StudySession();
        $fc   = new Flashcard();
        $cp   = new CpProblem();
        $goal = new Goal();

        $week     = $ss->rangeMinutes($userId, 7);
        $prevWeek = $ss->rangeMinutes($userId, 14) - $week;
        $streak   = $ss->streak($userId);
        $byDay    = $ss->minutesByDay($userId, 7);
        $reviewed = $fc->reviewedSince($userId, 7);
        $solved   = $cp->solvedSince($userId, 7);
        $goals    = $goal->listWithProgress($userId, date('Y-m-d'));

        require __DIR__ . '/../views/report/index.php';
    }
}
