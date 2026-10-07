<?php
require_once __DIR__ . '/config/conexion.php';
iniciarSesionSegura();

if (!empty($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$salio = isset($_GET['salida']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar · Centro Educativo Nonogasta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Hanken+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
     <link rel="icon" type="image/png" href="assets/img/LOGOCEN.png">
</head>
<body class="pagina-login">
    <canvas id="particulas" aria-hidden="true"></canvas>

    <main class="login-wrap">
        <section class="tarjeta-vidrio login-card" id="loginCard">
            <header class="login-cabecera">
                <div>
                    <img src="assets/img/cen.png" alt="Logo Cen" style="height: 85px; ">
                </div>
                <h1>Centro Educativo Nonogasta</h1>
                <p>Módulo de Inscripción · Acceso de preceptoría</p>
            </header>

            <form id="formLogin" novalidate autocomplete="on">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="campo">
                    <input type="email" id="email" name="email" placeholder=" " required autocomplete="username">
                    <label for="email">Correo institucional</label>
                    <small class="campo-error" id="errEmail" role="alert"></small>
                </div>

                <div class="campo">
                    <input type="password" id="password" name="password" placeholder=" " required minlength="6" autocomplete="current-password">
                    <label for="password">Contraseña</label>
                    <button type="button" class="ojo" id="togglePass" aria-label="Mostrar contraseña" aria-pressed="false">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>
                            <line class="tachado" x1="3" y1="3" x2="21" y2="21"/>
                        </svg>
                    </button>
                    <small class="campo-error" id="errPass" role="alert"></small>
                </div>

                <button type="submit" class="btn-entrar" id="btnEntrar">
                    <span class="btn-texto">Entrar</span>
                    <span class="btn-spinner" aria-hidden="true"></span>
                </button>
            </form>
        </section>
    </main>

    <div class="toasts" id="toasts" aria-live="polite"></div>

    <script src="assets/js/particles.js"></script>
    <script src="assets/js/main.js"></script>
    <?php if ($salio): ?>
    <script>document.addEventListener('DOMContentLoaded', () => window.CEN.toast('Cerraste sesión correctamente.', 'ok'));</script>
    <?php endif; ?>
</body>
</html>
