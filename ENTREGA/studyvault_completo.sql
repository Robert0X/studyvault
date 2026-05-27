-- ============================================================
-- StudyVault — ESQUEMA COMPLETO (canónico, limpio, en un solo archivo)
-- Importar en phpMyAdmin (pestaña Importar) o:  mysql -u root < studyvault_completo.sql
--
-- AVISO: BORRA la base "studyvault" si existe y la crea desde cero,
-- con todas las tablas finales (sin ALTERs) y datos demo.
-- Refleja el estado final del esquema (equivale a studyvault.sql + migraciones v2..v7).
-- ============================================================

DROP DATABASE IF EXISTS studyvault;
CREATE DATABASE studyvault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studyvault;

-- ── Usuarios ────────────────────────────────────────────────
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student') DEFAULT 'student',
    cf_handle VARCHAR(64) NULL,
    cf_rating INT NULL,
    cf_synced_at TIMESTAMP NULL DEFAULT NULL,
    daily_goal_minutes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Materias ────────────────────────────────────────────────
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'fa-book',
    color VARCHAR(20) DEFAULT '#4f46e5',
    user_id INT NOT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Recursos ────────────────────────────────────────────────
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
    total_units INT DEFAULT 0,
    unit_type ENUM('chapter','page','lesson','problem','custom') DEFAULT 'custom',
    is_habit TINYINT(1) DEFAULT 0,
    estimated_minutes INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_resources_user (user_id),
    KEY idx_resources_subject (subject_id)
);

-- ── Unidades (capítulos/lecciones de un recurso) ────────────
CREATE TABLE units (
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
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    KEY idx_units_resource (resource_id)
);

-- ── Metas / planes ──────────────────────────────────────────
CREATE TABLE goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    target_date DATE NULL,
    status ENUM('active','paused','done') DEFAULT 'active',
    is_public TINYINT(1) DEFAULT 0,
    cloned_from INT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE goal_resources (
    goal_id INT NOT NULL,
    resource_id INT NOT NULL,
    weight INT DEFAULT 1,
    PRIMARY KEY (goal_id, resource_id),
    FOREIGN KEY (goal_id) REFERENCES goals(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
);

-- ── Sesiones de estudio (tiempo) ────────────────────────────
CREATE TABLE study_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resource_id INT NULL,
    unit_id INT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL DEFAULT NULL,
    minutes INT DEFAULT 0,
    technique ENUM('read','practice','review','pomodoro') DEFAULT 'read',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_sessions_user (user_id)
);

-- ── Flashcards (repetición espaciada SM-2) ──────────────────
CREATE TABLE flashcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    deck VARCHAR(50) DEFAULT 'vocab',
    front TEXT NOT NULL,
    back TEXT NOT NULL,
    example TEXT,
    extra TEXT,
    source VARCHAR(100) DEFAULT 'manual',
    cefr_level VARCHAR(5) NULL,
    audio_url VARCHAR(300) NULL,
    ease_factor FLOAT DEFAULT 2.5,
    interval_days INT DEFAULT 0,
    repetitions INT DEFAULT 0,
    due_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('new','learning','mature','mastered') DEFAULT 'new',
    last_reviewed_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_flashcards_due (user_id, due_date),
    KEY idx_flashcards_deck (user_id, deck),
    KEY idx_flashcards_level (user_id, cefr_level)
);

-- ── Banco CEFR + enriquecimiento (estudio por nivel) ────────
CREATE TABLE word_bank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(100) NOT NULL,
    cefr_level ENUM('A1','A2','B1','B2','C1','C2') NULL,
    part_of_speech VARCHAR(40) NULL,
    frequency_rank INT NULL,
    KEY idx_wordbank_level (cefr_level)
);

CREATE TABLE word_enrichment (
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

-- ── Programación competitiva ────────────────────────────────
CREATE TABLE cp_problems (
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
    ease_factor FLOAT DEFAULT 2.5,
    interval_days INT DEFAULT 0,
    repetitions INT DEFAULT 0,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    KEY idx_cp_due (user_id, due_review),
    KEY idx_cp_status (user_id, status)
);

CREATE TABLE cp_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    language VARCHAR(30) DEFAULT 'cpp',
    code MEDIUMTEXT NOT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE cf_problemset_cache (
    contest_id INT NOT NULL,
    idx VARCHAR(10) NOT NULL,
    name VARCHAR(255),
    rating INT,
    tags VARCHAR(500),
    fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (contest_id, idx),
    KEY idx_problemset_rating (rating)
);

-- ── Soporte: bitácora, caché de diccionario, intentos de login ──
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity VARCHAR(50) NULL,
    entity_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE dictionary_cache (
    word VARCHAR(100) PRIMARY KEY,
    payload MEDIUMTEXT NOT NULL,
    fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    email VARCHAR(150) NOT NULL,
    success TINYINT(1) DEFAULT 0,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_login_ip (ip, attempted_at),
    KEY idx_login_email (email, attempted_at)
);

-- ============================================================
-- DATOS DEMO
-- Credenciales: admin@studyvault.com / password   ·   student@studyvault.com
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES
('Administrador', 'admin@studyvault.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Estudiante Demo', 'student@studyvault.com', '$2y$10$TKh8H1.PyfuviSpW4zsIs.CB5BuNkCQi2Qy/XkP0OKB.C8t.QCWK.', 'student');

INSERT INTO subjects (name, description, icon, color, user_id) VALUES
('Inglés', 'Recursos para aprender inglés: gramática, vocabulario, listening', 'fa-language', '#4f46e5', 1),
('Programación Competitiva', 'Algoritmos, estructuras de datos, problemas de CP', 'fa-code', '#f72585', 1),
('Programación Web', 'HTML, CSS, JavaScript, PHP, frameworks', 'fa-globe', '#4cc9f0', 1),
('Matemáticas', 'Cálculo, álgebra lineal, estadística', 'fa-calculator', '#7209b7', 1);

INSERT INTO resources (title, description, url, type, subject_id, user_id, status) VALUES
('Grammar in Use - Raymond Murphy', 'El libro más recomendado para gramática inglesa', 'https://www.cambridge.org/grammar-in-use', 'link', 1, 1, 'in_progress'),
('Anki - Flashcards', 'App para memorizar vocabulario con repetición espaciada', 'https://apps.ankiweb.net/', 'link', 1, 1, 'completed'),
('CP-Algorithms', 'Referencia completa de algoritmos para programación competitiva', 'https://cp-algorithms.com/', 'link', 2, 1, 'in_progress'),
('Codeforces', 'Plataforma de concursos de programación', 'https://codeforces.com/', 'link', 2, 1, 'pending'),
('MDN Web Docs', 'Documentación de HTML, CSS y JavaScript', 'https://developer.mozilla.org/', 'link', 3, 1, 'completed');

INSERT INTO flashcards (user_id, deck, front, back, example, extra, source, cefr_level, due_date) VALUES
(1, 'vocab', 'although', 'aunque / a pesar de', 'Although it was raining, we went out.', 'conjunción de contraste', 'manual', 'B1', CURRENT_DATE),
(1, 'vocab', 'achieve',  'lograr / conseguir',   'She worked hard to achieve her goals.', 'achieve a goal/result', 'manual', 'B1', CURRENT_DATE),
(1, 'cp',    '¿Qué es un DSU?', 'Disjoint Set Union: estructura para uniones/find con path compression + union by rank.', 'Útil en Kruskal y componentes conexas.', 'complejidad casi O(1) amortizado', 'manual', NULL, CURRENT_DATE);
