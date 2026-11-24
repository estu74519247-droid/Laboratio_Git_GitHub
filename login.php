<?php
// login.php
// Procesa el formulario de acceso del administrador que está en admin_login.html
require_once "file_conexion.php";

if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    redirigir("admin_citas.php");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir("admin_login.html");
}

$usuario  = trim($_POST['usuario'] ?? '');
$correo   = trim($_POST['correo'] ?? '');
$password = (string) ($_POST['password'] ?? '');

if ($usuario === '' || $correo === '' || $password === '') {
    redirigir("admin_login.html?error=campos");
}

// Máximo 5 intentos fallidos cada 10 minutos por IP y usuario.
$limite = 'adm|' . primelab_ip() . '|' . mb_strtolower($usuario, 'UTF-8');
if (primelab_bloqueado($limite, 5, 600)) {
    redirigir("admin_login.html?error=bloqueado");
}

// Los administradores viven en la tabla `administrador` (solo activos).
$mysqli = Conectarse();
$admin  = buscarAdministrador($mysqli, $usuario, $correo);

$claveOk = false;
if ($admin) {
    $claveOk = primelab_verificar_clave($admin['contrasena'] ?? '', $password);
}

if ($claveOk) {
    primelab_limpiar_intentos($limite);
    session_regenerate_id(true);
    $_SESSION['admin_logged'] = true;
    $_SESSION['admin_user']   = $admin['usuario'];
    $_SESSION['admin_email']  = $admin['correo'];
    $_SESSION['admin_nombre'] = $admin['nombre'] ?? $admin['usuario'];

    $mysqli->close();
    flash('exito', 'Bienvenido al panel administrativo, ' . ($admin['nombre'] ?? $admin['usuario']) . '.');
    redirigir("admin_citas.php");
}

primelab_registrar_intento($limite, 600);
$mysqli->close();
redirigir("admin_login.html?error=credenciales");