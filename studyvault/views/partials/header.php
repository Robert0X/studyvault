<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?> — <?= APP_NAME ?></title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css">
</head>
<body>

<!-- Navbar top -->
<nav class="navbar navbar-expand-lg navbar-dark sv-navbar">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>?page=dashboard">
            <i class="fa-solid fa-vault me-2"></i><?= APP_NAME ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($page ?? '') === 'dashboard' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>?page=dashboard">
                        <i class="fa-solid fa-gauge-high me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($page ?? '') === 'subjects' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>?page=subjects">
                        <i class="fa-solid fa-layer-group me-1"></i>Materias
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($page ?? '') === 'resources' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>?page=resources">
                        <i class="fa-solid fa-box-archive me-1"></i>Recursos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($page ?? '') === 'flashcards' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>?page=flashcards">
                        <i class="fa-solid fa-clone me-1"></i>Flashcards
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($page ?? '') === 'cp' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>?page=cp">
                        <i class="fa-solid fa-trophy me-1"></i>Competitiva
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($page ?? '') === 'goals' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>?page=goals">
                        <i class="fa-solid fa-bullseye me-1"></i>Metas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($page ?? '') === 'timer' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>?page=timer">
                        <i class="fa-solid fa-clock me-1"></i>Temporizador
                    </a>
                </li>
            </ul>
            <div class="navbar-nav align-items-center">
                <li class="nav-item">
                    <button class="nav-link btn btn-link border-0" id="darkToggle" onclick="toggleDarkMode()" title="Modo oscuro">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-circle-user me-1"></i>
                        <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>
                        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                            <span class="badge bg-warning text-dark ms-1">Admin</span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item text-danger" href="<?= BASE_URL ?>?page=logout">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-lg-2 d-none d-lg-block sv-sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'dashboard' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>?page=dashboard">
                            <i class="fa-solid fa-gauge-high me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'subjects' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>?page=subjects">
                            <i class="fa-solid fa-layer-group me-2"></i>Materias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'resources' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>?page=resources">
                            <i class="fa-solid fa-box-archive me-2"></i>Recursos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'flashcards' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>?page=flashcards">
                            <i class="fa-solid fa-clone me-2"></i>Flashcards
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'cp' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>?page=cp">
                            <i class="fa-solid fa-trophy me-2"></i>Competitiva
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'goals' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>?page=goals">
                            <i class="fa-solid fa-bullseye me-2"></i>Metas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($page ?? '') === 'timer' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>?page=timer">
                            <i class="fa-solid fa-clock me-2"></i>Temporizador
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>?page=resources&action=create">
                            <i class="fa-solid fa-circle-plus me-2"></i>Añadir recurso
                        </a>
                    </li>
                </ul>

                <hr class="my-3">

                <!-- Widget: Buscar en diccionario -->
                <div class="sv-widget">
                    <div class="sv-widget-title">
                        <i class="fa-solid fa-book-open me-1"></i> Diccionario EN
                    </div>
                    <div class="input-group input-group-sm">
                        <input type="text" id="dictSearch" class="form-control"
                               placeholder="Buscar palabra...">
                        <button class="btn btn-primary" id="dictBtn">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                    <div id="dictResult" class="mt-2"></div>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-lg-10 ms-sm-auto sv-main">
