<?php


define('DB_HOST',    'localhost');
define('DB_NAME',    'hackclub_db');
define('DB_USER',    'root');
define('DB_PASS',    '');           // XAMPP default is empty

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', '/hack/uploads/');

function get_db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
