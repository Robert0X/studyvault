<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Tokens de recuperación de contraseña.
 * Tokens criptográficamente aleatorios (32 bytes hex) con expiración corta (1h).
 */
class PasswordReset {
    private PDO $db;
    public const TTL_SECONDS = 3600; // 1 hora

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Genera y guarda un token nuevo. Invalida los anteriores del usuario. */
    public function create(int $userId): string {
        // Marca como usados los tokens anteriores no usados (uno activo por usuario).
        $this->db->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0")
                 ->execute([$userId]);
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + self::TTL_SECONDS);
        $stmt = $this->db->prepare(
            "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)"
        );
        $stmt->execute([$userId, $token, $expires]);
        return $token;
    }

    /** Devuelve el registro si el token es válido (no usado y no expirado). */
    public function findValid(string $token): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM password_resets
             WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1"
        );
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function markUsed(int $id): bool {
        $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
