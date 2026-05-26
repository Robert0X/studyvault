-- ============================================================
-- StudyVault - INSTALACION EN UN SOLO ARCHIVO
-- Importar en phpMyAdmin (pestana Importar).
-- AVISO: recrea la base "studyvault" desde cero (borra la existente).
-- ============================================================
DROP DATABASE IF EXISTS studyvault;

-- StudyVault - Base de datos
-- Ejecutar en phpMyAdmin o MySQL CLI

CREATE DATABASE IF NOT EXISTS studyvault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studyvault;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'fa-book',
    color VARCHAR(20) DEFAULT '#4361ee',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    url VARCHAR(500),
    type ENUM('link','pdf','note','video') DEFAULT 'link',
    file_path VARCHAR(300),
    subject_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending','in_progress','completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Usuarios de prueba (contraseña: admin123 y user123)
INSERT INTO users (name, email, password, role) VALUES
('Administrador', 'admin@studyvault.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Estudiante Demo', 'student@studyvault.com', '$2y$10$TKh8H1.PyfuviSpW4zsIs.CB5BuNkCQi2Qy/XkP0OKB.C8t.QCWK.', 'student');

-- Materias de prueba para el admin
INSERT INTO subjects (name, description, icon, color, user_id) VALUES
('Inglés', 'Recursos para aprender inglés: gramática, vocabulario, listening', 'fa-language', '#4361ee', 1),
('Programación Competitiva', 'Algoritmos, estructuras de datos, problemas de CP', 'fa-code', '#f72585', 1),
('Programación Web', 'HTML, CSS, JavaScript, PHP, frameworks', 'fa-globe', '#4cc9f0', 1),
('Matemáticas', 'Cálculo, álgebra lineal, estadística', 'fa-calculator', '#7209b7', 1);

-- Recursos de prueba
INSERT INTO resources (title, description, url, type, subject_id, user_id, status) VALUES
('Grammar in Use - Raymond Murphy', 'El libro más recomendado para gramática inglesa', 'https://www.cambridge.org/grammar-in-use', 'link', 1, 1, 'in_progress'),
('Anki - Flashcards', 'App para memorizar vocabulario con repetición espaciada', 'https://apps.ankiweb.net/', 'link', 1, 1, 'completed'),
('CP-Algorithms', 'Referencia completa de algoritmos para programación competitiva', 'https://cp-algorithms.com/', 'link', 2, 1, 'in_progress'),
('Codeforces', 'Plataforma de concursos de programación', 'https://codeforces.com/', 'link', 2, 1, 'pending'),
('MDN Web Docs', 'Documentación de HTML, CSS y JavaScript', 'https://developer.mozilla.org/', 'link', 3, 1, 'completed');


-- ============================================================
-- StudyVault — Migración v2 (Bloques 1 y 2 del PLAN_IMPLEMENTACION)
-- Ejecutar UNA VEZ después de studyvault.sql.
-- Añade: progreso granular, metas, unidades, sesiones,
-- flashcards + SM-2, banco CEFR, problemas CP, bitácora y caché.
-- ============================================================
USE studyvault;

-- ── Soft delete + progreso granular en tablas existentes ──────────
ALTER TABLE subjects  ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE resources ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE resources ADD COLUMN total_units INT DEFAULT 0;
ALTER TABLE resources ADD COLUMN unit_type ENUM('chapter','page','lesson','problem','custom') DEFAULT 'custom';
ALTER TABLE resources ADD COLUMN is_habit TINYINT(1) DEFAULT 0;
ALTER TABLE resources ADD COLUMN estimated_minutes INT NULL;

-- ── Metas / planes de estudio (Bloque 3) ──────────────────────────
CREATE TABLE IF NOT EXISTS goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    target_date DATE NULL,
    status ENUM('active','paused','done') DEFAULT 'active',
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS goal_resources (
    goal_id INT NOT NULL,
    resource_id INT NOT NULL,
    weight INT DEFAULT 1,
    PRIMARY KEY (goal_id, resource_id),
    FOREIGN KEY (goal_id) REFERENCES goals(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
);

-- ── Unidades dentro de un recurso (Bloque 3 — "libro enorme") ──────
CREATE TABLE IF NOT EXISTS units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    order_index INT DEFAULT 0,
    status ENUM('pending','in_progress','completed') DEFAULT 'pending',
    page_from INT NULL,
    page_to INT NULL,
    minutes_spent INT DEFAULT 0,
    note TEXT,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
);

-- ── Sesiones de estudio / tiempo real (Bloque 3) ───────────────────
CREATE TABLE IF NOT EXISTS study_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_id INT NULL,
    unit_id INT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL DEFAULT NULL,
    minutes INT DEFAULT 0,
    technique ENUM('read','practice','review','pomodoro') DEFAULT 'read',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Flashcards genéricas (EN/CP) con campos SM-2 (Bloque 2) ────────
CREATE TABLE IF NOT EXISTS flashcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    deck VARCHAR(50) DEFAULT 'vocab',          -- 'vocab' | 'cp' | personalizado
    front TEXT NOT NULL,                        -- palabra / pregunta
    back TEXT NOT NULL,                         -- significado / respuesta
    example TEXT,                               -- frase de ejemplo (uso)
    extra TEXT,                                 -- colocaciones, fonética, etc.
    source VARCHAR(100) DEFAULT 'manual',       -- 'manual' | 'dictionary' | 'cp'
    cefr_level VARCHAR(5) NULL,                 -- A1..C2 (para Bloque 5)
    -- SM-2
    ease_factor FLOAT DEFAULT 2.5,
    interval_days INT DEFAULT 0,
    repetitions INT DEFAULT 0,
    due_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('new','learning','mature','mastered') DEFAULT 'new',
    last_reviewed_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Banco CEFR + enriquecimiento (Bloque 5 — vacías por ahora) ─────
CREATE TABLE IF NOT EXISTS word_bank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(100) NOT NULL,
    cefr_level ENUM('A1','A2','B1','B2','C1','C2') NULL,
    part_of_speech VARCHAR(40) NULL,
    frequency_rank INT NULL
);

CREATE TABLE IF NOT EXISTS word_enrichment (
    word_id INT PRIMARY KEY,
    definition TEXT,
    phonetic VARCHAR(100),
    audio_url VARCHAR(300),
    collocations TEXT,
    example_en TEXT,
    translation_es TEXT,
    fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (word_id) REFERENCES word_bank(id) ON DELETE CASCADE
);

-- ── Problemas de programación competitiva (Bloque 4) ───────────────
CREATE TABLE IF NOT EXISTS cp_problems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    platform VARCHAR(40) DEFAULT 'codeforces',
    problem_url VARCHAR(400),
    name VARCHAR(200),
    rating INT NULL,
    tags VARCHAR(255),
    status ENUM('todo','attempted','solved','upsolved') DEFAULT 'todo',
    solved_at TIMESTAMP NULL DEFAULT NULL,
    time_spent INT DEFAULT 0,
    editorial_note TEXT,
    due_review DATE NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Bitácora de actividad (Bloque 1) ───────────────────────────────
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity VARCHAR(50) NULL,
    entity_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Caché del diccionario (Bloque 0) ───────────────────────────────
CREATE TABLE IF NOT EXISTS dictionary_cache (
    word VARCHAR(100) PRIMARY KEY,
    payload MEDIUMTEXT NOT NULL,
    fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Índices ─────────────────────────────────────────────────────────
CREATE INDEX idx_flashcards_due      ON flashcards (user_id, due_date);
CREATE INDEX idx_flashcards_deck     ON flashcards (user_id, deck);
CREATE INDEX idx_units_resource      ON units (resource_id);
CREATE INDEX idx_sessions_user       ON study_sessions (user_id);
CREATE INDEX idx_cp_due              ON cp_problems (user_id, due_review);
CREATE INDEX idx_resources_user      ON resources (user_id);
CREATE INDEX idx_resources_subject   ON resources (subject_id);
CREATE INDEX idx_wordbank_level      ON word_bank (cefr_level);

-- ── Datos demo de flashcards (vencen hoy para probar el repaso) ────
INSERT INTO flashcards (user_id, deck, front, back, example, extra, source, cefr_level, due_date) VALUES
(1, 'vocab', 'although', 'aunque / a pesar de', 'Although it was raining, we went out.', 'conjunción de contraste', 'manual', 'B1', CURRENT_DATE),
(1, 'vocab', 'achieve',  'lograr / conseguir',   'She worked hard to achieve her goals.', 'achieve a goal/result', 'manual', 'B1', CURRENT_DATE),
(1, 'cp',    '¿Qué es un DSU?', 'Disjoint Set Union: estructura para uniones/find con path compression + union by rank.', 'Útil en Kruskal y componentes conexas.', 'complejidad casi O(1) amortizado', 'manual', NULL, CURRENT_DATE);


-- ============================================================
-- StudyVault — Migración v3 (Bloque 4: integración Codeforces)
-- Ejecutar UNA VEZ después de migrations_v2.sql.
-- ============================================================
USE studyvault;

ALTER TABLE users ADD COLUMN cf_handle VARCHAR(64) NULL;
ALTER TABLE users ADD COLUMN cf_rating INT NULL;
ALTER TABLE users ADD COLUMN cf_synced_at TIMESTAMP NULL DEFAULT NULL;


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
