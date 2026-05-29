-- ============================================================
-- StudyVault — ESQUEMA COMPLETO + DATOS DEMO ABUNDANTES
-- Importar en phpMyAdmin (pestaña Importar) o:  mysql -u root < studyvault_completo.sql
--
-- AVISO: BORRA la base "studyvault" si existe y la crea desde cero,
-- con todas las tablas finales (esquema v8) y datos demo amplios
-- listos para probar TODAS las funcionalidades (gráficas, racha,
-- notificaciones, plantillas públicas, panel admin, etc.)
--
-- Credenciales demo (ambas con contraseña "password"):
--   admin@studyvault.com   (rol: admin)
--   student@studyvault.com (rol: student)
-- ============================================================

DROP DATABASE IF EXISTS studyvault;
CREATE DATABASE studyvault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studyvault;

-- ============================================================
-- ESQUEMA
-- ============================================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student') DEFAULT 'student',
    active TINYINT(1) NOT NULL DEFAULT 1,
    cf_handle VARCHAR(64) NULL,
    cf_rating INT NULL,
    cf_synced_at TIMESTAMP NULL DEFAULT NULL,
    daily_goal_minutes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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

-- ============================================================
-- DATOS DEMO
-- Hash bcrypt verificado para "password" en ambas cuentas.
-- ============================================================

INSERT INTO users (id, name, email, password, role, active, cf_handle, cf_rating, cf_synced_at, daily_goal_minutes) VALUES
(1, 'Administrador',    'admin@studyvault.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',   1, 'tourist', 3779, NOW() - INTERVAL 2 HOUR, 45),
(2, 'Estudiante Demo',  'student@studyvault.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 1, 'jiangly', 3814, NOW() - INTERVAL 1 DAY,  60);

-- ── Materias ─────────────────────────────────────────────────────
INSERT INTO subjects (id, name, description, icon, color, user_id) VALUES
(1, 'Inglés',                   'Recursos para aprender inglés: gramática, vocabulario, listening', 'fa-language',    '#4f46e5', 1),
(2, 'Programación Competitiva', 'Algoritmos, estructuras de datos, problemas de CP',                'fa-code',        '#f72585', 1),
(3, 'Programación Web',         'HTML, CSS, JavaScript, PHP, frameworks',                            'fa-globe',       '#4cc9f0', 1),
(4, 'Matemáticas',              'Cálculo, álgebra lineal, estadística',                              'fa-calculator',  '#7209b7', 1),
(5, 'Inglés (Estudiante)',      'Mi plan personal de inglés',                                        'fa-language',    '#22c55e', 2),
(6, 'CP (Estudiante)',          'Resolución de problemas en Codeforces',                             'fa-code',        '#ef4444', 2);

-- ── Recursos ────────────────────────────────────────────────────
INSERT INTO resources (id, title, description, url, type, subject_id, user_id, status, total_units, unit_type) VALUES
(1,  'Grammar in Use - Raymond Murphy', 'El libro más recomendado para gramática inglesa',          'https://www.cambridge.org/grammar-in-use', 'link',  1, 1, 'in_progress', 10, 'chapter'),
(2,  'Anki - Flashcards',               'App para memorizar vocabulario con repetición espaciada',  'https://apps.ankiweb.net/',                'link',  1, 1, 'completed',    0, 'custom'),
(3,  'CP-Algorithms',                   'Referencia completa de algoritmos para CP',                'https://cp-algorithms.com/',               'link',  2, 1, 'in_progress', 12, 'chapter'),
(4,  'Codeforces',                      'Plataforma de concursos de programación',                  'https://codeforces.com/',                  'link',  2, 1, 'pending',      0, 'custom'),
(5,  'MDN Web Docs',                    'Documentación de HTML, CSS y JavaScript',                  'https://developer.mozilla.org/',           'link',  3, 1, 'completed',    0, 'custom'),
(6,  'Khan Academy - Calculus',         'Curso completo de cálculo diferencial e integral',         'https://www.khanacademy.org/',             'link',  4, 1, 'in_progress',  8, 'lesson'),
(7,  'English Listening: TED Talks',    'Listening con transcripción para entrenar el oído',        'https://www.ted.com/',                     'video', 1, 1, 'in_progress',  0, 'custom'),
(8,  'Competitive Programmer''s Handbook', 'PDF gratuito de Antti Laaksonen',                       'https://cses.fi/book/book.pdf',            'pdf',   2, 1, 'in_progress', 30, 'chapter'),
(9,  'Anotación: trucos C++',           'Mis snippets favoritos de C++',                            NULL,                                       'note',  2, 1, 'completed',    0, 'custom'),
-- Recursos del estudiante (para demo cross-user / plantillas)
(10, 'Cambridge B2 First',              'Libro oficial de preparación FCE',                         'https://www.cambridgeenglish.org/',        'link',  5, 2, 'in_progress',  8, 'chapter'),
(11, 'USACO Guide',                     'Guía oficial para USACO',                                  'https://usaco.guide/',                     'link',  6, 2, 'in_progress', 10, 'lesson'),
(12, 'BBC 6 Minute English',            'Podcast corto diario para listening',                      'https://www.bbc.co.uk/learningenglish/',   'video', 5, 2, 'in_progress',  0, 'custom');

-- ── Unidades (capítulos dentro de los libros) ─────────────────────
INSERT INTO units (resource_id, title, order_index, status, completed_at) VALUES
(1, 'Capítulo 1 — Present simple',  1, 'completed',   NOW() - INTERVAL 20 DAY),
(1, 'Capítulo 2 — Present continuous',2,'completed',  NOW() - INTERVAL 17 DAY),
(1, 'Capítulo 3 — Past simple',     3, 'completed',   NOW() - INTERVAL 14 DAY),
(1, 'Capítulo 4 — Past continuous', 4, 'in_progress', NULL),
(1, 'Capítulo 5 — Present perfect', 5, 'pending',     NULL),
(1, 'Capítulo 6 — Past perfect',    6, 'pending',     NULL),
(1, 'Capítulo 7 — Future forms',    7, 'pending',     NULL),
(1, 'Capítulo 8 — Conditionals',    8, 'pending',     NULL),
(1, 'Capítulo 9 — Reported speech', 9, 'pending',     NULL),
(1, 'Capítulo 10 — Passive voice',  10,'pending',     NULL),

(3, 'Binary search',                1, 'completed',   NOW() - INTERVAL 15 DAY),
(3, 'Two pointers',                 2, 'completed',   NOW() - INTERVAL 12 DAY),
(3, 'Dynamic programming (basics)', 3, 'completed',   NOW() - INTERVAL 9 DAY),
(3, 'Graph BFS/DFS',                4, 'completed',   NOW() - INTERVAL 7 DAY),
(3, 'Dijkstra',                     5, 'in_progress', NULL),
(3, 'Segment tree',                 6, 'pending',     NULL),
(3, 'Trie',                         7, 'pending',     NULL),
(3, 'Number theory',                8, 'pending',     NULL),
(3, 'String matching',              9, 'pending',     NULL),
(3, 'Convex hull',                  10,'pending',     NULL),
(3, 'Heavy-Light Decomposition',    11,'pending',     NULL),
(3, 'Suffix array',                 12,'pending',     NULL);

-- ── Metas ────────────────────────────────────────────────────────
INSERT INTO goals (id, user_id, title, description, target_date, status, is_public, created_at) VALUES
(1, 1, 'Inglés B2 para diciembre',          'Llegar a B2 estudiando con Murphy + flashcards + listening', CURDATE() + INTERVAL 60 DAY, 'active', 0, NOW() - INTERVAL 25 DAY),
(2, 1, 'Llegar a 1800 en Codeforces',       'Subir el rating practicando CP-Algorithms + problemas',     CURDATE() + INTERVAL 90 DAY, 'active', 1, NOW() - INTERVAL 30 DAY),
(3, 1, 'Terminar Khan Academy Calculus',    'Refrescar cálculo para optimización',                       CURDATE() + INTERVAL 30 DAY, 'active', 0, NOW() - INTERVAL 40 DAY),
(4, 2, 'Roadmap CP — junior division',      'Plan público compartido por el estudiante para C',          CURDATE() + INTERVAL 120 DAY,'active', 1, NOW() - INTERVAL 10 DAY);

INSERT INTO goal_resources (goal_id, resource_id, weight) VALUES
(1, 1, 3), (1, 2, 1), (1, 7, 2),
(2, 3, 3), (2, 4, 1), (2, 8, 2), (2, 9, 1),
(3, 6, 1),
(4, 11, 3), (4, 12, 1);

-- ── Sesiones de estudio (últimos 30 días → racha + heatmap + gráficas) ──
INSERT INTO study_sessions (user_id, resource_id, started_at, ended_at, minutes, technique) VALUES
-- Hoy (admin estudia)
(1, 1, NOW() - INTERVAL 30 MINUTE,  NOW(),                       25, 'pomodoro'),
(1, 3, NOW() - INTERVAL 90 MINUTE,  NOW() - INTERVAL 60 MINUTE,  30, 'practice'),
-- Ayer
(1, 1, NOW() - INTERVAL 1 DAY,      NOW() - INTERVAL 1 DAY + INTERVAL 25 MINUTE, 25, 'pomodoro'),
(1, 3, NOW() - INTERVAL 1 DAY,      NOW() - INTERVAL 1 DAY + INTERVAL 40 MINUTE, 40, 'read'),
-- Anteayer
(1, 7, NOW() - INTERVAL 2 DAY,      NOW() - INTERVAL 2 DAY + INTERVAL 20 MINUTE, 20, 'review'),
-- D-3
(1, 1, NOW() - INTERVAL 3 DAY,      NOW() - INTERVAL 3 DAY + INTERVAL 50 MINUTE, 50, 'read'),
-- D-4
(1, 3, NOW() - INTERVAL 4 DAY,      NOW() - INTERVAL 4 DAY + INTERVAL 35 MINUTE, 35, 'practice'),
-- D-5
(1, 6, NOW() - INTERVAL 5 DAY,      NOW() - INTERVAL 5 DAY + INTERVAL 25 MINUTE, 25, 'pomodoro'),
-- D-6 (cierra racha de 7 días)
(1, 1, NOW() - INTERVAL 6 DAY,      NOW() - INTERVAL 6 DAY + INTERVAL 25 MINUTE, 25, 'pomodoro'),
-- D-8 .. D-14 (más datos para gráfica de 30d)
(1, 3, NOW() - INTERVAL 8 DAY,      NOW() - INTERVAL 8 DAY + INTERVAL 45 MINUTE, 45, 'practice'),
(1, 1, NOW() - INTERVAL 9 DAY,      NOW() - INTERVAL 9 DAY + INTERVAL 30 MINUTE, 30, 'read'),
(1, 7, NOW() - INTERVAL 10 DAY,     NOW() - INTERVAL 10 DAY + INTERVAL 20 MINUTE,20, 'review'),
(1, 6, NOW() - INTERVAL 12 DAY,     NOW() - INTERVAL 12 DAY + INTERVAL 60 MINUTE,60, 'read'),
(1, 8, NOW() - INTERVAL 14 DAY,     NOW() - INTERVAL 14 DAY + INTERVAL 50 MINUTE,50, 'practice'),
-- D-18 .. D-25
(1, 1, NOW() - INTERVAL 18 DAY,     NOW() - INTERVAL 18 DAY + INTERVAL 25 MINUTE,25, 'pomodoro'),
(1, 3, NOW() - INTERVAL 20 DAY,     NOW() - INTERVAL 20 DAY + INTERVAL 40 MINUTE,40, 'practice'),
(1, 6, NOW() - INTERVAL 22 DAY,     NOW() - INTERVAL 22 DAY + INTERVAL 30 MINUTE,30, 'read'),
(1, 1, NOW() - INTERVAL 25 DAY,     NOW() - INTERVAL 25 DAY + INTERVAL 50 MINUTE,50, 'read'),
(1, 3, NOW() - INTERVAL 28 DAY,     NOW() - INTERVAL 28 DAY + INTERVAL 35 MINUTE,35, 'practice'),
-- Algunas sesiones del estudiante
(2, 10, NOW() - INTERVAL 1 DAY,     NOW() - INTERVAL 1 DAY + INTERVAL 25 MINUTE, 25, 'pomodoro'),
(2, 11, NOW() - INTERVAL 2 DAY,     NOW() - INTERVAL 2 DAY + INTERVAL 50 MINUTE, 50, 'practice'),
(2, 12, NOW() - INTERVAL 3 DAY,     NOW() - INTERVAL 3 DAY + INTERVAL 15 MINUTE, 15, 'review');

-- ── Flashcards (mezcla de niveles CEFR, estados SM-2 y vencimientos) ──
INSERT INTO flashcards (user_id, deck, front, back, example, extra, source, cefr_level, audio_url, ease_factor, interval_days, repetitions, due_date, status, last_reviewed_at) VALUES
-- Admin: vocabulario inglés
(1, 'vocab', 'although',     'aunque / a pesar de',      'Although it was raining, we went out.',           'conjunción de contraste',         'manual', 'B1', NULL, 2.6, 6, 2, CURDATE(),                  'mature',   NOW() - INTERVAL 1 DAY),
(1, 'vocab', 'achieve',      'lograr / conseguir',       'She worked hard to achieve her goals.',           'achieve a goal/result',           'manual', 'B1', NULL, 2.5, 1, 1, CURDATE(),                  'learning', NOW() - INTERVAL 1 DAY),
(1, 'vocab', 'overwhelmed',  'abrumado',                 'I felt overwhelmed by the news.',                 'collocations: completely, totally','dict', 'B2', NULL, 2.5, 0, 0, CURDATE(),                  'new',      NULL),
(1, 'vocab', 'commitment',   'compromiso',               'They have a strong commitment to quality.',       NULL,                              'manual', 'B2', NULL, 2.7, 15, 3, CURDATE() + INTERVAL 5 DAY,  'mature',   NOW() - INTERVAL 10 DAY),
(1, 'vocab', 'reluctant',    'reacio / renuente',        'He was reluctant to admit his mistake.',          'be reluctant to do sth',          'manual', 'C1', NULL, 2.8, 25, 4, CURDATE() + INTERVAL 15 DAY, 'mature',   NOW() - INTERVAL 8 DAY),
(1, 'vocab', 'inevitable',   'inevitable',               'Conflict was inevitable.',                        NULL,                              'manual', 'C1', NULL, 2.9, 95, 6, CURDATE() + INTERVAL 60 DAY, 'mastered', NOW() - INTERVAL 25 DAY),
(1, 'vocab', 'ubiquitous',   'omnipresente',             'Smartphones have become ubiquitous.',             NULL,                              'dict',   'C2', NULL, 2.5, 0, 0, CURDATE(),                  'new',      NULL),
(1, 'vocab', 'hello',        'hola',                     'Hello, how are you?',                             NULL,                              'manual', 'A1', NULL, 2.5, 0, 0, CURDATE(),                  'new',      NULL),
(1, 'vocab', 'breakfast',    'desayuno',                 'I have breakfast at 8.',                          NULL,                              'manual', 'A1', NULL, 2.6, 6, 2, CURDATE() + INTERVAL 2 DAY,  'learning', NOW() - INTERVAL 3 DAY),
(1, 'vocab', 'although',     'sinónimo: even though',    'Even though it rained, we went.',                 NULL,                              'manual', 'B1', NULL, 2.5, 1, 1, CURDATE() + INTERVAL 1 DAY,  'learning', NOW() - INTERVAL 1 DAY),
(1, 'vocab', 'pursue',       'perseguir (objetivo)',     'She pursued a career in medicine.',               'pursue a career/dream',           'manual', 'B2', NULL, 2.7, 6, 2, CURDATE() + INTERVAL 4 DAY,  'learning', NOW() - INTERVAL 2 DAY),
(1, 'vocab', 'embark',       'embarcarse',               'They embarked on a new journey.',                 'embark on/upon sth',              'manual', 'C1', NULL, 2.5, 0, 0, CURDATE(),                  'new',      NULL),
(1, 'vocab', 'lunch',        'almuerzo',                 'Let''s have lunch together.',                     NULL,                              'manual', 'A1', NULL, 2.5, 1, 1, CURDATE(),                  'learning', NOW() - INTERVAL 1 DAY),
(1, 'vocab', 'work',         'trabajar / trabajo',       'I work from home.',                               NULL,                              'manual', 'A2', NULL, 2.7, 21, 3, CURDATE() + INTERVAL 18 DAY, 'mature',   NOW() - INTERVAL 3 DAY),
(1, 'vocab', 'opportunity',  'oportunidad',              'A great opportunity arose.',                      NULL,                              'manual', 'B1', NULL, 2.6, 6, 2, CURDATE() + INTERVAL 3 DAY,  'learning', NOW() - INTERVAL 3 DAY),
-- Admin: deck CP
(1, 'cp', '¿Qué es un DSU?',          'Disjoint Set Union (Union-Find) con path compression y union by rank.', 'Útil en Kruskal y componentes conexas.', 'casi O(1) amortizado', 'manual', NULL, NULL, 2.5, 1, 1, CURDATE(),                 'learning', NOW() - INTERVAL 1 DAY),
(1, 'cp', '¿Para qué sirve Dijkstra?', 'Camino mínimo desde un origen en grafo con pesos no negativos.',       'O((V+E) log V) con heap',                NULL,                   'manual', NULL, NULL, 2.6, 6, 2, CURDATE(),                 'mature',   NOW() - INTERVAL 6 DAY),
(1, 'cp', '¿Qué es memoización?',     'Cachear resultados de subproblemas en DP top-down.',                   'evita recomputar la misma f(state)',     NULL,                   'manual', NULL, NULL, 2.5, 0, 0, CURDATE(),                 'new',      NULL),
(1, 'cp', '¿Complejidad de KMP?',     'O(n + m) para matching de patrón en texto.',                           NULL,                                     NULL,                   'manual', NULL, NULL, 2.8, 21, 3, CURDATE() + INTERVAL 14 DAY,'mature',  NOW() - INTERVAL 7 DAY),
-- Estudiante (pocas tarjetas)
(2, 'vocab', 'although',     'aunque',                   'Although it rained...',                           NULL,                              'manual', 'B1', NULL, 2.5, 0, 0, CURDATE(),                  'new',      NULL),
(2, 'vocab', 'manage to',    'arreglárselas para',       'I managed to finish on time.',                    NULL,                              'manual', 'B1', NULL, 2.5, 1, 1, CURDATE(),                  'learning', NOW() - INTERVAL 1 DAY),
(2, 'cp',    '¿Qué es BFS?', 'Búsqueda en anchura, O(V+E)',                                                  NULL,                                     NULL,                   'manual', NULL, NULL, 2.5, 1, 1, CURDATE(),                 'learning', NOW() - INTERVAL 1 DAY);

-- ── Programación competitiva (problemas del admin) ──
INSERT INTO cp_problems (user_id, platform, problem_url, name, rating, tags, status, solved_at, editorial_note) VALUES
(1, 'codeforces', 'https://codeforces.com/problemset/problem/4/A',   'A. Watermelon',           800,  'brute force, math',                          'solved',   NOW() - INTERVAL 20 DAY, 'Par - sí; impar - no.'),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/71/A',  'A. Way Too Long Words',   800,  'strings, implementation',                    'solved',   NOW() - INTERVAL 19 DAY, NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/158/A', 'A. Next Round',           800,  'implementation',                              'solved',   NOW() - INTERVAL 18 DAY, NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/231/A', 'A. Team',                 800,  'brute force, greedy',                         'solved',   NOW() - INTERVAL 17 DAY, NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/282/A', 'A. Bit++',                800,  'implementation',                              'solved',   NOW() - INTERVAL 16 DAY, NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/118/A', 'A. String Task',          1000, 'implementation, strings',                     'solved',   NOW() - INTERVAL 15 DAY, NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/4/C',   'C. Registration system',  1300, 'data structures, hashing, implementation',    'solved',   NOW() - INTERVAL 14 DAY, 'unordered_map<string,int> con contador para duplicados.'),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/580/C', 'C. Kefa and Park',        1500, 'dfs and similar, graphs, trees',              'solved',   NOW() - INTERVAL 12 DAY, 'DFS arrastrando racha consecutiva de gatos.'),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/489/B', 'B. BerSU Ball',           1200, 'dp, greedy, sortings, two pointers',          'solved',   NOW() - INTERVAL 11 DAY, NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/727/A', 'A. Transformation',       1000, 'dfs and similar, math',                       'solved',   NOW() - INTERVAL 10 DAY, NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/977/F', 'F. Consecutive Subsequence', 1700, 'dp, hashing',                              'solved',   NOW() - INTERVAL 9 DAY,  'dp[x]=dp[x-1]+1 con map'),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/466/C', 'C. Number of Ways',       1700, 'binary search, brute force, dp',              'solved',   NOW() - INTERVAL 7 DAY,  'sufijos con la misma suma / 3'),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/796/C', 'C. Bank Hacking',         1600, 'binary search, sortings, trees',              'upsolved', NOW() - INTERVAL 6 DAY,  NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/702/C', 'C. Cellular Network',     1400, 'binary search, implementation',               'solved',   NOW() - INTERVAL 5 DAY,  NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/281/D', 'D. Two Problems',         1900, 'data structures, dp, hashing, math',          'attempted',NULL,                    'pendiente revisar editorial'),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/1133/D','D. Zero Quantity Maximization', 1500, 'math, sortings, hashing',                'todo',     NULL,                    NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/702/B', 'B. Powers of Two',        1200, 'math, two pointers',                          'todo',     NULL,                    NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/1213/D2','D2. Equalizing by Division', 1300, 'sortings, brute force',                   'attempted',NULL,                    NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/1335/C','C. Two Teams Composing', 1200, 'greedy, math',                                 'solved',   NOW() - INTERVAL 4 DAY,  NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/1714/C','C. Minimum Varied Number',900, 'greedy',                                       'solved',   NOW() - INTERVAL 3 DAY,  NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/1873/D','D. 1D Eraser',           1100, 'greedy, implementation',                       'solved',   NOW() - INTERVAL 2 DAY,  NULL),
(1, 'codeforces', 'https://codeforces.com/problemset/problem/1899/C','C. Yarik and Array',     1100, 'dp, greedy, implementation',                   'solved',   NOW() - INTERVAL 1 DAY,  NULL),
-- Uno marcado para repaso (SM-2 en CP) — vence hoy
(1, 'codeforces', 'https://codeforces.com/problemset/problem/580/C', 'C. Kefa and Park (REPASO)', 1500, 'dfs and similar, graphs, trees',            'solved',   NOW() - INTERVAL 12 DAY, 'Marcado para repaso espaciado.'),
-- Estudiante (algunos problemas)
(2, 'codeforces', 'https://codeforces.com/problemset/problem/4/A',   'A. Watermelon',           800,  'brute force, math',                          'solved',   NOW() - INTERVAL 5 DAY,  NULL),
(2, 'codeforces', 'https://codeforces.com/problemset/problem/231/A', 'A. Team',                 800,  'brute force, greedy',                         'solved',   NOW() - INTERVAL 4 DAY,  NULL),
(2, 'codeforces', 'https://codeforces.com/problemset/problem/118/A', 'A. String Task',          1000, 'implementation, strings',                     'attempted',NULL,                    NULL);

-- Activar repaso espaciado de un problema (para ver la cola "Repasar" funcionar)
UPDATE cp_problems SET due_review = CURDATE(), ease_factor = 2.5, interval_days = 1, repetitions = 1
 WHERE user_id = 1 AND name = 'C. Kefa and Park (REPASO)';

-- ── Plantillas de código CP ───────────────────────────────────────
INSERT INTO cp_templates (user_id, title, language, code) VALUES
(1, 'Plantilla base C++', 'cpp', '#include <bits/stdc++.h>\nusing namespace std;\nusing ll = long long;\n\nint main() {\n    ios::sync_with_stdio(false);\n    cin.tie(nullptr);\n    int t; cin >> t;\n    while (t--) {\n        // tu solución\n    }\n    return 0;\n}'),
(1, 'DSU (Union-Find)',   'cpp', 'struct DSU {\n    vector<int> p, r;\n    DSU(int n): p(n), r(n,0) { iota(p.begin(), p.end(), 0); }\n    int find(int x) { return p[x]==x ? x : p[x]=find(p[x]); }\n    bool unite(int a, int b){ a=find(a); b=find(b); if(a==b) return false; if(r[a]<r[b]) swap(a,b); p[b]=a; if(r[a]==r[b]) r[a]++; return true; }\n};'),
(2, 'Lectura rápida Java','java', 'BufferedReader br = new BufferedReader(new InputStreamReader(System.in));\nStreamTokenizer in = new StreamTokenizer(br);\nin.nextToken(); int n = (int) in.nval;');

-- ── Notificaciones de muestra (mezcla leídas/no leídas) ──────────
INSERT INTO notifications (user_id, type, title, body, link, icon, read_at, created_at) VALUES
(1, 'reviews_due',    'Tienes 5 tarjetas por repasar hoy',           'La repetición espaciada funciona mejor si repasas al día.', '?page=flashcards&action=study',          'fa-clone',     NULL,                       NOW() - INTERVAL 10 MINUTE),
(1, 'cp_reviews_due', 'Tienes 1 problema de CP por repasar',         'Reafirma lo aprendido antes de que se olvide.',             '?page=cp&action=review',                 'fa-trophy',    NULL,                       NOW() - INTERVAL 30 MINUTE),
(1, 'daily_goal',     'Te faltan 20 min para tu meta diaria',        'Hoy llevas 25 de 45 min.',                                  '?page=timer',                            'fa-clock',     NULL,                       NOW() - INTERVAL 2 HOUR),
(1, 'streak_at_risk', 'Tu racha de 7 día(s) está en riesgo',         'Estudia al menos 1 minuto hoy para mantenerla.',            '?page=timer',                            'fa-fire',      NOW() - INTERVAL 1 DAY,     NOW() - INTERVAL 1 DAY),
(1, 'goal_behind_3',  'Vas atrasado en: Terminar Khan Academy Calculus','Avance 12% — esperado 60%.',                              '?page=goals&action=show&id=3',           'fa-bullseye',  NOW() - INTERVAL 2 DAY,     NOW() - INTERVAL 2 DAY),
(2, 'reviews_due',    'Tienes 3 tarjetas por repasar hoy',           NULL,                                                        '?page=flashcards&action=study',          'fa-clone',     NULL,                       NOW() - INTERVAL 1 HOUR);

-- ── Bitácora de actividad ────────────────────────────────────────
INSERT INTO activity_log (user_id, action, entity, entity_id, created_at) VALUES
(1, 'login',                NULL,    NULL,  NOW() - INTERVAL 30 MINUTE),
(1, 'flashcard.review',     'card',  1,     NOW() - INTERVAL 1 HOUR),
(1, 'flashcard.review',     'card',  2,     NOW() - INTERVAL 1 HOUR),
(1, 'cp.sync',              NULL,    NULL,  NOW() - INTERVAL 2 HOUR),
(1, 'goal.create',          'goal',  1,     NOW() - INTERVAL 25 DAY),
(1, 'goal.create',          'goal',  2,     NOW() - INTERVAL 30 DAY),
(1, 'resource.create',      'resource', 1,  NOW() - INTERVAL 35 DAY),
(2, 'login',                NULL,    NULL,  NOW() - INTERVAL 1 DAY),
(2, 'goal.clone',           'goal',  4,     NOW() - INTERVAL 9 DAY);
