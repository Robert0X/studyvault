<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PasswordReset.php';

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
            if ($user && (int)($user['active'] ?? 1) === 0) {
                $throttle->record($ip, $email, false);
                $error = 'Tu cuenta está desactivada. Contacta al administrador.';
            } elseif ($user && $this->userModel->verifyPassword($password, $user['password'])) {
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

    /**
     * Solicitar recuperación de contraseña.
     * Genera token y muestra el enlace en pantalla (sin SMTP local).
     */
    public function forgot(): void {
        $info = '';
        $error = '';
        $resetLink = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Rate limit: 5 solicitudes por sesión cada 10 minutos.
            if (!rate_limit('forgot_pwd', 5, 600)) {
                $error = 'Demasiadas solicitudes. Espera unos minutos.';
            } else {
                $email = trim($_POST['email'] ?? '');
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Ingresa un correo válido.';
                } else {
                    $user = $this->userModel->findByEmail($email);
                    // Respuesta uniforme (no revela si existe), pero generamos enlace si existe.
                    if ($user) {
                        $pr = new PasswordReset();
                        $token = $pr->create((int) $user['id']);
                        $resetLink = BASE_URL . '?page=reset&token=' . $token;
                    }
                    $info = 'Si el correo está registrado, se generó un enlace de recuperación. Es válido por 1 hora.';
                }
            }
        }
        require __DIR__ . '/../views/auth/forgot.php';
    }

    /** Reestablecer contraseña dado un token válido. */
    public function reset(): void {
        $token = $_GET['token'] ?? ($_POST['token'] ?? '');
        $token = is_string($token) ? trim($token) : '';
        $pr = new PasswordReset();
        $record = $token !== '' ? $pr->findValid($token) : false;
        $error = '';
        $success = false;

        if (!$record) {
            $error = 'El enlace no es válido o ha expirado. Solicita uno nuevo.';
            require __DIR__ . '/../views/auth/reset.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['password_confirm'] ?? '';
            if (strlen($password) < 8) {
                $error = 'La contraseña debe tener al menos 8 caracteres.';
            } elseif ($password !== $confirm) {
                $error = 'Las contraseñas no coinciden.';
            } else {
                $this->userModel->updatePassword((int) $record['user_id'], $password);
                $pr->markUsed((int) $record['id']);
                $success = true;
            }
        }
        require __DIR__ . '/../views/auth/reset.php';
    }
}
