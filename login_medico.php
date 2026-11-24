<?php
// login_medico.php — Ingreso del médico con su CMP y su contraseña.
require_once "file_conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir("medico_login.html");
}

$cmpm = trim($_POST['cmpm'] ?? '');
$contrasena = (string) ($_POST['contrasena'] ?? '');

if ($cmpm === '' || !preg_match('/^\d{4,10}$/', $cmpm)) {
    redirigir("medico_login.html?error=cmp&cmpm=" . urlencode($cmpm));
}
if ($contrasena === '') {
    redirigir("medico_login.html?error=pass&cmpm=" . urlencode($cmpm));
}

// Máximo 5 intentos fallidos cada 10 minutos por IP y CMP.
$limite = 'med|' . primelab_ip() . '|' . $cmpm;
if (primelab_bloqueado($limite, 5, 600)) {
    redirigir("medico_login.html?error=bloqueado&cmpm=" . urlencode($cmpm));
}

$mysqli = Conectarse();
$stmt = $mysqli->prepare("SELECT m.codm, m.nomm, m.cmpm, m.codesp, m.contrasena
                          FROM medico m
                          WHERE m.cmpm = ? LIMIT 1");
$stmt->bind_param("s", $cmpm);
$stmt->execute();
$medico = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$medico) {
    primelab_registrar_intento($limite, 600);
    $mysqli->close();
    redirigir("medico_login.html?error=noexiste&cmpm=" . urlencode($cmpm));
}

if (!primelab_verificar_clave($medico['contrasena'] ?? '', $contrasena)) {
    primelab_registrar_intento($limite, 600);
    $mysqli->close();
    redirigir("medico_login.html?error=pass&cmpm=" . urlencode($cmpm));
}

primelab_limpiar_intentos($limite);
session_regenerate_id(true);
$_SESSION['medico_codm']   = $medico['codm'];
$_SESSION['medico_nombre'] = $medico['nomm'];
$_SESSION['medico_cmpm']   = $medico['cmpm'];

$mysqli->close();
flash('exito', 'Bienvenido(a), ' . $medico['nomm'] . '.');
redirigir("panel_medico.php");
