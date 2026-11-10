<?php
declare(strict_types=1);
require_once __DIR__ . '/conexion.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');

const ESTUDIOS = [
    'Sin estudios', 'Primario incompleto', 'Primario completo',
    'Secundario incompleto', 'Secundario completo',
    'Terciario/Universitario incompleto', 'Superior completo',
];

/** Escapa texto para HTML. */
function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/** Exige sesión activa (expira a los 30 min sin actividad). */
function requerirSesion(): void
{
    iniciarSesionSegura();
    if (empty($_SESSION['usuario_id']) || (time() - ($_SESSION['ultimo_uso'] ?? 0)) > 1800) {
        header('Location: auth/logout.php');
        exit;
    }
    $_SESSION['ultimo_uso'] = time();
    header('Cache-Control: no-store, no-cache, must-revalidate');
}

function fmtFecha(?string $s): string
{
    $d = $s ? DateTime::createFromFormat('Y-m-d', $s) : false;
    return $d ? $d->format('d/m/Y') : '—';
}

function fmtDni(?string $s): string
{
    return ($s !== null && ctype_digit($s)) ? number_format((int)$s, 0, ',', '.') : '—';
}

function fmtCuil(?string $s): string
{
    return ($s !== null && strlen($s) === 11 && ctype_digit($s))
        ? substr($s, 0, 2) . '-' . substr($s, 2, 8) . '-' . substr($s, 10) : '—';
}

/** Valor para mostrar: escapado o raya si está vacío. */
function dato(?string $v): string
{
    $v = trim((string)$v);
    return $v === '' ? '—' : e($v);
}