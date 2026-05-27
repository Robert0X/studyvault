-- ============================================================
-- StudyVault — Migración v6 (seguridad: throttling de login)
-- Ejecutar UNA VEZ después de migrations_v5.sql.
-- ============================================================
USE studyvault;

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    email VARCHAR(150) NOT NULL,
    success TINYINT(1) DEFAULT 0,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_login_ip    ON login_attempts (ip, attempted_at);
CREATE INDEX idx_login_email ON login_attempts (email, attempted_at);
