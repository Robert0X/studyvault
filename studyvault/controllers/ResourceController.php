<?php
require_once __DIR__ . '/../models/Resource.php';
require_once __DIR__ . '/../models/Subject.php';

class ResourceController {
    private Resource $model;
    private Subject  $subjectModel;

    public function __construct() {
        $this->model        = new Resource();
        $this->subjectModel = new Subject();
    }

    public function index(): void {
        requireLogin();
        $userId    = (int) $_SESSION['user_id'];
        $subjectId = isset($_GET['subject']) ? (int) $_GET['subject'] : null;
        $type      = $_GET['type'] ?? null;
        $status    = $_GET['status'] ?? null;
        $search    = trim($_GET['q'] ?? '');

        $perPage   = 10;
        $pageNum   = max(1, (int) ($_GET['pg'] ?? 1));
        $total     = $this->model->countByUser($userId, $subjectId, $type, $status, $search);
        $pages     = max(1, (int) ceil($total / $perPage));
        $resources = $this->model->getByUser($userId, $subjectId, $type, $status, $search, $perPage, ($pageNum - 1) * $perPage);
        $subjects  = $this->subjectModel->getByUser($userId);
        require __DIR__ . '/../views/resources/index.php';
    }

    public function create(): void {
        requireLogin();
        $userId   = (int) $_SESSION['user_id'];
        $subjects = $this->subjectModel->getByUser($userId);
        require __DIR__ . '/../views/resources/form.php';
    }

    public function store(): void {
        requireLogin();
        $userId = (int) $_SESSION['user_id'];
        $error  = '';

        $data = [
            'title'      => trim($_POST['title'] ?? ''),
            'description'=> trim($_POST['description'] ?? ''),
            'url'        => trim($_POST['url'] ?? '') ?: null,
            'type'       => $_POST['type'] ?? 'link',
            'subject_id' => (int) ($_POST['subject_id'] ?? 0),
            'user_id'    => $userId,
            'status'     => $_POST['status'] ?? 'pending',
            'file_path'  => null,
        ];

        $allowedTypes   = ['link', 'pdf', 'note', 'video'];
        $allowedStatuses = ['pending', 'in_progress', 'completed'];

        if (empty($data['title'])) {
            $error = 'El título es obligatorio.';
        } elseif (!$data['subject_id']) {
            $error = 'Selecciona una materia.';
        } elseif (!in_array($data['type'], $allowedTypes)) {
            $error = 'Tipo inválido.';
        } elseif (!in_array($data['status'], $allowedStatuses)) {
            $error = 'Estado inválido.';
        }

        // Manejo de archivo
        if (!$error && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file     = $_FILES['file'];
            $mimeType = mime_content_type($file['tmp_name']);
            if (!in_array($mimeType, ALLOWED_TYPES)) {
                $error = 'Tipo de archivo no permitido. Solo PDF e imágenes.';
            } elseif ($file['size'] > MAX_FILE_SIZE) {
                $error = 'El archivo supera el límite de ' . MAX_FILE_SIZE_MB . ' MB.';
            } else {
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('file_') . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filename)) {
                    $data['file_path'] = $filename;
                } else {
                    $error = 'Error al subir el archivo.';
                }
            }
        }

        if (!$error) {
            $this->model->create($data);
            header('Location: ' . BASE_URL . '?page=resources&saved=1');
            exit;
        }

        $subjects = $this->subjectModel->getByUser($userId);
        require __DIR__ . '/../views/resources/form.php';
    }

    public function edit(): void {
        requireLogin();
        $userId   = (int) $_SESSION['user_id'];
        $id       = (int) ($_GET['id'] ?? 0);
        $resource = $this->model->findById($id, $userId);
        if (!$resource) {
            header('Location: ' . BASE_URL . '?page=resources');
            exit;
        }
        $subjects = $this->subjectModel->getByUser($userId);
        $editing  = true;
        require __DIR__ . '/../views/resources/form.php';
    }

    public function update(): void {
        requireLogin();
        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);
        $error  = '';

        $resource = $this->model->findById($id, $userId);
        if (!$resource) {
            header('Location: ' . BASE_URL . '?page=resources');
            exit;
        }

        $data = [
            'title'      => trim($_POST['title'] ?? ''),
            'description'=> trim($_POST['description'] ?? ''),
            'url'        => trim($_POST['url'] ?? '') ?: null,
            'type'       => $_POST['type'] ?? 'link',
            'subject_id' => (int) ($_POST['subject_id'] ?? 0),
            'status'     => $_POST['status'] ?? 'pending',
            'file_path'  => $resource['file_path'],
        ];

        if (empty($data['title'])) $error = 'El título es obligatorio.';

        if (!$error && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file     = $_FILES['file'];
            $mimeType = mime_content_type($file['tmp_name']);
            if (!in_array($mimeType, ALLOWED_TYPES)) {
                $error = 'Tipo de archivo no permitido.';
            } elseif ($file['size'] > MAX_FILE_SIZE) {
                $error = 'El archivo supera ' . MAX_FILE_SIZE_MB . ' MB.';
            } else {
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('file_') . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filename)) {
                    if ($resource['file_path'] && file_exists(UPLOAD_PATH . $resource['file_path'])) {
                        unlink(UPLOAD_PATH . $resource['file_path']);
                    }
                    $data['file_path'] = $filename;
                } else {
                    $error = 'Error al subir el archivo.';
                }
            }
        }

        if (!$error) {
            $this->model->update($id, $data, $userId);
            header('Location: ' . BASE_URL . '?page=resources&saved=1');
            exit;
        }

        $subjects = $this->subjectModel->getByUser($userId);
        $editing  = true;
        require __DIR__ . '/../views/resources/form.php';
    }

    public function destroy(): void {
        requireLogin();
        header('Content-Type: application/json');
        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);

        $resource = $this->model->delete($id, $userId);
        if ($resource && $resource['file_path'] && file_exists(UPLOAD_PATH . $resource['file_path'])) {
            unlink(UPLOAD_PATH . $resource['file_path']);
        }

        echo json_encode(['success' => $resource !== false, 'message' => $resource ? 'Recurso eliminado.' : 'Error.']);
        exit;
    }
}
