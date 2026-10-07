<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/conexion.php';
iniciarSesionSegura();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responder(bool $ok, string $mensaje, int $codigo = 200, array $extra = []): never
{
    http_response_code($codigo);
    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.', 405);
}

// Protección CSRF
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    responder(false, 'El formulario venció. Recargá la página e intentá de nuevo.', 403);
}

// Límite simple de intentos (5 fallos => 60 s de espera)
$_SESSION['intentos'] = $_SESSION['intentos'] ?? 0;
if ($_SESSION['intentos'] >= 5 && time() < ($_SESSION['bloqueo_hasta'] ?? 0)) {
    $resta = $_SESSION['bloqueo_hasta'] - time();
    responder(false, "Demasiados intentos. Esperá {$resta} segundos.", 429);
}

$email    = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    responder(false, 'Ingresá un correo válido y tu contraseña.', 422);
}

try {
    $pdo  = obtenerConexion();
    $stmt = $pdo->prepare(
        'SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.password_hash, u.activo, r.nombre AS rol
         FROM usuario u INNER JOIN rol r ON r.id_rol = u.id_rol
         WHERE u.email = :email LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    // Se verifica siempre un hash para igualar los tiempos de respuesta
    $hash   = $usuario['password_hash'] ?? password_hash('relleno', PASSWORD_DEFAULT);
    $valido = password_verify($password, $hash);

    if (!$usuario || !$valido || !(bool)$usuario['activo']) {
        $_SESSION['intentos']++;
        if ($_SESSION['intentos'] >= 5) {
            $_SESSION['bloqueo_hasta'] = time() + 60;
        }
        responder(false, 'Correo o contraseña incorrectos.', 401);
    }

    if (password_needs_rehash($usuario['password_hash'], PASSWORD_DEFAULT)) {
        $pdo->prepare('UPDATE usuario SET password_hash = :h WHERE id_usuario = :id')
            ->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $usuario['id_usuario']]);
    }

    session_regenerate_id(true);
    $_SESSION = [
        'usuario_id' => (int)$usuario['id_usuario'],
        'nombre'     => htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'),
        'apellido'   => htmlspecialchars($usuario['apellido'], ENT_QUOTES, 'UTF-8'),
        'rol'        => htmlspecialchars($usuario['rol'], ENT_QUOTES, 'UTF-8'),
        'ultimo_uso' => time(),
    ];

    responder(true, '¡Bienvenida, ' . $usuario['nombre'] . '!', 200, ['redirect' => 'dashboard.php']);
} catch (PDOException $e) {
    error_log('[CEN login] ' . $e->getMessage());
    responder(false, 'No pudimos conectar con el servidor. Intentá más tarde.', 500);
}
