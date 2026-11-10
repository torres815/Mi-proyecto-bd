<?php
require_once __DIR__ . '/config/auth.php';
requerirSesion();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Datos y errores de un intento anterior (se muestran una sola vez).
$old     = $_SESSION['form_old'] ?? [];
$errores = $_SESSION['form_errores'] ?? [];
unset($_SESSION['form_old'], $_SESSION['form_errores']);

$nombre  = $_SESSION['nombre'];
$rol     = $_SESSION['rol'];
$inicial = mb_strtoupper(mb_substr(html_entity_decode($nombre), 0, 1));

$v   = static fn(string $k, string $def = ''): string => e((string)($old[$k] ?? $def));
$sel = static fn(string $k, string $val): string => (($old[$k] ?? '') === $val) ? 'selected' : '';
$chk = static fn(string $k, string $val): string => (($old[$k] ?? '') === $val) ? 'checked' : '';

$hoy = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formulario de inscripción · CEN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Hanken+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/formulario.css">
    <link rel="icon" type="image/png" href="assets/img/LOGOCEN.png">
</head>
<body class="pagina-panel pagina-form">
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
                <a href="dashboard.php">Inicio</a>
                <a href="formulario.php" class="activo">Formulario</a>
            </nav>
            <a class="salir" href="auth/logout.php">Cerrar sesión</a>
        </aside>

        <main class="contenido">
            <button class="burger" id="burger" aria-label="Abrir menú">☰</button>

            <section class="banner tarjeta-vidrio">
                <div>
                    <h1>Inscripción de estudiante</h1>
                    <p>Completá los datos del alumno/a, su familia y el acta de convivencia. Los campos con asterisco son obligatorios.</p>
                </div>
            </section>

            <div class="alerta" id="alerta" role="alert" <?= $errores ? '' : 'hidden' ?>>
                <strong>Revisá estos puntos antes de guardar:</strong>
                <ul>
                    <?php foreach ($errores as $msg): ?><li><?= e((string)$msg) ?></li><?php endforeach; ?>
                    <?php if ($errores): ?><li>Si habías elegido una foto, volvé a adjuntarla.</li><?php endif; ?>
                </ul>
            </div>

            <form id="formInscripcion" method="post" action="guardar_formulario.php" enctype="multipart/form-data" novalidate autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <!-- ALUMNO/A -->
                <section class="tarjeta-vidrio seccion-form">
                    <header><h2>Datos del alumno/a</h2></header>
                    <div class="rejilla">
                        <div class="f c8">
                            <label for="alumno_apellido_nombres" class="req">Apellido y nombres</label>
                            <input type="text" id="alumno_apellido_nombres" name="alumno_apellido_nombres" maxlength="150" value="<?= $v('alumno_apellido_nombres') ?>">
                        </div>
                        <div class="f c4">
                            <label for="alumno_dni" class="req">D.N.I.</label>
                            <input type="text" id="alumno_dni" name="alumno_dni" inputmode="numeric" maxlength="8" placeholder="Solo números" data-num value="<?= $v('alumno_dni') ?>">
                        </div>
                        <div class="f c4">
                            <label for="alumno_cuil">CUIL</label>
                            <input type="text" id="alumno_cuil" name="alumno_cuil" inputmode="numeric" maxlength="13" placeholder="20-12345678-9" data-cuil value="<?= $v('alumno_cuil') ?>">
                        </div>
                        <div class="f c4">
                            <label for="alumno_fnac" class="req">Fecha de nacimiento</label>
                            <input type="date" id="alumno_fnac" name="alumno_fnac" max="<?= $hoy ?>" value="<?= $v('alumno_fnac') ?>">
                        </div>
                        <div class="f c4">
                            <label for="alumnoEdad">Edad</label>
                            <output id="alumnoEdad" class="edad" for="alumno_fnac">—</output>
                        </div>
                        <div class="f c6">
                            <label for="alumno_lugar">Lugar de nacimiento</label>
                            <input type="text" id="alumno_lugar" name="alumno_lugar" maxlength="120" value="<?= $v('alumno_lugar') ?>">
                        </div>
                        <div class="f c6">
                            <label for="alumno_escuela">Escuela de origen</label>
                            <input type="text" id="alumno_escuela" name="alumno_escuela" maxlength="150" value="<?= $v('alumno_escuela') ?>">
                        </div>

                        <div class="f c12" id="grupoFoto">
                            <label for="foto">Foto del alumno/a</label>
                            <div class="foto-bloque">
                                <div class="foto-previa" id="fotoPrevia">Sin foto</div>
                                <div class="foto-acciones">
                                    <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" hidden>
                                    <button type="button" class="btn-sec" id="btnArchivo">Subir archivo</button>
                                    <button type="button" class="btn-sec" id="btnCamara">Usar cámara</button>
                                    <button type="button" class="btn-sec" id="btnQuitarFoto">Quitar</button>
                                    <small>JPG, PNG o WEBP, hasta 3 MB.</small>
                                </div>
                            </div>
                        </div>

                        <div class="f c12">
                            <label for="alumno_obs">Observaciones de salud o necesidades</label>
                            <textarea id="alumno_obs" name="alumno_obs" rows="3" placeholder="Opcional"><?= $v('alumno_obs') ?></textarea>
                        </div>
                    </div>
                </section>

                <!-- MADRE Y PADRE / TUTORES -->
                <?php foreach ([
                    'madre' => ['Datos de la madre / tutora', 'Obligatorio', true],
                    'padre' => ['Datos del padre / tutor', 'Opcional. Si cargás el nombre, completá también D.N.I., domicilio y teléfono.', false],
                ] as $p => [$titulo, $nota, $oblig]): $rq = $oblig ? 'req' : ''; ?>
                <section class="tarjeta-vidrio seccion-form">
                    <header><h2><?= $titulo ?></h2><small><?= $nota ?></small></header>
                    <div class="rejilla">
                        <div class="f c8">
                            <label for="<?= $p ?>_nombre" class="<?= $rq ?>">Apellido y nombres</label>
                            <input type="text" id="<?= $p ?>_nombre" name="<?= $p ?>_nombre" maxlength="150" value="<?= $v($p . '_nombre') ?>">
                        </div>
                        <div class="f c4">
                            <label for="<?= $p ?>_dni" class="<?= $rq ?>">D.N.I.</label>
                            <input type="text" id="<?= $p ?>_dni" name="<?= $p ?>_dni" inputmode="numeric" maxlength="8" placeholder="Solo números" data-num value="<?= $v($p . '_dni') ?>">
                        </div>
                        <div class="f c4">
                            <label for="<?= $p ?>_cuil">CUIL</label>
                            <input type="text" id="<?= $p ?>_cuil" name="<?= $p ?>_cuil" inputmode="numeric" maxlength="13" placeholder="27-12345678-3" data-cuil value="<?= $v($p . '_cuil') ?>">
                        </div>
                        <div class="f c4">
                            <label for="<?= $p ?>_fnac">Fecha de nacimiento</label>
                            <input type="date" id="<?= $p ?>_fnac" name="<?= $p ?>_fnac" max="<?= $hoy ?>" value="<?= $v($p . '_fnac') ?>">
                        </div>
                        <div class="f c4">
                            <label for="<?= $p ?>_lugar">Lugar de nacimiento</label>
                            <input type="text" id="<?= $p ?>_lugar" name="<?= $p ?>_lugar" maxlength="120" value="<?= $v($p . '_lugar') ?>">
                        </div>
                        <div class="f c4">
                            <label for="<?= $p ?>_asignacion">¿Percibe asignación?</label>
                            <select id="<?= $p ?>_asignacion" name="<?= $p ?>_asignacion">
                                <option value=""></option>
                                <option value="Sí" <?= $sel($p . '_asignacion', 'Sí') ?>>Sí</option>
                                <option value="No" <?= $sel($p . '_asignacion', 'No') ?>>No</option>
                            </select>
                        </div>
                        <div class="f c8">
                            <label for="<?= $p ?>_domicilio" class="<?= $rq ?>">Domicilio</label>
                            <input type="text" id="<?= $p ?>_domicilio" name="<?= $p ?>_domicilio" maxlength="200" value="<?= $v($p . '_domicilio') ?>">
                        </div>
                        <div class="f c4">
                            <label for="<?= $p ?>_telefono" class="<?= $rq ?>">Teléfono</label>
                            <input type="tel" id="<?= $p ?>_telefono" name="<?= $p ?>_telefono" maxlength="30" value="<?= $v($p . '_telefono') ?>">
                        </div>
                        <div class="f c6">
                            <label for="<?= $p ?>_estudios">Estudios cursados</label>
                            <select id="<?= $p ?>_estudios" name="<?= $p ?>_estudios">
                                <option value=""></option>
                                <?php foreach (ESTUDIOS as $op): ?>
                                    <option value="<?= e($op) ?>" <?= $sel($p . '_estudios', $op) ?>><?= e($op) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="f c6">
                            <label for="<?= $p ?>_email">Correo electrónico</label>
                            <input type="email" id="<?= $p ?>_email" name="<?= $p ?>_email" maxlength="150" value="<?= $v($p . '_email') ?>">
                        </div>
                    </div>
                </section>
                <?php endforeach; ?>

                <!-- ACTA -->
                <section class="tarjeta-vidrio seccion-form">
                    <header><h2>Acta de aceptación del régimen de convivencia</h2></header>
                    <p class="texto-acta">
                        Declaro conocer y aceptar el Régimen de Convivencia del Centro Educativo Nonogasta y me comprometo
                        a respetarlo y a acompañar al alumno/a en su cumplimiento. La ficha impresa se firma en Preceptoría.
                    </p>
                    <div class="rejilla">
                        <div class="f c8">
                            <label for="acta_nombre" class="req">Apellido y nombres del tutor/a</label>
                            <input type="text" id="acta_nombre" name="acta_nombre" maxlength="150" value="<?= $v('acta_nombre') ?>">
                        </div>
                        <div class="f c4">
                            <label for="acta_dni" class="req">D.N.I. del tutor/a</label>
                            <input type="text" id="acta_dni" name="acta_dni" inputmode="numeric" maxlength="8" data-num value="<?= $v('acta_dni') ?>">
                        </div>
                        <div class="f c4">
                            <label for="acta_correo">Correo de contacto</label>
                            <input type="email" id="acta_correo" name="acta_correo" maxlength="150" value="<?= $v('acta_correo') ?>">
                        </div>
                        <div class="f c4">
                            <label for="acta_fecha">Fecha</label>
                            <input type="date" id="acta_fecha" name="acta_fecha" data-defecto="<?= $hoy ?>" value="<?= $v('acta_fecha', $hoy) ?>">
                        </div>
                        <div class="f c4">
                            <label for="acta_lugar">Lugar</label>
                            <input type="text" id="acta_lugar" name="acta_lugar" maxlength="120" data-defecto="Nonogasta, La Rioja" value="<?= $v('acta_lugar', 'Nonogasta, La Rioja') ?>">
                        </div>
                        <div class="f c12" id="grupoImagen">
                            <span class="etiqueta req">Autorización de uso de imagen</span>
                            <p class="texto-acta">¿Autoriza al Centro Educativo Nonogasta a usar fotos y videos del alumno/a en actividades y comunicaciones institucionales?</p>
                            <div class="opciones">
                                <label class="opcion"><input type="radio" name="acta_imagen" value="si" <?= $chk('acta_imagen', 'si') ?>> Autorizo</label>
                                <label class="opcion"><input type="radio" name="acta_imagen" value="no" <?= $chk('acta_imagen', 'no') ?>> No autorizo</label>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="acciones">
                    <button type="button" class="btn-sec" id="btnLimpiar">Limpiar</button>
                    <button type="submit" class="btn-primario" id="btnGuardar">Generar ficha e imprimir</button>
                </div>
            </form>
        </main>
    </div>

    <dialog class="camara" id="dlgCamara" aria-label="Tomar foto con la cámara">
        <video id="video" autoplay playsinline muted></video>
        <div class="camara-acciones">
            <button type="button" class="btn-sec" id="btnCancelarCamara">Cancelar</button>
            <button type="button" class="btn-primario" id="btnTomar">Tomar foto</button>
        </div>
    </dialog>

    <div class="toasts" id="toasts" aria-live="polite"></div>
    <script src="assets/js/particles.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/formulario.js"></script>
</body>
</html>