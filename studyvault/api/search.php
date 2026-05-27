<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$userId    = (int) $_SESSION['user_id'];
$q         = trim($_GET['q'] ?? '');
$subjectId = isset($_GET['subject_id']) && $_GET['subject_id'] !== '' ? (int) $_GET['subject_id'] : null;
$type      = $_GET['type'] ?? null;
$status    = $_GET['status'] ?? null;

$allowedTypes    = ['link', 'pdf', 'note', 'video'];
$allowedStatuses = ['pending', 'in_progress', 'completed'];
if ($type && !in_array($type, $allowedTypes, true))       $type = null;
if ($status && !in_array($status, $allowedStatuses, true)) $status = null;

$model     = new Resource();
$resources = $model->getByUser($userId, $subjectId, $type, $status, $q, 200, 0);

// Renderiza las filas con la MISMA plantilla que el listado inicial (una sola fuente de verdad).
$typeIcons   = ['link' => 'fa-link', 'pdf' => 'fa-file-pdf', 'note' => 'fa-sticky-note', 'video' => 'fa-video'];
$typeLabels  = ['link' => 'Enlace', 'pdf' => 'PDF/Archivo', 'note' => 'Nota', 'video' => 'Video'];
$statusBadge = [
    'pending'     => ['label' => 'Pendiente',   'class' => 'warning'],
    'in_progress' => ['label' => 'En progreso', 'class' => 'info'],
    'completed'   => ['label' => 'Completado',  'class' => 'success'],
];
ob_start();
foreach ($resources as $r) {
    include __DIR__ . '/../views/resources/row.php';
}
$html = ob_get_clean();

echo json_encode(['success' => true, 'html' => $html, 'count' => count($resources)]);
