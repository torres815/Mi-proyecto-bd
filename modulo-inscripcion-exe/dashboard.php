<?php
session_start();
require __DIR__ . '/db.php';

const ROLES_PERMITIDOS = [1, 2]; // 1 = Preceptor/a, 2 = Directivo/a

if (empty($_SESSION['id_usuario']) || !in_array($_SESSION['id_rol'] ?? 0, ROLES_PERMITIDOS, true)) {
    header('Location: logout.php');
    exit;
}

// Datos siempre frescos desde la base
$stmt = $pdo->prepare(
    'SELECT u.nombre, u.apellido, u.email, u.activo, r.nombre AS rol
       FROM usuario u
       JOIN rol r ON r.id_rol = u.id_rol
      WHERE u.id_usuario = :id'
);
$stmt->execute([':id' => $_SESSION['id_usuario']]);
$u = $stmt->fetch();

if (!$u || !$u['activo']) {
    header('Location: logout.php');
    exit;
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$iniciales = mb_strtoupper(mb_substr($u['nombre'], 0, 1) . mb_substr($u['apellido'], 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel · Módulo de Inscripciones</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<header class="banner">
    <h1>Módulo de Inscripciones</h1>
    <span class="chip"><?= e($u['rol']) === 'Preceptor/a' ? 'Preceptoría' : e($u['rol']) ?> · Acceso interno</span>
</header>

<div class="layout">
    <aside class="user-card">
        <div class="avatar" aria-hidden="true"><?= e($iniciales) ?></div>
        <h2><?= e($u['nombre'] . ' ' . $u['apellido']) ?></h2>
        <p class="muted"><?= e($u['rol']) ?></p>
        <nav>
            <a href="dashboard.php" class="active">Inicio</a>
            <a href="#formulario">Formulario</a>
            <a href="logout.php" class="logout">Cerrar sesión</a>
        </nav>
    </aside>

    <main>
        <section class="welcome">
            <h2>Bienvenido/a, <?= e($u['nombre']) ?></h2>
            <p>Tu sesión está activa y el módulo funciona con normalidad.</p>
            <span class="status"><i></i>Sistema operativo</span>
        </section>

        <section class="quick-grid" id="formulario">
            <a class="quick" href="#">
                <span class="q-icon a">N</span>
                <h3>Registro de novedades</h3>
                <p>Cargá inasistencias, avisos y observaciones del día.</p>
            </a>
            <a class="quick" href="#">
                <span class="q-icon b">G</span>
                <h3>Seguimiento grupal</h3>
                <p>Consultá el estado de cada curso y división.</p>
            </a>
            <a class="quick" href="#">
                <span class="q-icon c">E</span>
                <h3>Entregas digitales</h3>
                <p>Revisá la documentación recibida en línea.</p>
            </a>
        </section>
    </main>
</div>
</body>
</html>
