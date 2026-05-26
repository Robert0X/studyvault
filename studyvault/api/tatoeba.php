<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}
if (!rate_limit('tatoeba', 20, 60)) {
    echo json_encode(['success' => false, 'message' => 'Demasiadas solicitudes, espera un momento.']);
    exit;
}

$word = trim($_GET['word'] ?? '');
if ($word === '' || !preg_match('/^[a-zA-Z\s\-]+$/', $word)) {
    echo json_encode(['success' => false, 'message' => 'Palabra inválida.']);
    exit;
}

$w = urlencode($word);
$ctx = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'StudyVault/1.0']]);
$resp = @file_get_contents("https://tatoeba.org/en/api_v0/search?from=eng&query={$w}&sort=relevance", false, $ctx);

$sentences = [];
if ($resp !== false) {
    $j = json_decode($resp, true);
    foreach (($j['results'] ?? []) as $r) {
        $t = $r['text'] ?? '';
        if ($t !== '' && mb_strlen($t) < 120) {
            $sentences[] = $t;
        }
        if (count($sentences) >= 3) {
            break;
        }
    }
}

echo json_encode(['success' => true, 'word' => $word, 'sentences' => $sentences]);
