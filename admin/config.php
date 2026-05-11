<?php
session_start();

date_default_timezone_set('Europe/Madrid');

define('DB_HOST', 'localhost');
define('DB_NAME', 'hosting171968eu_casavera_gestion');
define('DB_USER', 'hosting171968eu_s9ujnC49');
define('DB_PASS', 'CasaVera2026!');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );
} catch (PDOException $e) {
    error_log('Error de conexión a BD: ' . $e->getMessage());
    http_response_code(500);
    exit('Error interno del servidor');
}

function requireAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}