<?php
define('BASE_URL', function_exists('env') ? env('BASE_URL', 'http://localhost/studyvault/') : 'http://localhost/studyvault/');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', BASE_URL . 'assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('APP_NAME', 'StudyVault');
