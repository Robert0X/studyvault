<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña — StudyVault</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css">
</head>
<body class="sv-auth-bg">

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="row w-100 justify-content-center">
        <div class="col-12 col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <div class="sv-auth-logo"><i class="fa-solid fa-lock-open fa-2x"></i></div>
                <h1 class="fw-bold mt-3 text-dark">Nueva contraseña</h1>
            </div>

            <div class="card sv-auth-card shadow-lg border-0">
                <div class="card-body p-4">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success py-2">
                            <i class="fa-solid fa-circle-check me-1"></i>Contraseña actualizada. Ya puedes iniciar sesión.
                        </div>
                        <a href="<?= BASE_URL ?>?page=login" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="fa-solid fa-right-to-bracket me-1"></i>Ir al inicio de sesión
                        </a>
                    <?php elseif (!empty($error) && empty($record)): ?>
                        <div class="alert alert-danger py-2"><i class="fa-solid fa-triangle-exclamation me-1"></i><?= htmlspecialchars($error) ?></div>
                        <a href="<?= BASE_URL ?>?page=forgot" class="btn btn-outline-secondary w-100">Solicitar uno nuevo</a>
                    <?php else: ?>
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="<?= BASE_URL ?>?page=reset" novalidate>
                            <?= csrf_field() ?>
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                            <div class="mb-3">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" name="password" class="form-control"
                                       placeholder="Mínimo 8 caracteres" required minlength="8" autofocus>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Confirmar contraseña</label>
                                <input type="password" name="password_confirm" class="form-control"
                                       placeholder="Repite la contraseña" required minlength="8">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="fa-solid fa-floppy-disk me-1"></i>Guardar contraseña
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
