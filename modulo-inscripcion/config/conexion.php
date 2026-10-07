<?php
/**
 * Conexión PDO a MySQL (XAMPP/MAMP).
 * Ajustá los valores según tu entorno. En MAMP el puerto suele ser 8889 y la clave "root".
 */
const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;           // MAMP: 8889
const DB_NAME = 'moduloIncrip';
const DB_USER = 'root';
const DB_PASS = '';             // MAMP: 'root'

function obtenerConexion(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

/** Inicia la sesión con cookies endurecidas. */
function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}
