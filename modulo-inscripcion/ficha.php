<?php
require_once __DIR__ . '/config/auth.php';
requerirSesion();

$id  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$pdo = obtenerConexion();

$al = null;
if ($id) {
    $st = $pdo->prepare(
        'SELECT a.*, u.nombre AS u_nombre, u.apellido AS u_apellido
         FROM alumno a INNER JOIN usuario u ON u.id_usuario = a.id_usuario
         WHERE a.id_alumno = :id'
    );
    $st->execute([':id' => $id]);
    $al = $st->fetch();
}

if (!$al) {
    http_response_code(404);
    ?><!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ficha no encontrada · CEN</title><link rel="stylesheet" href="assets/css/ficha.css"></head>
<body class="ficha-vacia"><main><h1>Ficha no encontrada.</h1>
<p><a href="formulario.php">Cargar una inscripción</a> · <a href="dashboard.php">Volver al inicio</a></p></main></body></html>
    <?php
    exit;
}

$st = $pdo->prepare('SELECT * FROM tutor WHERE id_alumno = :id ORDER BY tipo');
$st->execute([':id' => $id]);
$tutores = [];
foreach ($st->fetchAll() as $t) {
    $tutores[$t['tipo']] = $t;
}
$st = $pdo->prepare('SELECT * FROM acta_convivencia WHERE id_alumno = :id');
$st->execute([':id' => $id]);
$acta = $st->fetch() ?: [];

$edad = (new DateTime($al['fecha_nacimiento']))->diff(new DateTime('today'))->y;
$registro = DateTime::createFromFormat('Y-m-d H:i:s', (string)$al['creado_en']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ficha · <?= e($al['apellido_nombres']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Hanken+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/ficha.css">
    <link rel="icon" type="image/png" href="assets/img/LOGOCEN.png">
</head>
<body>
    <div class="barra no-print">
        <button type="button" onclick="window.print()">Imprimir</button>
        <a href="formulario.php">Cargar otra inscripción</a>
        <a href="dashboard.php">Volver al inicio</a>
    </div>

    <main class="hoja">
        <header class="cabecera">
            <img class="logo" src="assets/img/cen.png" alt="Logo del Centro Educativo Nonogasta">
            <div class="titulos">
                <p>Centro Educativo Nonogasta · La Rioja</p>
                <h1>Ficha de inscripción</h1>
                <p>Ficha N.º <?= (int)$al['id_alumno'] ?></p>
            </div>
            <div class="foto">
                <?php if (!empty($al['foto_path'])): ?>
                    <img src="<?= e($al['foto_path']) ?>" alt="Foto del alumno/a">
                <?php else: ?>
                    <span>Foto</span>
                <?php endif; ?>
            </div>
        </header>

        <section>
            <h2>Alumno/a</h2>
            <div class="datos">
                <div class="d w2"><span>Apellido y nombres</span><strong><?= dato($al['apellido_nombres']) ?></strong></div>
                <div class="d"><span>D.N.I.</span><strong><?= fmtDni($al['dni']) ?></strong></div>
                <div class="d"><span>CUIL</span><strong><?= fmtCuil($al['cuil']) ?></strong></div>
                <div class="d"><span>Fecha de nacimiento</span><strong><?= fmtFecha($al['fecha_nacimiento']) ?></strong></div>
                <div class="d"><span>Edad</span><strong><?= $edad ?> años</strong></div>
                <div class="d w2"><span>Lugar de nacimiento</span><strong><?= dato($al['lugar_nacimiento']) ?></strong></div>
                <div class="d w2"><span>Escuela de origen</span><strong><?= dato($al['escuela_origen']) ?></strong></div>
                <div class="d w4"><span>Observaciones de salud o necesidades</span><strong><?= nl2br(dato($al['observaciones_salud'])) ?></strong></div>
            </div>
        </section>

        <?php foreach (['madre' => 'Madre / tutora', 'padre' => 'Padre / tutor'] as $tipo => $titulo): $t = $tutores[$tipo] ?? null; ?>
        <section>
            <h2><?= $titulo ?></h2>
            <?php if ($t): ?>
            <div class="datos">
                <div class="d w2"><span>Apellido y nombres</span><strong><?= dato($t['apellido_nombres']) ?></strong></div>
                <div class="d"><span>D.N.I.</span><strong><?= fmtDni($t['dni']) ?></strong></div>
                <div class="d"><span>CUIL</span><strong><?= fmtCuil($t['cuil']) ?></strong></div>
                <div class="d"><span>Fecha de nacimiento</span><strong><?= fmtFecha($t['fecha_nacimiento']) ?></strong></div>
                <div class="d"><span>Lugar de nacimiento</span><strong><?= dato($t['lugar_nacimiento']) ?></strong></div>
                <div class="d"><span>Percibe asignación</span><strong><?= dato($t['percibe_asignacion']) ?></strong></div>
                <div class="d"><span>Estudios cursados</span><strong><?= dato($t['estudios_cursados']) ?></strong></div>
                <div class="d w2"><span>Domicilio</span><strong><?= dato($t['domicilio']) ?></strong></div>
                <div class="d"><span>Teléfono</span><strong><?= dato($t['telefono']) ?></strong></div>
                <div class="d"><span>Correo electrónico</span><strong><?= dato($t['email']) ?></strong></div>
            </div>
            <?php else: ?>
                <p class="sin-datos">No se cargaron datos.</p>
            <?php endif; ?>
        </section>
        <?php endforeach; ?>

        <section>
            <h2>Acta de aceptación del régimen de convivencia</h2>
            <p class="texto-acta">
                Declaro conocer y aceptar el Régimen de Convivencia del Centro Educativo Nonogasta y me comprometo
                a respetarlo y a acompañar al alumno/a en su cumplimiento.
            </p>
            <div class="datos">
                <div class="d w2"><span>Tutor/a que suscribe</span><strong><?= dato($acta['tutor_nombre'] ?? '') ?></strong></div>
                <div class="d"><span>D.N.I.</span><strong><?= fmtDni($acta['tutor_dni'] ?? null) ?></strong></div>
                <div class="d"><span>Correo de contacto</span><strong><?= dato($acta['correo_contacto'] ?? '') ?></strong></div>
                <div class="d w2"><span>Autorización de uso de imagen</span>
                    <strong><?= !empty($acta['autoriza_imagen']) ? '☒ Autorizo   ☐ No autorizo' : '☐ Autorizo   ☒ No autorizo' ?></strong></div>
                <div class="d"><span>Fecha</span><strong><?= fmtFecha($acta['fecha'] ?? null) ?></strong></div>
                <div class="d"><span>Lugar</span><strong><?= dato($acta['lugar'] ?? '') ?></strong></div>
            </div>
        </section>

        <div class="firmas">
            <div><span>Firma y aclaración del tutor/a</span></div>
            <div><span>Firma de Preceptoría</span></div>
            <div><span>Firma y sello de Dirección</span></div>
        </div>

        <footer class="pie">
            Registrada por <?= e($al['u_nombre'] . ' ' . $al['u_apellido']) ?>
            <?= $registro ? 'el ' . $registro->format('d/m/Y \a \l\a\s H:i') : '' ?>
        </footer>
    </main>

    <?php if (isset($_GET['imprimir'])): ?>
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
    <?php endif; ?>
</body>
</html>