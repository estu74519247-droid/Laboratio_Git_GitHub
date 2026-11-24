<?php
// login_paciente.php — Ingreso del paciente con DNI y contraseña propia.
require_once "file_conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir("index.html");
}

$dni = trim($_POST['dni'] ?? '');
$contrasena = (string) ($_POST['contrasena'] ?? '');

if (!preg_match('/^\d{8}$/', $dni)) {
    redirigir("index.html?error=dni&dni=" . urlencode($dni));
}
if ($contrasena === '') {
    redirigir("index.html?error=pass&dni=" . urlencode($dni));
}

// Máximo 5 intentos fallidos cada 10 minutos por IP y DNI.
$limite = 'pac|' . primelab_ip() . '|' . $dni;
if (primelab_bloqueado($limite, 5, 600)) {
    redirigir("index.html?error=bloqueado&dni=" . urlencode($dni));
}

$mysqli = Conectarse();
$stmt = $mysqli->prepare("SELECT codp, nomp, apep, dni, contrasena FROM paciente WHERE dni = ? LIMIT 1");
$stmt->bind_param("s", $dni);
$stmt->execute();
$paciente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$paciente) {
    primelab_registrar_intento($limite, 600);
    $mysqli->close();
    redirigir("index.html?error=noexiste&dni=" . urlencode($dni));
}

$rehash = false;
if (!primelab_verificar_clave($paciente['contrasena'] ?? '', $contrasena, $rehash)) {
    primelab_registrar_intento($limite, 600);
    $mysqli->close();
    redirigir("index.html?error=pass&dni=" . urlencode($dni));
}

if ($rehash) {
    $nuevo = primelab_hash($contrasena);
    $stmt = $mysqli->prepare("UPDATE paciente SET contrasena = ? WHERE codp = ?");
    $stmt->bind_param("ss", $nuevo, $paciente['codp']);
    $stmt->execute();
    $stmt->close();
}

primelab_limpiar_intentos($limite);
session_regenerate_id(true);
$_SESSION['paciente_codp']   = $paciente['codp'];
$_SESSION['paciente_nombre'] = $paciente['nomp'] . ' ' . $paciente['apep'];
$_SESSION['paciente_dni']    = $paciente['dni'];

$mysqli->close();
flash('exito', 'Bienvenido, ' . $paciente['nomp'] . ' ' . $paciente['apep'] . '.');
redirigir("panel.php");