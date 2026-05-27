-- ============================================================
-- StudyVault — Migración v7 (rendimiento: índices)
-- Ejecutar UNA VEZ después de migrations_v6.sql.
-- ============================================================
USE studyvault;

CREATE INDEX idx_flashcards_level ON flashcards (user_id, cefr_level);
CREATE INDEX idx_cp_status        ON cp_problems (user_id, status);
