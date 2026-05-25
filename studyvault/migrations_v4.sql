-- ============================================================
-- StudyVault — Migración v4 (Bloque 4 completo: repaso, plantillas, sugerencias)
-- Ejecutar UNA VEZ después de migrations_v3.sql.
-- ============================================================
USE studyvault;

-- Campos SM-2 para repaso espaciado de problemas (reusa el mismo algoritmo que flashcards)
ALTER TABLE cp_problems ADD COLUMN ease_factor FLOAT DEFAULT 2.5;
ALTER TABLE cp_problems ADD COLUMN interval_days INT DEFAULT 0;
ALTER TABLE cp_problems ADD COLUMN repetitions INT DEFAULT 0;

-- Biblioteca de plantillas / snippets de código
CREATE TABLE IF NOT EXISTS cp_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    language VARCHAR(30) DEFAULT 'cpp',
    code MEDIUMTEXT NOT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Caché del catálogo de problemas de Codeforces (para sugerir-siguiente sin re-descargar)
CREATE TABLE IF NOT EXISTS cf_problemset_cache (
    contest_id INT NOT NULL,
    idx VARCHAR(10) NOT NULL,
    name VARCHAR(255),
    rating INT,
    tags VARCHAR(500),
    fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (contest_id, idx)
);
CREATE INDEX idx_problemset_rating ON cf_problemset_cache (rating);
