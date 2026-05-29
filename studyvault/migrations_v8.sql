-- ============================================================
-- StudyVault — Migración v8
-- Habilita: estado activo/inactivo de usuarios (panel admin),
-- recuperación de contraseña con token, y centro de notificaciones.
-- Ejecutar UNA VEZ después de migrations_v7.sql.
-- ============================================================
USE studyvault;

-- 1) Estado activo/inactivo (para el mini-panel admin)
ALTER TABLE users
    ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1 AFTER role;

-- 2) Recuperación de contraseña con token
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token CHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_pr_token (token)
);

-- 3) Centro de notificaciones in-app
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(160) NOT NULL,
    body TEXT,
    link VARCHAR(300) NULL,
    icon VARCHAR(50) DEFAULT 'fa-bell',
    read_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_notif_user (user_id, read_at, created_at)
);
