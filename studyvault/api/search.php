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

if ($type && !in_array($type, $allowedTypes))       $type = null;
if ($status && !in_array($status, $allowedStatuses)) $status = null;

$model     = new Resource();
$resources = $model->getByUser($userId, $subjectId, $type, $status, $q);

$typeIcons = [
    'link'  => 'fa-link',
    'pdf'   => 'fa-file-pdf',
    'note'  => 'fa-sticky-note',
    'video' => 'fa-video',
];
$statusLabels = [
    'pending'     => ['label' => 'Pendiente',    'class' => 'warning'],
    'in_progress' => ['label' => 'En progreso',  'class' => 'info'],
    'completed'   => ['label' => 'Completado',   'class' => 'success'],
];

$result = array_map(function($r) use ($typeIcons, $statusLabels) {
    return [
        'id'           => $r['id'],
        'title'        => htmlspecialchars($r['title']),
        'description'  => htmlspecialchars($r['description'] ?? ''),
        'url'          => htmlspecialchars($r['url'] ?? ''),
        'type'         => $r['type'],
        'type_icon'    => $typeIcons[$r['type']] ?? 'fa-file',
        'file_path'    => $r['file_path'] ? htmlspecialchars($r['file_path']) : null,
        'subject_name' => htmlspecialchars($r['subject_name']),
        'subject_color'=> htmlspecialchars($r['subject_color']),
        'subject_icon' => htmlspecialchars($r['subject_icon']),
        'status'       => $r['status'],
        'status_label' => $statusLabels[$r['status']]['label'],
        'status_class' => $statusLabels[$r['status']]['class'],
        'created_at'   => $r['created_at'],
    ];
}, $resources);

echo json_encode(['success' => true, 'data' => $result, 'count' => count($result)]);
