<?php
declare(strict_types=1);
require_once __DIR__ . '/config/auth.php';
requerirSesion();

/** Vuelve al formulario conservando lo escrito y mostrando los errores. */
function volver(array $errores): never
{
    $_SESSION['form_old']     = array_filter($_POST, 'is_string');
    $_SESSION['form_errores'] = $errores;
    header('Location: formulario.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: formulario.php');
    exit;
}
// Si el envío superó post_max_size, PHP descarta todo $_POST.
if (empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    volver(['El envío es demasiado grande. La foto debe pesar hasta 3 MB.']);
}
$token = (string)($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    volver(['El formulario venció. Intentá de nuevo.']);
}

/* ---------- Helpers de lectura y validación ---------- */
function txt(string $k, int $max): string
{
    $s = preg_replace('/\s+/u', ' ', trim((string)($_POST[$k] ?? ''))) ?? '';
    return mb_substr($s, 0, $max);
}
function nums(string $k): string
{
    return preg_replace('/\D/', '', (string)($_POST[$k] ?? '')) ?? '';
}
function fechaOk(string $s): bool
{
    $d = DateTime::createFromFormat('!Y-m-d', $s);
    return $d !== false && $d->format('Y-m-d') === $s
        && (int)$d->format('Y') >= 1900 && $d <= new DateTime('today');
}
function emailOk(string $s): bool
{
    return $s === '' || filter_var($s, FILTER_VALIDATE_EMAIL) !== false;
}
function dniOk(string $s): bool
{
    return (bool)preg_match('/^\d{7,8}$/', $s);
}
function cuilOk(string $s): bool
{
    return $s === '' || strlen($s) === 11;
}

$errores = [];

/* ---------- Alumno/a ---------- */
$al = [
    'nombre'  => txt('alumno_apellido_nombres', 150),
    'dni'     => nums('alumno_dni'),
    'cuil'    => nums('alumno_cuil'),
    'fnac'    => trim((string)($_POST['alumno_fnac'] ?? '')),
    'lugar'   => txt('alumno_lugar', 120),
    'escuela' => txt('alumno_escuela', 150),
    'obs'     => trim((string)($_POST['alumno_obs'] ?? '')),
];
if ($al['nombre'] === '')       $errores[] = 'Completá el apellido y nombres del alumno/a.';
if (!dniOk($al['dni']))         $errores[] = 'El D.N.I. del alumno/a debe tener 7 u 8 números.';
if (!cuilOk($al['cuil']))       $errores[] = 'El CUIL del alumno/a debe tener 11 números.';
if (!fechaOk($al['fnac']))      $errores[] = 'La fecha de nacimiento del alumno/a no es válida.';

/* ---------- Madre y padre / tutores ---------- */
function leerTutor(string $p, bool $obligatorio, string $etq, array &$errores): ?array
{
    $t = [
        'nombre'     => txt($p . '_nombre', 150),
        'dni'        => nums($p . '_dni'),
        'cuil'       => nums($p . '_cuil'),
        'fnac'       => trim((string)($_POST[$p . '_fnac'] ?? '')),
        'lugar'      => txt($p . '_lugar', 120),
        'asignacion' => (string)($_POST[$p . '_asignacion'] ?? ''),
        'domicilio'  => txt($p . '_domicilio', 200),
        'telefono'   => txt($p . '_telefono', 30),
        'estudios'   => (string)($_POST[$p . '_estudios'] ?? ''),
        'email'      => txt($p . '_email', 150),
    ];
    if (!$obligatorio && $t['nombre'] === '') {
        return null; // el padre/tutor es opcional
    }
    if ($t['nombre'] === '')     $errores[] = "Completá el apellido y nombres de $etq.";
    if (!dniOk($t['dni']))       $errores[] = "El D.N.I. de $etq debe tener 7 u 8 números.";
    if ($t['domicilio'] === '')  $errores[] = "Completá el domicilio de $etq.";
    if (!preg_match('/^[0-9+\-()\s]{6,30}$/', $t['telefono'])) {
        $errores[] = "Ingresá un teléfono válido para $etq (solo números, espacios, + - y paréntesis).";
    }
    if (!cuilOk($t['cuil']))     $errores[] = "El CUIL de $etq debe tener 11 números.";
    if ($t['fnac'] !== '' && !fechaOk($t['fnac'])) {
        $errores[] = "La fecha de nacimiento de $etq no es válida.";
    }
    if (!emailOk($t['email']))   $errores[] = 'Revisá los correos: uno no tiene un formato válido.';
    if (!in_array($t['asignacion'], ['', 'Sí', 'No'], true)) $t['asignacion'] = '';
    if ($t['estudios'] !== '' && !in_array($t['estudios'], ESTUDIOS, true)) $t['estudios'] = '';
    return $t;
}
$madre = leerTutor('madre', true, 'la madre/tutora', $errores);
$padre = leerTutor('padre', false, 'el padre/tutor', $errores);

/* ---------- Acta ---------- */
$ac = [
    'nombre' => txt('acta_nombre', 150),
    'dni'    => nums('acta_dni'),
    'correo' => txt('acta_correo', 150),
    'fecha'  => trim((string)($_POST['acta_fecha'] ?? '')),
    'lugar'  => txt('acta_lugar', 120),
    'imagen' => (string)($_POST['acta_imagen'] ?? ''),
];
if ($ac['nombre'] === '')  $errores[] = 'Completá el nombre del tutor/a que firma el acta.';
if (!dniOk($ac['dni']))    $errores[] = 'El D.N.I. del tutor/a del acta debe tener 7 u 8 números.';
if (!emailOk($ac['correo'])) $errores[] = 'Revisá los correos: uno no tiene un formato válido.';
if ($ac['fecha'] !== '' && !fechaOk($ac['fecha'])) $errores[] = 'La fecha del acta no es válida.';
if (!in_array($ac['imagen'], ['si', 'no'], true)) $errores[] = 'Indicá si autoriza o no el uso de imagen en el acta.';

/* ---------- Foto (opcional) ---------- */
$fotoTmp = null;
$fotoExt = null;
if (!empty($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $f = $_FILES['foto'];
    if (in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || $f['size'] > 3 * 1024 * 1024) {
        $errores[] = 'La foto no puede superar los 3 MB.';
    } elseif ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
        $errores[] = 'No se pudo subir la foto. Probá de nuevo.';
    } else {
        $permitidos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        $info = @getimagesize($f['tmp_name']);   // lee el contenido real, no solo la extensión
        if ($info === false || !isset($permitidos[$info[2]])) {
            $errores[] = 'La foto debe ser JPG, PNG o WEBP.';
        } else {
            $fotoTmp = $f['tmp_name'];
            $fotoExt = $permitidos[$info[2]];
        }
    }
}

try {
    $pdo = obtenerConexion();

    if (dniOk($al['dni'])) {
        $st = $pdo->prepare('SELECT 1 FROM alumno WHERE dni = :d LIMIT 1');
        $st->execute([':d' => $al['dni']]);
        if ($st->fetchColumn()) {
            $errores[] = 'Ya existe un alumno/a registrado con ese D.N.I.';
        }
    }
    if ($errores) {
        volver(array_values(array_unique($errores)));
    }

    // Guardar la foto con nombre aleatorio (nunca el original).
    $fotoRel = null;
    $fotoAbs = null;
    if ($fotoTmp !== null) {
        $dir = __DIR__ . '/uploads/alumnos';
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            volver(['No se pudo preparar la carpeta de fotos.']);
        }
        $archivo = bin2hex(random_bytes(12)) . '.' . $fotoExt;
        $fotoAbs = $dir . '/' . $archivo;
        if (!move_uploaded_file($fotoTmp, $fotoAbs)) {
            volver(['No se pudo guardar la foto. Probá de nuevo.']);
        }
        $fotoRel = 'uploads/alumnos/' . $archivo;
    }

    // Todo o nada: alumno + tutores + acta en una transacción.
    $pdo->beginTransaction();

    $pdo->prepare(
        'INSERT INTO alumno (apellido_nombres, dni, cuil, fecha_nacimiento, lugar_nacimiento, escuela_origen,
                             foto_path, observaciones_salud, id_usuario)
         VALUES (:n, :dni, :cuil, :fn, :lugar, :esc, :foto, :obs, :u)'
    )->execute([
        ':n'     => $al['nombre'],
        ':dni'   => $al['dni'],
        ':cuil'  => $al['cuil'] ?: null,
        ':fn'    => $al['fnac'],
        ':lugar' => $al['lugar'] ?: null,
        ':esc'   => $al['escuela'] ?: null,
        ':foto'  => $fotoRel,
        ':obs'   => $al['obs'] ?: null,
        ':u'     => (int)$_SESSION['usuario_id'],
    ]);
    $idAlumno = (int)$pdo->lastInsertId();

    $insTutor = $pdo->prepare(
        'INSERT INTO tutor (id_alumno, tipo, apellido_nombres, dni, cuil, fecha_nacimiento, lugar_nacimiento,
                            percibe_asignacion, domicilio, telefono, estudios_cursados, email)
         VALUES (:a, :tipo, :n, :dni, :cuil, :fn, :lugar, :asig, :dom, :tel, :est, :mail)'
    );
    foreach (['madre' => $madre, 'padre' => $padre] as $tipo => $t) {
        if ($t === null) {
            continue;
        }
        $insTutor->execute([
            ':a'     => $idAlumno,
            ':tipo'  => $tipo,
            ':n'     => $t['nombre'],
            ':dni'   => $t['dni'],
            ':cuil'  => $t['cuil'] ?: null,
            ':fn'    => $t['fnac'] ?: null,
            ':lugar' => $t['lugar'] ?: null,
            ':asig'  => $t['asignacion'] ?: null,
            ':dom'   => $t['domicilio'],
            ':tel'   => $t['telefono'],
            ':est'   => $t['estudios'] ?: null,
            ':mail'  => $t['email'] ?: null,
        ]);
    }

    $pdo->prepare(
        'INSERT INTO acta_convivencia (id_alumno, tutor_nombre, tutor_dni, correo_contacto, autoriza_imagen, fecha, lugar)
         VALUES (:a, :n, :dni, :c, :img, :f, :l)'
    )->execute([
        ':a'   => $idAlumno,
        ':n'   => $ac['nombre'],
        ':dni' => $ac['dni'],
        ':c'   => $ac['correo'] ?: null,
        ':img' => $ac['imagen'] === 'si' ? 1 : 0,
        ':f'   => $ac['fecha'] ?: null,
        ':l'   => $ac['lugar'] ?: null,
    ]);

    $pdo->commit();
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if (!empty($fotoAbs) && is_file($fotoAbs)) {
        unlink($fotoAbs);   // no dejar fotos huérfanas
    }
    error_log('[CEN inscripción] ' . $e->getMessage());
    $duplicado = (int)($e->errorInfo[1] ?? 0) === 1062;
    volver([$duplicado
        ? 'Ya existe un alumno/a registrado con ese D.N.I.'
        : 'No pudimos guardar la inscripción. No se guardó nada; intentá de nuevo.']);
}

unset($_SESSION['form_old'], $_SESSION['form_errores']);
header('Location: ficha.php?id=' . $idAlumno . '&imprimir=1');
exit;