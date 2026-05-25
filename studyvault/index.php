<?php
require_once __DIR__ . '/config/init.php';

$page   = $_GET['page'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];

// Validar CSRF en TODA petición POST (centralizado)
if ($method === 'POST') {
    csrf_verify();
}

// Páginas públicas
if (in_array($page, ['login', 'register', 'logout'], true)) {
    $auth = new AuthController();
    match ($page) {
        'login'    => $auth->login(),
        'register' => $auth->register(),
        'logout'   => $auth->logout(),
    };
    exit;
}

// Todas las demás requieren sesión
requireLogin();

$subjects   = new SubjectController();
$resources  = new ResourceController();
$flashcards = new FlashcardController();
$cp         = new CpController();
$units      = new UnitController();
$goals      = new GoalController();
$timer      = new TimerController();

$action = $_GET['action'] ?? null;
$post   = $_POST['_action'] ?? null;

match (true) {
    $page === 'dashboard' => (function () {
        $resourceModel = new Resource();
        $subjectModel  = new Subject();
        $cardModel     = new Flashcard();
        $userId        = (int) $_SESSION['user_id'];
        $isAdmin       = $_SESSION['user_role'] === 'admin';
        $stats         = $resourceModel->getStats($userId);
        $recent        = $resourceModel->getRecent($userId, 6);
        $subjectStats  = $subjectModel->getWithStats($userId, $isAdmin);
        $dueCards      = $cardModel->countDue($userId);
        $goalModel     = new Goal();
        $sessionModel  = new StudySession();
        $goals         = $goalModel->listWithProgress($userId, date('Y-m-d'));
        $timeToday     = $sessionModel->todayMinutes($userId);
        $timeWeek      = $sessionModel->rangeMinutes($userId, 7);
        $streak        = $sessionModel->streak($userId);
        require __DIR__ . '/views/dashboard/index.php';
    })(),

    $page === 'subjects' && $method === 'GET'                       => $subjects->index(),
    $page === 'subjects' && $method === 'POST' && $post === 'store'   => $subjects->store(),
    $page === 'subjects' && $method === 'POST' && $post === 'update'  => $subjects->update(),
    $page === 'subjects' && $method === 'POST' && $post === 'destroy' => $subjects->destroy(),

    $page === 'resources' && $method === 'GET' && $action === null      => $resources->index(),
    $page === 'resources' && $method === 'GET' && $action === 'create'  => $resources->create(),
    $page === 'resources' && $method === 'GET' && $action === 'edit'    => $resources->edit(),
    $page === 'resources' && $method === 'POST' && $post === 'store'   => $resources->store(),
    $page === 'resources' && $method === 'POST' && $post === 'update'  => $resources->update(),
    $page === 'resources' && $method === 'POST' && $post === 'destroy' => $resources->destroy(),

    $page === 'flashcards' && $method === 'GET' && $action === 'study' => $flashcards->study(),
    $page === 'flashcards' && $method === 'GET'                        => $flashcards->index(),
    $page === 'flashcards' && $method === 'POST' && $post === 'store'      => $flashcards->store(),
    $page === 'flashcards' && $method === 'POST' && $post === 'store_dict' => $flashcards->storeFromDictionary(),
    $page === 'flashcards' && $method === 'POST' && $post === 'review'     => $flashcards->review(),
    $page === 'flashcards' && $method === 'POST' && $post === 'destroy'    => $flashcards->destroy(),

    $page === 'cp' && $method === 'GET'                            => $cp->index(),
    $page === 'cp' && $method === 'POST' && $post === 'save_handle'  => $cp->saveHandle(),
    $page === 'cp' && $method === 'POST' && $post === 'sync'         => $cp->sync(),
    $page === 'cp' && $method === 'POST' && $post === 'store'        => $cp->store(),
    $page === 'cp' && $method === 'POST' && $post === 'status'       => $cp->setStatus(),
    $page === 'cp' && $method === 'POST' && $post === 'destroy'      => $cp->destroy(),

    $page === 'units' && $method === 'GET'                       => $units->index(),
    $page === 'units' && $method === 'POST' && $post === 'store'    => $units->store(),
    $page === 'units' && $method === 'POST' && $post === 'bulk'     => $units->bulk(),
    $page === 'units' && $method === 'POST' && $post === 'status'   => $units->setStatus(),
    $page === 'units' && $method === 'POST' && $post === 'destroy'  => $units->destroy(),

    $page === 'goals' && $method === 'GET' && $action === 'show' => $goals->show(),
    $page === 'goals' && $method === 'GET'                       => $goals->index(),
    $page === 'goals' && $method === 'POST' && $post === 'store'    => $goals->store(),
    $page === 'goals' && $method === 'POST' && $post === 'update'   => $goals->update(),
    $page === 'goals' && $method === 'POST' && $post === 'destroy'  => $goals->destroy(),
    $page === 'goals' && $method === 'POST' && $post === 'attach'   => $goals->attach(),
    $page === 'goals' && $method === 'POST' && $post === 'detach'   => $goals->detach(),

    $page === 'timer' && $method === 'GET'                      => $timer->index(),
    $page === 'timer' && $method === 'POST' && $post === 'log'     => $timer->log(),

    default => (function () {
        http_response_code(404);
        echo '<h1>404 - Página no encontrada</h1>';
    })(),
};
