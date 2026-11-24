<?php
// perfil.php — Perfil del paciente: actualizar datos o eliminar cuenta.
require_once "file_conexion.php";
requerirPaciente();

$mysqli = Conectarse();
$paciente = pacienteActual($mysqli);
if (!$paciente) { $mysqli->close(); redirigir("index.html?msg=sesion"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'actualizar') {
        $telp   = trim($_POST['telp'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        // El seguro NO se puede cambiar desde aquí: define el costo de las citas y
        // debe validarlo el centro de salud.
        $seguro = $paciente['seguro'] ?? 'SIS';

        if ($telp === '') {
            $mysqli->close();
            redirigir("perfil.php?error=campos");
        }
        if (!preg_match('/^\d{9}$/', $telp)) {
            $mysqli->close();
            redirigir("perfil.php?error=tel&telp=" . urlencode($telp) . "&correo=" . urlencode($correo));
        }
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mysqli->close();
            redirigir("perfil.php?error=correo&telp=" . urlencode($telp) . "&correo=" . urlencode($correo));
        }
        $correoDb = ($correo === '') ? null : $correo;
        $stmt = $mysqli->prepare("UPDATE paciente SET telp = ?, correo = ?, seguro = ? WHERE codp = ?");
        $stmt->bind_param("ssss", $telp, $correoDb, $seguro, $paciente['codp']);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
        redirigir("perfil.php?ok=actualiza");
    }

    if ($accion === 'cambiar_clave') {
        $actual = (string) ($_POST['clave_actual'] ?? '');
        $nueva  = (string) ($_POST['clave_nueva'] ?? '');
        $nueva2 = (string) ($_POST['clave_nueva2'] ?? '');

        $stmt = $mysqli->prepare("SELECT contrasena FROM paciente WHERE codp = ? LIMIT 1");
        $stmt->bind_param("s", $paciente['codp']);
        $stmt->execute();
        $guardada = (string) (($stmt->get_result()->fetch_assoc()['contrasena']) ?? '');
        $stmt->close();

        $limite = 'cla|' . primelab_ip() . '|' . $paciente['codp'];
        if (primelab_bloqueado($limite, 5, 600) || !primelab_verificar_clave($guardada, $actual)) {
            primelab_registrar_intento($limite, 600);
            $mysqli->close();
            redirigir("perfil.php?error=clave_mal");
        }
        if (!primelab_clave_valida($nueva)) {
            $mysqli->close();
            redirigir("perfil.php?error=clave_corta");
        }
        if (!hash_equals($nueva, $nueva2)) {
            $mysqli->close();
            redirigir("perfil.php?error=clave_dif");
        }
        primelab_limpiar_intentos($limite);
        $hash = primelab_hash($nueva);
        $stmt = $mysqli->prepare("UPDATE paciente SET contrasena = ? WHERE codp = ?");
        $stmt->bind_param("ss", $hash, $paciente['codp']);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
        redirigir("perfil.php?ok=clave_ok");
    }

    if ($accion === 'eliminar') {
        // Para eliminar la cuenta se exige la contraseña (el DNI no es un secreto).
        $claveConf = (string) ($_POST['clave_confirma'] ?? '');
        if ($claveConf === '') {
            $mysqli->close();
            redirigir("perfil.php?error=confirma");
        }
        $stmt = $mysqli->prepare("SELECT contrasena FROM paciente WHERE codp = ? LIMIT 1");
        $stmt->bind_param("s", $paciente['codp']);
        $stmt->execute();
        $guardada = (string) (($stmt->get_result()->fetch_assoc()['contrasena']) ?? '');
        $stmt->close();
        if (!primelab_verificar_clave($guardada, $claveConf)) {
            $mysqli->close();
            redirigir("perfil.php?error=confirma_dni");
        }

        // Eliminar citas del paciente (historial se elimina por FK cascade desde cita)
        $stmt = $mysqli->prepare("DELETE FROM cita WHERE codp = ?");
        $stmt->bind_param("s", $paciente['codp']);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("DELETE FROM paciente WHERE codp = ?");
        $stmt->bind_param("s", $paciente['codp']);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        redirigir("index.html?msg=eliminado");
    }
}

$mysqli->close();
$nombreCompleto = $paciente['nomp'] . ' ' . $paciente['apep'];
$inicial = mb_strtoupper(mb_substr($paciente['nomp'], 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil — PrimeLab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Source+Sans+3:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body data-page="perfil">
    <header class="topbar">
        <a href="panel.php" class="brand">
            <img src="img/PrimeLab%20Logo.png" alt="PrimeLab" class="brand-logo">
            <span class="brand-copy"><strong>PrimeLab</strong><small>Centros de salud públicos del Perú</small></span>
        </a>
        <div class="topbar-right">
            <div class="section-tag"><span>Sección actual</span><b>MI PERFIL</b></div>
        </div>
    </header>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="user-chip">
                <div class="avatar"><?= e($inicial) ?></div>
                <div><strong><?= e($nombreCompleto) ?></strong><small>DNI <?= e($paciente['dni']) ?></small></div>
            </div>
            <nav>
                <p class="nav-label">Paciente</p>
                <a href="panel.php" class="nav-btn"><span>Inicio</span></a>
                <a href="reservar.php" class="nav-btn"><span>Reservar cita</span></a>
                <a href="mis_citas.php" class="nav-btn"><span>Mis citas</span></a>
                <a href="consultas.php" class="nav-btn"><span>Consultas</span></a>
                <a href="perfil.php" class="nav-btn active"><span>Mi perfil</span></a>
            </nav>
            <div class="account-actions">
                <a href="logout.php" class="nav-btn nav-btn-danger"><span>Cerrar sesión</span></a>
            </div>
        </aside>

        <main class="content">
            <div id="alertBox" class="alert" aria-live="polite"></div>

            <div class="page-heading">
                <p class="eyebrow">Cuenta</p>
                <h1>Mi perfil</h1>
                <p class="muted">Consulta tu información. Para actualizar teléfono, correo o seguro usa “Editar mis
                    datos”. El DNI no se puede modificar.</p>
            </div>

            <section class="panel">
                <h2 class="panel-title">Datos personales</h2>
                <div class="data-grid" style="margin-bottom:18px;">
                    <div><span>DNI</span><strong><?= e($paciente['dni']) ?></strong></div>
                    <div><span>Nombres</span><strong><?= e($paciente['nomp']) ?></strong></div>
                    <div><span>Apellidos</span><strong><?= e($paciente['apep']) ?></strong></div>
                    <div><span>Fecha de
                            nacimiento</span><strong><?= !empty($paciente['fechanac']) ? e(fechaTexto($paciente['fechanac'])) : 'No registrada' ?></strong>
                    </div>
                    <div>
                        <span>Dirección</span><strong><?= !empty($paciente['direccion']) ? e($paciente['direccion']) : 'No registrada' ?></strong>
                    </div>
                    <div>
                        <span>Distrito</span><strong><?= !empty($paciente['distrito']) ? e($paciente['distrito']) : 'No registrado' ?></strong>
                    </div>
                    <div><span>Registrado</span><strong><?= e(fechaTexto($paciente['fechareg'])) ?></strong></div>
                </div>

                <h3 class="list-h" style="margin-top:6px;">Datos de contacto y cobertura</h3>
                <div id="perfilVista">
                    <div class="data-grid" style="margin-bottom:14px;">
                        <div><span>Teléfono</span><strong
                                id="vistaTelp"><?= $paciente['telp'] ? e($paciente['telp']) : 'No registrado' ?></strong>
                        </div>
                        <div><span>Correo electrónico</span><strong
                                id="vistaCorreo"><?= $paciente['correo'] ? e($paciente['correo']) : 'Sin correo registrado' ?></strong>
                        </div>
                        <div><span>Tipo de seguro</span><strong
                                id="vistaSeguro"><?= e(textoSeguro($paciente['seguro'] ?? 'SIS')) ?></strong></div>
                        <div><span>Costo de
                                consulta</span><strong><?= e(costoTexto($paciente['seguro'] ?? 'SIS')) ?></strong></div>
                        <div><span>Contraseña de acceso</span><strong>Tu DNI</strong></div>
                    </div>
                    <button type="button" class="button secondary" id="btnEditarPerfil">Editar mis datos</button>
                </div>

                <form method="POST" action="perfil.php" class="form-stack" id="formEditarPerfil"
                    style="display:none; margin-top:8px;">
                    <input type="hidden" name="accion" value="actualizar">
                    <p class="muted" style="margin:0;">Modifica tu teléfono, correo o tipo de seguro y luego guarda los
                        cambios.</p>
                    <div class="form-grid-2">
                        <label class="field">
                            <span>Teléfono</span>
                            <input type="text" name="telp" maxlength="9" inputmode="numeric" data-numerico="true"
                                data-max="9" data-exacto="9" data-label="Teléfono" value="<?= e($paciente['telp']) ?>"
                                required>
                        </label>
                        <label class="field">
                            <span>Correo electrónico <span class="hint">(opcional)</span></span>
                            <input type="email" name="correo" maxlength="60" value="<?= e($paciente['correo'] ?? '') ?>"
                                placeholder="correo@ejemplo.com">
                        </label>
                    </div>
                    <label class="field">
                        <span>Tipo de seguro</span>
                        <select name="seguro_vista" disabled aria-describedby="ayudaSeguro">
                            <option selected><?= e(textoSeguro($paciente['seguro'] ?? 'SIS')) ?></option>
                        </select>
                        <small class="field-help" id="ayudaSeguro">Para cambiar tu seguro, acércate a tu centro de
                            salud.</small>
                    </label>
                    <div class="ticket-actions">
                        <button type="submit" class="button primary">Guardar cambios</button>
                        <button type="button" class="button secondary" id="btnCancelarPerfil">Cancelar</button>
                    </div>
                </form>
            </section>
            <script>
            (function() {
                var btn = document.getElementById('btnEditarPerfil');
                var cancel = document.getElementById('btnCancelarPerfil');
                var vista = document.getElementById('perfilVista');
                var form = document.getElementById('formEditarPerfil');
                if (!btn || !form || !vista) return;
                btn.addEventListener('click', function() {
                    vista.style.display = 'none';
                    form.style.display = 'grid';
                    var tel = form.querySelector('[name="telp"]');
                    if (tel) tel.focus();
                });
                if (cancel) {
                    cancel.addEventListener('click', function() {
                        form.reset();
                        form.style.display = 'none';
                        vista.style.display = 'block';
                    });
                }
                // Si hay error de validación en URL, abrir edición automáticamente
                var p = new URLSearchParams(location.search);
                if (p.get('error')) {
                    vista.style.display = 'none';
                    form.style.display = 'grid';
                }
            })();
            </script>

            <section class="panel" style="margin-top:18px;">
                <h2 class="panel-title">Cambiar contraseña</h2>
                <form method="POST" action="perfil.php" class="form-stack" style="max-width:420px;">
                    <input type="hidden" name="accion" value="cambiar_clave">
                    <label class="field">
                        <span>Contraseña actual</span>
                        <input type="password" name="clave_actual" maxlength="72" autocomplete="current-password"
                            required>
                    </label>
                    <label class="field">
                        <span>Nueva contraseña <span class="hint">(mínimo 8 caracteres)</span></span>
                        <input type="password" name="clave_nueva" minlength="8" maxlength="72"
                            autocomplete="new-password" required>
                    </label>
                    <label class="field">
                        <span>Repite la nueva contraseña</span>
                        <input type="password" name="clave_nueva2" minlength="8" maxlength="72"
                            autocomplete="new-password" required>
                    </label>
                    <button type="submit" class="button primary">Cambiar contraseña</button>
                </form>
            </section>

            <section class="panel danger-zone" style="margin-top:18px;">
                <h2 class="panel-title">Eliminar cuenta</h2>
                <p class="muted">Esta acción eliminará tu cuenta y todas tus citas asociadas. Para confirmar, ingresa tu
                    contraseña.</p>
                <form method="POST" action="perfil.php" class="form-stack" style="max-width:420px;">
                    <input type="hidden" name="accion" value="eliminar">
                    <label class="field">
                        <span>Confirma tu contraseña</span>
                        <input type="password" name="clave_confirma" maxlength="72" autocomplete="current-password"
                            placeholder="Tu contraseña" required>
                    </label>
                    <button type="submit" class="button danger">Eliminar mi cuenta</button>
                </form>
            </section>
        </main>
    </div>

    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <strong>PrimeLab</strong>
                <p>Sistema de reserva de citas médicas en centros de salud públicos del Perú. Reserva, consulta y haz
                    seguimiento de tus atenciones en un solo lugar.</p>
            </div>
            <div class="footer-col">
                <h4>Accesos</h4>
                <ul>
                    <li><a href="index.html">Pacientes</a></li>
                    <li><a href="registro.html">Crear cuenta</a></li>
                    <li><a href="medico_login.html">Médicos</a></li>
                    <li><a href="admin_login.html">Administración</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Servicios</h4>
                <ul>
                    <li>Reserva de citas</li>
                    <li>Historial de consultas</li>
                    <li>Ticket de cita</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p><span>© 2026 PrimeLab. Proyecto académico — Semana de Innovación 2026.</span><span>Los datos mostrados
                    son de ejemplo.</span></p>
        </div>
    </footer>
    <script src="js/main.js"></script>
</body>

</html>