<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$word = trim($_GET['word'] ?? '');
if ($word === '' || !preg_match('/^[a-zA-Z\s\-]+$/', $word)) {
    echo json_encode(['success' => false, 'message' => 'Palabra inválida.']);
    exit;
}

$w = urlencode(strtolower($word));
$ctx = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'StudyVault/1.0']]);

// rel_bgb = palabras que suelen ir ANTES; rel_bga = palabras que suelen ir DESPUÉS
$words = [];
foreach (["rel_bgb=$w", "rel_bga=$w"] as $rel) {
    $resp = @file_get_contents("https://api.datamuse.com/words?{$rel}&max=6", false, $ctx);
    if ($resp !== false) {
        foreach (json_decode($resp, true) ?: [] as $it) {
            if (!empty($it['word'])) {
                $words[] = $it['word'];
            }
        }
    }
}

echo json_encode(['success' => true, 'word' => $word, 'collocations' => array_values(array_unique(array_slice($words, 0, 12)))]);
