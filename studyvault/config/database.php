<?php
class Database {
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct() {
        $host    = env('DB_HOST', 'localhost');
        $dbname  = env('DB_NAME', 'studyvault');
        $user    = env('DB_USER', 'root');
        $pass    = env('DB_PASS', '');
        $charset = env('DB_CHARSET', 'utf8mb4');

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}
