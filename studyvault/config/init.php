<?php
/**
 * Bootstrap centralizado de StudyVault.
 * Carga .env, configura sesión segura, manejo de errores, autoload,
 * helpers de CSRF y de respuesta JSON. Todo punto de entrada debe
 * incluir este archivo PRIMERO.
 */

// ── 1. Cargar variables de entorno (.env) ──────────────────────────
(function () {
    $envFile = __DIR__ . '/../.env';
    if (!is_file($envFile)) {
        return;
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim(trim($val), "\"'");
        if (getenv($key) === false) {
            putenv("$key=$val");
            $_ENV[$key] = $val;
        }
    }
})();

function env(string $key, mixed $default = null): mixed {
    $val = getenv($key);
    return $val === false ? $default : $val;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// ── 2. Manejo de errores centralizado ──────────────────────────────
$GLOBALS['__APP_DEBUG'] = (env('APP_DEBUG', 'false') === 'true');

$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/error.log');
ini_set('display_errors', $GLOBALS['__APP_DEBUG'] ? '1' : '0');
error_reporting(E_ALL);

set_exception_handler(function (Throwable $e): void {
    error_log('[EXCEPTION] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        || str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');
    if ($wantsJson && !headers_sent()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
        return;
    }
    echo $GLOBALS['__APP_DEBUG']
        ? '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>'
        : '<h1>Ocurrió un error</h1><p>Intenta de nuevo más tarde.</p>';
});

// ── 3. Autoloader para models/ y controllers/ ──────────────────────
spl_autoload_register(function (string $class): void {
    foreach (['models', 'controllers'] as $dir) {
        $path = __DIR__ . '/../' . $dir . '/' . $class . '.php';
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

// ── 4. Sesión con cookies seguras ──────────────────────────────────
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $secure,
    ]);
    session_start();
}

// ── 4b. Cabeceras de seguridad ──────────────────────────────────────
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// ── 5. CSRF ─────────────────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token(): string {
    return $_SESSION['csrf_token'] ?? '';
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/** Valida el token en peticiones POST. Corta la ejecución si es inválido. */
function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Token CSRF inválido. Recarga la página.']);
        exit;
    }
}

// ── 6. Helpers ──────────────────────────────────────────────────────
function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '?page=login');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        header('Location: ' . BASE_URL . '?page=dashboard');
        exit;
    }
}

/** Registra una acción en la bitácora (activity_log). Silencioso si falla. */
function log_activity(string $action, ?string $entity = null, ?int $entityId = null): void {
    if (empty($_SESSION['user_id'])) {
        return;
    }
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "INSERT INTO activity_log (user_id, action, entity, entity_id) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([(int) $_SESSION['user_id'], $action, $entity, $entityId]);
    } catch (Throwable $e) {
        error_log('[activity_log] ' . $e->getMessage());
    }
}
