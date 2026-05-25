<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

csrf_verify();

$userId = (int) $_SESSION['user_id'];
$id     = (int) ($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

$model = new Resource();
$ok    = $model->changeStatus($id, $status, $userId);

echo json_encode(['success' => $ok, 'message' => $ok ? 'Estado actualizado.' : 'Error al actualizar.']);
