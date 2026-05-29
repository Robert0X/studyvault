<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña — StudyVault</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css">
</head>
<body class="sv-auth-bg">

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="row w-100 justify-content-center">
        <div class="col-12 col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <div class="sv-auth-logo"><i class="fa-solid fa-key fa-2x"></i></div>
                <h1 class="fw-bold mt-3 text-dark">Recuperar contraseña</h1>
                <p class="text-muted">Te enviaremos un enlace para restablecerla</p>
            </div>

            <div class="card sv-auth-card shadow-lg border-0">
                <div class="card-body p-4">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger py-2"><i class="fa-solid fa-circle-exclamation me-1"></i><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($info)): ?>
                        <div class="alert alert-success py-2"><i class="fa-solid fa-circle-check me-1"></i><?= htmlspecialchars($info) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($resetLink)): ?>
                        <div class="alert alert-info py-2 small">
                            <strong>Demo sin SMTP:</strong> usa este enlace para restablecer (válido 1h).<br>
                            <a class="text-break" href="<?= htmlspecialchars($resetLink) ?>"><?= htmlspecialchars($resetLink) ?></a>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>?page=forgot" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control"
                                       placeholder="tu@correo.com" required autofocus
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="fa-solid fa-paper-plane me-1"></i>Generar enlace
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>?page=login" class="small text-muted">
                            <i class="fa-solid fa-arrow-left me-1"></i>Volver al inicio de sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
