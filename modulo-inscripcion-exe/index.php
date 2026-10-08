<?php
session_start();
if (!empty($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · Módulo de Inscripciones</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="login-body">
<main class="login-card">
    <section class="login-side">
        <div class="school-badge">
            <!-- Reemplazá por tu logo: assets/img/logo.png -->
            <span class="badge-mark">CE</span>
            <span>Centro Educativo</span>
        </div>
        <div>
            <h1>Bienvenido al módulo de inscripciones</h1>
            <p>Gestioná el seguimiento de estudiantes y entregas desde un solo lugar.</p>
        </div>
        <div class="side-foot">
            <span class="seal"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 4.5-3.2 8-8 9-4.8-1-8-4.5-8-9V6z"/><path d="M9 12l2 2 4-4"/></svg>Seguridad activa · Sesión cifrada</span>
            <span class="version">v1.0</span>
        </div>
    </section>

    <section class="login-form-wrap">
        <h2>Iniciar sesión</h2>
        <p class="muted">Usá tu correo institucional para entrar.</p>

        <?php if ($error): ?>
            <div class="alert" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form id="loginForm" action="login_process.php" method="post" novalidate>
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

            <label for="email">Correo institucional</label>
            <input type="email" id="email" name="email" placeholder="nombre@escuela.edu.ar" autocomplete="username" required>
            <small class="field-error" id="emailError"></small>

            <label for="password">Contraseña</label>
            <div class="pass-field">
                <input type="password" id="password" name="password" placeholder="Tu contraseña" autocomplete="current-password" required>
                <button type="button" id="togglePass" aria-label="Mostrar contraseña">Mostrar</button>
            </div>
            <small class="field-error" id="passError"></small>

            <a class="forgot" href="#" onclick="alert('Pedí el restablecimiento a Dirección o Secretaría.'); return false;">¿Olvidaste tu contraseña?</a>

            <button type="submit" class="btn-primary" id="submitBtn">Entrar</button>
        </form>
    </section>
</main>

<script>
const form = document.getElementById('loginForm');
const email = document.getElementById('email');
const pass = document.getElementById('password');
const toggle = document.getElementById('togglePass');

toggle.addEventListener('click', () => {
    const visible = pass.type === 'text';
    pass.type = visible ? 'password' : 'text';
    toggle.textContent = visible ? 'Mostrar' : 'Ocultar';
    toggle.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
});

form.addEventListener('submit', (e) => {
    let ok = true;
    const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    document.getElementById('emailError').textContent = '';
    document.getElementById('passError').textContent = '';

    if (!emailRx.test(email.value.trim())) {
        document.getElementById('emailError').textContent = 'Ingresá un correo válido.';
        ok = false;
    }
    if (pass.value === '') {
        document.getElementById('passError').textContent = 'Ingresá tu contraseña.';
        ok = false;
    }
    if (!ok) { e.preventDefault(); return; }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Entrando…';
});
</script>
</body>
</html>
