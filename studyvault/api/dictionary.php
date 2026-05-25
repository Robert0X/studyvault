<?php
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$word = trim($_GET['word'] ?? '');
if ($word === '' || !preg_match('/^[a-zA-Z\s\-]+$/', $word)) {
    echo json_encode(['success' => false, 'message' => 'Ingresa una palabra válida en inglés.']);
    exit;
}

$wordKey = strtolower($word);
$db = Database::getInstance()->getConnection();

// 1. Caché local (válida 30 días) — evita re-llamar a la API
$stmt = $db->prepare(
    "SELECT payload FROM dictionary_cache WHERE word = ? AND fetched_at > (NOW() - INTERVAL 30 DAY)"
);
$stmt->execute([$wordKey]);
$cached = $stmt->fetchColumn();
if ($cached !== false) {
    echo $cached;
    exit;
}

// 2. Consultar la API externa (Free Dictionary API)
$apiUrl  = 'https://api.dictionaryapi.dev/api/v2/entries/en/' . urlencode($wordKey);
$context = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'StudyVault/1.0']]);
$response = @file_get_contents($apiUrl, false, $context);

if ($response === false) {
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar al diccionario. Verifica tu conexión.']);
    exit;
}

$data = json_decode($response, true);

if (isset($data['title']) && $data['title'] === 'No Definitions Found') {
    echo json_encode(['success' => false, 'message' => 'Palabra no encontrada en el diccionario.']);
    exit;
}
if (!is_array($data) || empty($data)) {
    echo json_encode(['success' => false, 'message' => 'Respuesta inválida del diccionario.']);
    exit;
}

$entry      = $data[0];
$word_clean = $entry['word'] ?? $word;
$phonetic   = $entry['phonetic'] ?? '';
$audioUrl   = '';
foreach ($entry['phonetics'] ?? [] as $ph) {
    if (!empty($ph['audio'])) {
        $audioUrl = $ph['audio'];
        if ($phonetic === '' && !empty($ph['text'])) {
            $phonetic = $ph['text'];
        }
        break;
    }
}

$meanings = [];
foreach (array_slice($entry['meanings'] ?? [], 0, 3) as $meaning) {
    $defs = [];
    foreach (array_slice($meaning['definitions'] ?? [], 0, 2) as $def) {
        $defs[] = [
            'definition' => $def['definition'] ?? '',
            'example'    => $def['example'] ?? '',
            'synonyms'   => array_slice($def['synonyms'] ?? [], 0, 4),
        ];
    }
    $meanings[] = ['partOfSpeech' => $meaning['partOfSpeech'] ?? '', 'definitions' => $defs];
}

$payload = json_encode([
    'success'  => true,
    'word'     => $word_clean,
    'phonetic' => $phonetic,
    'audio'    => $audioUrl,
    'meanings' => $meanings,
]);

// 3. Guardar en caché
try {
    $db->prepare("REPLACE INTO dictionary_cache (word, payload) VALUES (?, ?)")->execute([$wordKey, $payload]);
} catch (Throwable $e) {
    error_log('[dictionary_cache] ' . $e->getMessage());
}

echo $payload;
