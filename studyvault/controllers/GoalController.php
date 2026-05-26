<?php
require_once __DIR__ . '/../models/Goal.php';

class GoalController {
    private Goal $model;

    public function __construct() {
        $this->model = new Goal();
    }

    private function today(): string {
        return date('Y-m-d');
    }

    public function index(): void {
        requireLogin();
        $userId = (int) $_SESSION['user_id'];
        $goals  = $this->model->listWithProgress($userId, $this->today());
        require __DIR__ . '/../views/goals/index.php';
    }

    public function show(): void {
        requireLogin();
        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_GET['id'] ?? 0);
        $goal   = $this->model->findById($id, $userId);
        if (!$goal) {
            header('Location: ' . BASE_URL . '?page=goals');
            exit;
        }
        $resources = $this->model->resourcesWithProgress($id);
        $available = $this->model->availableResources($userId, $id);
        $pct       = Goal::weightedProgress($resources);
        $pace      = Goal::pace($goal['created_at'] ?? null, $goal['target_date'] ?? null, $pct, $this->today());
        require __DIR__ . '/../views/goals/show.php';
    }

    public function store(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $title  = trim($_POST['title'] ?? '');
        if ($title === '') {
            json_response(['success' => false, 'message' => 'El título es obligatorio.']);
        }
        $id = $this->model->create($userId, $title, trim($_POST['description'] ?? ''), trim($_POST['target_date'] ?? '') ?: null);
        log_activity('goal.create', 'goal', $id);
        json_response(['success' => true, 'id' => $id, 'message' => 'Meta creada.']);
    }

    public function update(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);
        if (!$this->model->findById($id, $userId)) {
            json_response(['success' => false, 'message' => 'Meta no válida.']);
        }
        $ok = $this->model->update($id, $userId, trim($_POST['title'] ?? ''), trim($_POST['description'] ?? ''), trim($_POST['target_date'] ?? '') ?: null, $_POST['status'] ?? 'active');
        json_response(['success' => $ok, 'message' => $ok ? 'Meta actualizada.' : 'Error.']);
    }

    public function destroy(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $ok = $this->model->delete((int) ($_POST['id'] ?? 0), $userId);
        json_response(['success' => $ok, 'message' => $ok ? 'Meta eliminada.' : 'Error.']);
    }

    public function attach(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $goalId = (int) ($_POST['goal_id'] ?? 0);
        if (!$this->model->findById($goalId, $userId)) {
            json_response(['success' => false, 'message' => 'Meta no válida.']);
        }
        $resourceId = (int) ($_POST['resource_id'] ?? 0);
        // verificar que el recurso es del usuario
        $own = $this->model->availableResources($userId, $goalId);
        if (!in_array($resourceId, array_map(fn($r) => (int) $r['id'], $own), true)) {
            json_response(['success' => false, 'message' => 'Recurso no válido o ya agregado.']);
        }
        $ok = $this->model->attachResource($goalId, $resourceId, (int) ($_POST['weight'] ?? 1));
        json_response(['success' => $ok, 'message' => $ok ? 'Recurso agregado.' : 'Error.']);
    }

    public function detach(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $goalId = (int) ($_POST['goal_id'] ?? 0);
        if (!$this->model->findById($goalId, $userId)) {
            json_response(['success' => false, 'message' => 'Meta no válida.']);
        }
        $ok = $this->model->detachResource($goalId, (int) ($_POST['resource_id'] ?? 0));
        json_response(['success' => $ok, 'message' => $ok ? 'Recurso quitado.' : 'Error.']);
    }

    public function togglePublic(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);
        if (!$this->model->findById($id, $userId)) {
            json_response(['success' => false, 'message' => 'Meta no válida.']);
        }
        $public = ($_POST['public'] ?? '0') === '1';
        $ok = $this->model->setPublic($id, $userId, $public);
        json_response(['success' => $ok, 'message' => $ok ? ($public ? 'Publicada como plantilla.' : 'Ahora es privada.') : 'Error.']);
    }

    public function browse(): void {
        requireLogin();
        $userId    = (int) $_SESSION['user_id'];
        $templates = $this->model->getPublicTemplates($userId);
        require __DIR__ . '/../views/goals/browse.php';
    }

    public function cloneGoal(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $newId  = $this->model->cloneForUser((int) ($_POST['id'] ?? 0), $userId);
        if ($newId === false) {
            json_response(['success' => false, 'message' => 'No se pudo clonar la plantilla.']);
        }
        log_activity('goal.clone', 'goal', $newId);
        json_response(['success' => true, 'id' => $newId, 'message' => 'Plantilla clonada en tus metas.']);
    }
}
