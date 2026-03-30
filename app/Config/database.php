<?php

try {

    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $db   = $_ENV['DB_DATABASE'] ?? '';
    $user = $_ENV['DB_USERNAME'] ?? '';
    $pass = $_ENV['DB_PASSWORD'] ?? '';
    $port = $_ENV['DB_PORT'] ?? '3306';

    if (!$db || !$user) {
        throw new Exception('Database config missing in .env');
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => false,
    ]);

} catch (\Exception $e) {

    if ($_ENV['APP_ENV'] === 'local') {
        die('DB Error: ' . $e->getMessage());
    }

    // log lại nếu cần
    error_log($e->getMessage());

    die('Database connection error');
}