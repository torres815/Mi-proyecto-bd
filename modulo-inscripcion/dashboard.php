<?php
require_once __DIR__ . '/config/conexion.php';
iniciarSesionSegura();

// Protección de página: sin sesión => al login. Expira tras 30 min de inactividad.
if (empty($_SESSION['usuario_id']) || (time() - ($_SESSION['ultimo_uso'] ?? 0)) > 1800) {
    header('Location: auth/logout.php');
    exit;
}
$_SESSION['ultimo_uso'] = time();

header('Cache-Control: no-store, no-cache, must-revalidate');

$nombre   = $_SESSION['nombre'];
$rol      = $_SESSION['rol'];
$inicial  = mb_strtoupper(mb_substr(html_entity_decode($nombre), 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Preceptoría · CEN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Hanken+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
         <link rel="icon" type="image/png" href="assets/img/LOGOCEN.png">
</head>
<body class="pagina-panel">
    <canvas id="particulas" aria-hidden="true"></canvas>

    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="perfil">
                <div class="avatar" aria-hidden="true"><?= $inicial ?></div>
                <div>
                    <strong><?= $nombre ?></strong>
                    <span><?= $rol ?> • CEN</span>
                </div>
            </div>

            <nav class="menu" aria-label="Navegación principal">
                <a href="#" class="activo" data-seccion="inicio">Inicio</a>
                <a href="#" data-seccion="novedades">Registro de novedades</a>
                <a href="#" data-seccion="seguimiento">Seguimiento grupal</a>
                <a href="#" data-seccion="entregas">Entregas digitales</a>
                <a href="#" data-seccion="inscripciones">Inscripciones</a>
            </nav>

            <a class="salir" href="auth/logout.php">Cerrar sesión</a>
        </aside>

        <main class="contenido">
            <button class="burger" id="burger" aria-label="Abrir menú">☰</button>

            <section class="banner tarjeta-vidrio">
                <div>
                    <h1>Hola, <?= $nombre ?></h1>
                    <p>Este es tu panel de preceptoría. Registrá novedades, seguí a cada curso y revisá las entregas del día.</p>
                </div>
                <time class="fecha" id="fechaHoy"></time>
            </section>

            <section class="grilla-tarjetas">
                <article class="tarjeta-vidrio tilt" tabindex="0">
                    <div class="icono">📝</div>
                    <h2>Registro de novedades</h2>
                    <p>Anotá inasistencias, llegadas tarde y comunicaciones con las familias.</p>
                    <a href="#" class="enlace-tarjeta" data-aviso="Registro de novedades">Abrir registro</a>
                </article>
                <article class="tarjeta-vidrio tilt" tabindex="0">
                    <div class="icono">👥</div>
                    <h2>Seguimiento grupal</h2>
                    <p>Consultá la asistencia y el estado de cada curso en un solo lugar.</p>
                    <a href="#" class="enlace-tarjeta" data-aviso="Seguimiento grupal">Ver cursos</a>
                </article>
                <article class="tarjeta-vidrio tilt" tabindex="0">
                    <div class="icono">📂</div>
                    <h2>Entregas digitales</h2>
                    <p>Revisá la documentación que enviaron las familias para la inscripción.</p>
                    <a href="#" class="enlace-tarjeta" data-aviso="Entregas digitales">Revisar entregas</a>
                </article>
            </section>
        </main>
    </div>

    <div class="toasts" id="toasts" aria-live="polite"></div>
    <script src="assets/js/particles.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
