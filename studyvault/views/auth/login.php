<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — StudyVault</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css">
</head>
<body class="sv-auth-bg">

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="row w-100 justify-content-center">
        <div class="col-12 col-md-5 col-lg-4">

            <!-- Logo -->
            <div class="text-center mb-4">
                <div class="sv-auth-logo">
                    <i class="fa-solid fa-vault fa-3x"></i>
                </div>
                <h1 class="fw-bold mt-3 text-dark">StudyVault</h1>
                <p class="text-muted">Tu gestor de material de estudio</p>
            </div>

            <!-- Tabs Login / Registro -->
            <div class="card sv-auth-card shadow-lg border-0">
                <div class="card-body p-4">
                    <ul class="nav nav-pills nav-fill mb-4" id="authTabs">
                        <li class="nav-item">
                            <button class="nav-link <?= empty($registerError) ? 'active' : '' ?>"
                                    id="loginTab" data-bs-toggle="pill" data-bs-target="#loginPanel">
                                <i class="fa-solid fa-right-to-bracket me-1"></i>Entrar
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= !empty($registerError) ? 'active' : '' ?>"
                                    id="registerTab" data-bs-toggle="pill" data-bs-target="#registerPanel">
                                <i class="fa-solid fa-user-plus me-1"></i>Registrarse
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- LOGIN -->
                        <div class="tab-pane fade <?= empty($registerError) ? 'show active' : '' ?>" id="loginPanel">
                            <?php if (!empty($error) && empty($registerError)): ?>
                                <div class="alert alert-danger py-2">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Credenciales demo -->
                            <div class="alert alert-info py-2 small">
                                <strong>Demo:</strong> admin@studyvault.com / <code>password</code>
                            </div>

                            <form method="POST" action="<?= BASE_URL ?>?page=login" novalidate>
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label class="form-label">Correo electrónico</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                        <input type="email" name="email" class="form-control"
                                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                               placeholder="tu@correo.com" required autofocus>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" name="password" id="loginPw" class="form-control"
                                               placeholder="••••••••" required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePw('loginPw', this)" tabindex="-1"><i class="fa-solid fa-eye"></i></button>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                    <i class="fa-solid fa-right-to-bracket me-1"></i>Iniciar sesión
                                </button>
                                <div class="text-center mt-3">
                                    <a href="<?= BASE_URL ?>?page=forgot" class="small text-muted">
                                        <i class="fa-solid fa-key me-1"></i>¿Olvidaste tu contraseña?
                                    </a>
                                </div>
                                <hr class="my-3">
                                <div class="text-center">
                                    <a href="<?= BASE_URL ?>?page=goals&action=browse" class="small text-muted text-decoration-none">
                                        <i class="fa-solid fa-globe me-1"></i>Explorar plantillas públicas como invitado
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- REGISTRO -->
                        <div class="tab-pane fade <?= !empty($registerError) ? 'show active' : '' ?>" id="registerPanel">
                            <?php if (!empty($registerError)): ?>
                                <div class="alert alert-danger py-2">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                                    <?= htmlspecialchars($registerError) ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="<?= BASE_URL ?>?page=register" novalidate>
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label class="form-label">Nombre completo</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                        <input type="text" name="name" class="form-control"
                                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                               placeholder="Tu nombre" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Correo electrónico</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                        <input type="email" name="email" class="form-control"
                                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                               placeholder="tu@correo.com" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" name="password" id="regPw" class="form-control"
                                               placeholder="Mínimo 8 caracteres" required minlength="8">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePw('regPw', this)" tabindex="-1"><i class="fa-solid fa-eye"></i></button>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Confirmar contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                        <input type="password" name="password_confirm" id="regPw2" class="form-control"
                                               placeholder="Repite la contraseña" required minlength="8">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePw('regPw2', this)" tabindex="-1"><i class="fa-solid fa-eye"></i></button>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                                    <i class="fa-solid fa-user-plus me-1"></i>Crear cuenta
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePw(id, btn) {
    const inp = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fa-solid fa-eye-slash'; }
    else { inp.type = 'password'; icon.className = 'fa-solid fa-eye'; }
}
</script>
</body>
</html>
