-- ============================================================
-- StudyVault — Migración v5 (completar B5/B6/B7)
-- Ejecutar UNA VEZ después de migrations_v4.sql.
-- ============================================================
USE studyvault;

-- Modo escucha: audio de la palabra en la tarjeta
ALTER TABLE flashcards ADD COLUMN audio_url VARCHAR(300) NULL;

-- Meta diaria de estudio (minutos)
ALTER TABLE users ADD COLUMN daily_goal_minutes INT DEFAULT 0;

-- Compartir metas como plantillas públicas + clonado
ALTER TABLE goals ADD COLUMN is_public TINYINT(1) DEFAULT 0;
ALTER TABLE goals ADD COLUMN cloned_from INT NULL;
