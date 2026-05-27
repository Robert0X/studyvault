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
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $throttle = new LoginThrottle();

        if ($throttle->tooMany($ip, $email)) {
            $wait  = (int) ceil($throttle->secondsUntilRetry($ip, $email) / 60);
            $error = "Demasiados intentos fallidos. Intenta de nuevo en {$wait} minuto(s).";
        } elseif (empty($email) || empty($password)) {
            $error = 'Por favor completa todos los campos.';
        } else {
            $user = $this->userModel->findByEmail($email);
            if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                $throttle->clearFailures($email);
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                header('Location: ' . BASE_URL . '?page=dashboard');
                exit;
            } else {
                $throttle->record($ip, $email, false);
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
        } elseif (strlen($password) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif (($_POST['password_confirm'] ?? '') !== $password) {
            $error = 'Las contraseñas no coinciden.';
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
