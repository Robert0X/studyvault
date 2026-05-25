<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $error    = '';

        if (empty($email) || empty($password)) {
            $error = 'Por favor completa todos los campos.';
        } else {
            $user = $this->userModel->findByEmail($email);
            if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                header('Location: ' . BASE_URL . '?page=dashboard');
                exit;
            } else {
                $error = 'Correo o contraseña incorrectos.';
            }
        }

        require __DIR__ . '/../views/auth/login.php';
    }

    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require __DIR__ . '/../views/auth/login.php';
            return;
        }

        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $error    = '';

        if (empty($name) || empty($email) || empty($password)) {
            $error = 'Todos los campos son obligatorios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo no es válido.';
        } elseif (strlen($password) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres.';
        } elseif ($this->userModel->emailExists($email)) {
            $error = 'Este correo ya está registrado.';
        } else {
            $id = $this->userModel->create($name, $email, $password);
            $user = $this->userModel->findById($id);
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: ' . BASE_URL . '?page=dashboard');
            exit;
        }

        $registerError = $error;
        require __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void {
        session_destroy();
        header('Location: ' . BASE_URL . '?page=login');
        exit;
    }
}
