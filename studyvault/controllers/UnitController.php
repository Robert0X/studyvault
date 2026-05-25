<?php
require_once __DIR__ . '/../models/Unit.php';

class UnitController {
    private Unit $model;

    public function __construct() {
        $this->model = new Unit();
    }

    public function index(): void {
        requireLogin();
        $userId     = (int) $_SESSION['user_id'];
        $resourceId = (int) ($_GET['resource'] ?? 0);
        $resource   = $this->model->resourceOwned($resourceId, $userId);
        if (!$resource) {
            header('Location: ' . BASE_URL . '?page=resources');
            exit;
        }
        $units    = $this->model->getByResource($resourceId);
        $progress = $this->model->progress($resourceId);
        require __DIR__ . '/../views/resources/units.php';
    }

    public function store(): void {
        requireLogin();
        csrf_verify();
        $userId     = (int) $_SESSION['user_id'];
        $resourceId = (int) ($_POST['resource_id'] ?? 0);
        if (!$this->model->resourceOwned($resourceId, $userId)) {
            json_response(['success' => false, 'message' => 'Recurso no válido.']);
        }
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            json_response(['success' => false, 'message' => 'El título es obligatorio.']);
        }
        $pf = ($v = (int) ($_POST['page_from'] ?? 0)) > 0 ? $v : null;
        $pt = ($v = (int) ($_POST['page_to'] ?? 0)) > 0 ? $v : null;
        $id = $this->model->create($resourceId, $title, $pf, $pt);
        log_activity('unit.create', 'unit', $id);
        json_response(['success' => true, 'id' => $id, 'message' => 'Unidad agregada.']);
    }

    public function bulk(): void {
        requireLogin();
        csrf_verify();
        $userId     = (int) $_SESSION['user_id'];
        $resourceId = (int) ($_POST['resource_id'] ?? 0);
        if (!$this->model->resourceOwned($resourceId, $userId)) {
            json_response(['success' => false, 'message' => 'Recurso no válido.']);
        }
        $count  = (int) ($_POST['count'] ?? 0);
        $prefix = trim($_POST['prefix'] ?? 'Capítulo') ?: 'Capítulo';
        if ($count < 1) {
            json_response(['success' => false, 'message' => 'Indica cuántas unidades generar.']);
        }
        $n = $this->model->bulkCreate($resourceId, $count, $prefix);
        json_response(['success' => true, 'message' => "{$n} unidades generadas."]);
    }

    public function setStatus(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);
        if (!$this->model->unitOwned($id, $userId)) {
            json_response(['success' => false, 'message' => 'Unidad no válida.']);
        }
        $ok = $this->model->setStatus($id, $_POST['status'] ?? '');
        json_response(['success' => $ok, 'message' => $ok ? 'Actualizado.' : 'Error.']);
    }

    public function destroy(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);
        $unit   = $this->model->unitOwned($id, $userId);
        if (!$unit) {
            json_response(['success' => false, 'message' => 'Unidad no válida.']);
        }
        $ok = $this->model->delete($id, (int) $unit['resource_id']);
        json_response(['success' => $ok, 'message' => $ok ? 'Unidad eliminada.' : 'Error.']);
    }
}
