<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Limita intentos de inicio de sesión para frenar ataques de fuerza bruta.
 *
 * Persistente en BD (no en sesión): un atacante controla su propia cookie/sesión,
 * así que un límite por sesión sería inútil. Se cuenta por IP y por email dentro de
 * una ventana de decaimiento (estilo "lockout" de Microsoft Identity / RateLimiter
 * de Laravel). Escala entre múltiples servidores al vivir en la base de datos.
 */
class LoginThrottle {
    private PDO $db;
    private int $maxAttempts;
    private int $decaySeconds;

    public function __construct(int $maxAttempts = 5, int $decayMinutes = 15) {
        $this->db = Database::getInstance()->getConnection();
        $this->maxAttempts  = max(1, $maxAttempts);
        $this->decaySeconds = max(1, $decayMinutes) * 60;
    }

    /** Nº de fallos recientes para esta IP o email dentro de la ventana. */
    public function recentFailures(string $ip, string $email): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE success = 0 AND (ip = ? OR email = ?)
               AND attempted_at > (NOW() - INTERVAL " . $this->decaySeconds . " SECOND)"
        );
        $stmt->execute([$ip, $email]);
        return (int) $stmt->fetchColumn();
    }

    public function tooMany(string $ip, string $email): bool {
        return $this->recentFailures($ip, $email) >= $this->maxAttempts;
    }

    /**
     * Segundos hasta que el bloqueo expire (cuando el fallo más antiguo de la ventana caduca).
     * Se calcula en SQL para evitar desfases de zona horaria entre PHP y MySQL.
     */
    public function secondsUntilRetry(string $ip, string $email): int {
        $stmt = $this->db->prepare(
            "SELECT " . $this->decaySeconds . " - TIMESTAMPDIFF(SECOND, MIN(attempted_at), NOW())
             FROM login_attempts
             WHERE success = 0 AND (ip = ? OR email = ?)
               AND attempted_at > (NOW() - INTERVAL " . $this->decaySeconds . " SECOND)"
        );
        $stmt->execute([$ip, $email]);
        $remaining = $stmt->fetchColumn();
        return ($remaining === null || $remaining === false) ? 0 : max(0, (int) $remaining);
    }

    public function record(string $ip, string $email, bool $success): void {
        $stmt = $this->db->prepare(
            "INSERT INTO login_attempts (ip, email, success) VALUES (?, ?, ?)"
        );
        $stmt->execute([mb_substr($ip, 0, 45), mb_substr($email, 0, 150), $success ? 1 : 0]);
        // Poda para crecimiento acotado (índice por attempted_at lo hace barato).
        $this->db->exec("DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)");
    }

    /** Limpia los fallos del email tras un login exitoso (no deja al usuario bloqueado). */
    public function clearFailures(string $email): void {
        $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE success = 0 AND email = ?");
        $stmt->execute([$email]);
    }
}
