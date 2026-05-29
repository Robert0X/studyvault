<?php
require_once __DIR__ . '/../models/User.php';

/**
 * Panel administrativo: gestión de usuarios y uso global.
 * Sólo accesible para usuarios con role='admin' (vía requireAdmin()).
 */
class AdminController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function index(): void {
        requireAdmin();
        $page    = max(1, (int) ($_GET['pg'] ?? 1));
        $q       = trim($_GET['q'] ?? '');
        $perPage = 20;
        $result  = $this->userModel->paginate($page, $perPage, $q);
        $stats   = $this->userModel->globalStats();
        $pages   = (int) max(1, ceil(($result['total'] ?? 0) / $perPage));
        $users   = $result['rows'];
        $total   = $result['total'];
        $pageNum = $page;
        require __DIR__ . '/../views/admin/index.php';
    }

    public function toggleActive(): void {
        requireAdmin();
        csrf_verify();
        $id   = (int) ($_POST['id'] ?? 0);
        $self = (int) ($_SESSION['user_id'] ?? 0);
        if ($id === $self) {
            json_response(['success' => false, 'message' => 'No puedes desactivarte a ti mismo.']);
        }
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'Usuario inválido.']);
        }
        $user = $this->userModel->findById($id);
        if (!$user) {
            json_response(['success' => false, 'message' => 'Usuario no encontrado.']);
        }
        $newState = (int) ($user['active'] ?? 1) === 1 ? false : true;
        $ok = $this->userModel->setActive($id, $newState);
        log_activity('admin.toggle_active', 'user', $id);
        json_response([
            'success' => $ok,
            'active'  => $newState,
            'message' => $newState ? 'Usuario activado.' : 'Usuario desactivado.',
        ]);
    }
}
