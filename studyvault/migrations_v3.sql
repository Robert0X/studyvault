-- ============================================================
-- StudyVault — Migración v3 (Bloque 4: integración Codeforces)
-- Ejecutar UNA VEZ después de migrations_v2.sql.
-- ============================================================
USE studyvault;

ALTER TABLE users ADD COLUMN cf_handle VARCHAR(64) NULL;
ALTER TABLE users ADD COLUMN cf_rating INT NULL;
ALTER TABLE users ADD COLUMN cf_synced_at TIMESTAMP NULL DEFAULT NULL;
