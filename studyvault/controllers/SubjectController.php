<?php
require_once __DIR__ . '/../models/Subject.php';

class SubjectController {
    private Subject $model;

    public function __construct() {
        $this->model = new Subject();
    }

    public function index(): void {
        requireLogin();
        $userId   = (int) $_SESSION['user_id'];
        $isAdmin  = $_SESSION['user_role'] === 'admin';
        $subjects = $this->model->getWithStats($userId, $isAdmin);
        require __DIR__ . '/../views/subjects/index.php';
    }

    public function store(): void {
        requireLogin();
        header('Content-Type: application/json');

        $userId = (int) $_SESSION['user_id'];
        $name   = trim($_POST['name'] ?? '');
        $desc   = trim($_POST['description'] ?? '');
        $icon   = trim($_POST['icon'] ?? 'fa-book');
        $color  = trim($_POST['color'] ?? '#4361ee');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
            exit;
        }

        $id = $this->model->create($name, $desc, $icon, $color, $userId);
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Materia creada.']);
        exit;
    }

    public function update(): void {
        requireLogin();
        header('Content-Type: application/json');

        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $desc   = trim($_POST['description'] ?? '');
        $icon   = trim($_POST['icon'] ?? 'fa-book');
        $color  = trim($_POST['color'] ?? '#4361ee');

        if (!$id || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            exit;
        }

        $ok = $this->model->update($id, $name, $desc, $icon, $color, $userId);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Materia actualizada.' : 'Error al actualizar.']);
        exit;
    }

    public function destroy(): void {
        requireLogin();
        header('Content-Type: application/json');

        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }

        $ok = $this->model->delete($id, $userId);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Materia eliminada.' : 'No se pudo eliminar.']);
        exit;
    }
}
