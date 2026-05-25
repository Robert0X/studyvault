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
