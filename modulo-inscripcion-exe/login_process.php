<?php
session_start();
require __DIR__ . '/db.php';

function volver(string $error): void {
    $_SESSION['login_error'] = $error;
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    volver('La sesión del formulario venció. Intentá de nuevo.');
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    volver('Ingresá un correo válido y tu contraseña.');
}

$stmt = $pdo->prepare('SELECT id_usuario, password_hash, id_rol, activo FROM usuario WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    volver('Correo o contraseña incorrectos.');
}

if (!$user['activo']) {
    volver('Tu cuenta está inactiva. Consultá con Dirección.');
}

session_regenerate_id(true);
$_SESSION['id_usuario'] = (int) $user['id_usuario'];
$_SESSION['id_rol']     = (int) $user['id_rol'];
unset($_SESSION['login_error'], $_SESSION['csrf']);

header('Location: dashboard.php');
exit;
