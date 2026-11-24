<?php
// panel.php — Panel principal del paciente.
require_once "file_conexion.php";
requerirPaciente();

$mysqli = Conectarse();
$paciente = pacienteActual($mysqli);
if (!$paciente) { $mysqli->close(); redirigir("index.html?msg=sesion"); }

$codp = $paciente['codp'];

// Próxima cita pendiente
$stmt = $mysqli->prepare("
    SELECT c.codci, c.fechaci, c.horaci, c.estado, c.motivo,
           ce.nomce, ce.dirc, ce.distrito,
           e.nomesp, m.codm, m.nomm
    FROM cita c
    JOIN centro ce ON ce.codce = c.codce
    JOIN medico m ON m.codm = c.codm
    JOIN especialidad e ON e.codesp = m.codesp
    WHERE c.codp = ? AND c.estado = 'pendiente' AND c.fechaci >= CURDATE()
    ORDER BY c.fechaci ASC, c.horaci ASC
    LIMIT 1
");
$stmt->bind_param("s", $codp);
$stmt->execute();
$proxima = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Conteos
function contarCitas($mysqli, $codp, $estado = null) {
    if ($estado) {
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS n FROM cita WHERE codp = ? AND estado = ?");
        $stmt->bind_param("ss", $codp, $estado);
    } else {
        $stmt = $mysqli->prepare("SELECT COUNT(*) AS n FROM cita WHERE codp = ?");
        $stmt->bind_param("s", $codp);
    }
    $stmt->execute();
    $n = (int)$stmt->get_result()->fetch_assoc()['n'];
    $stmt->close();
    return $n;
}
$total = contarCitas($mysqli, $codp);
$pendientes = contarCitas($mysqli, $codp, 'pendiente');
$atendidas = contarCitas($mysqli, $codp, 'atendida');

$mysqli->close();
$nombreCompleto = $paciente['nomp'] . ' ' . $paciente['apep'];
$inicial = mb_strtoupper(mb_substr($paciente['nomp'], 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del paciente — PrimeLab</title>
    <link rel="icon" type="image/png" href="img/PrimeLab%20Logo.png">
    <link rel="apple-touch-icon" href="img/PrimeLab%20Logo.png">
    <meta name="theme-color" content="#14504b">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Source+Sans+3:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header class="topbar">
        <a href="panel.php" class="brand">
            <img src="img/PrimeLab%20Logo.png" alt="PrimeLab" class="brand-logo">
            <span class="brand-copy"><strong>PrimeLab</strong><small>Centros de salud públicos del Perú</small></span>
        </a>
        <div class="topbar-right">
            <div class="section-tag"><span>Sección actual</span><b>INICIO</b></div>
        </div>
    </header>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="user-chip">
                <div class="avatar"><?= e($inicial) ?></div>
                <div>
                    <strong><?= e($nombreCompleto) ?></strong>
                    <small>DNI <?= e($paciente['dni']) ?></small>
                </div>
            </div>
            <nav>
                <p class="nav-label">Paciente</p>
                <a href="panel.php" class="nav-btn active"><span>Inicio</span></a>
                <a href="reservar.php" class="nav-btn"><span>Reservar cita</span></a>
                <a href="mis_citas.php" class="nav-btn"><span>Mis citas</span></a>
                <a href="consultas.php" class="nav-btn"><span>Consultas</span></a>
                <a href="perfil.php" class="nav-btn"><span>Mi perfil</span></a>
            </nav>
            <div class="account-actions">
                <a href="logout.php" class="nav-btn nav-btn-danger"><span>Cerrar sesión</span></a>
            </div>
        </aside>

        <main class="content">
            <?php mostrarFlash(); ?>

            <div class="page-heading">
                <p class="eyebrow">Bienvenido</p>
                <h1>Bienvenido, <?= e(explode(' ', trim($paciente['nomp']))[0]) ?></h1>
                <p class="muted">Gestiona tus reservas y consulta tus citas en centros de salud públicos.</p>
            </div>

            <section class="dashboard-banner">
                <div class="banner-copy">
                    <h2>Reserva tu cita de forma rápida y segura</h2>
                    <p>Elige el centro, la especialidad, el médico y el horario que mejor se adapte a ti.</p>
                    <a href="reservar.php" class="button primary">Reservar nueva cita</a>
                    <a href="consultas.php" class="button secondary">Ver consultas</a>
                </div>
                <div class="banner-visual">
                    <?php if ($proxima): ?>
                    <div class="mini-ticket">
                        <strong>Próxima cita</strong>
                        <small><?= e($proxima['nomesp']) ?></small>
                        <small><?= e(fechaTexto($proxima['fechaci'])) ?> ·
                            <?= e(horaTexto($proxima['horaci'])) ?></small>
                        <small><?= e($proxima['nomce']) ?></small>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="dashboard-overview">
                <article class="summary-card">
                    <span class="summary-label">Citas totales</span>
                    <strong class="summary-count"><?= $total ?></strong>
                    <span class="summary-details">registradas</span>
                </article>
                <article class="summary-card">
                    <span class="summary-label">Pendientes</span>
                    <strong class="summary-count"><?= $pendientes ?></strong>
                    <span class="summary-details">por atender</span>
                </article>
                <article class="summary-card">
                    <span class="summary-label">Atendidas</span>
                    <strong class="summary-count"><?= $atendidas ?></strong>
                    <span class="summary-details">completadas</span>
                </article>
            </section>

            <?php if ($proxima):
                $fProx   = new DateTime($proxima['fechaci']);
                $dias    = max(0, (int) (new DateTime('today'))->diff($fProx)->format('%r%a'));
                $cuando  = $dias === 0 ? 'Hoy' : ($dias === 1 ? 'Mañana' : 'En ' . $dias . ' días');
                $mesesC  = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
                $diasSem = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
            ?>
            <section class="panel prox">
                <div class="panel-head">
                    <h2 class="panel-title">Próxima cita pendiente</h2>
                    <span class="prox-cuando"><?= e($cuando) ?></span>
                </div>
                <div class="prox-body">
                    <div class="prox-fecha" role="img"
                        aria-label="<?= e(fechaTexto($proxima['fechaci']) . ', ' . horaTexto($proxima['horaci'])) ?>">
                        <span class="prox-sem"><?= e($diasSem[(int) $fProx->format('w')]) ?></span>
                        <strong><?= e($fProx->format('j')) ?></strong>
                        <span class="prox-mes"><?= e($mesesC[(int) $fProx->format('n') - 1]) ?>
                            <?= e($fProx->format('Y')) ?></span>
                        <span class="prox-hora"><?= e(horaTexto($proxima['horaci'])) ?></span>
                    </div>

                    <div class="prox-info">
                        <h3><?= e($proxima['nomesp']) ?></h3>
                        <div class="medico-inline prox-medico">
                            <?= fotoMedico($proxima['codm'], $proxima['nomm'], 36) ?><span><?= e($proxima['nomm']) ?></span>
                        </div>
                        <ul class="prox-meta">
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21Z" />
                                    <circle cx="12" cy="9.5" r="2.5" />
                                </svg>
                                <span><?= e($proxima['nomce']) ?><small><?= e(trim($proxima['dirc'] . ', ' . $proxima['distrito'], ', ')) ?></small></span>
                            </li>
                            <?php if (!empty($proxima['motivo'])): ?>
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 3h9l4 4v14H6Z" />
                                    <path d="M14 3v5h5M9 13h7M9 17h5" />
                                </svg>
                                <span>Motivo: <?= e($proxima['motivo']) ?></span>
                            </li>
                            <?php endif; ?>
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 9h16M4 15h16M9 4 7 20M17 4l-2 16" />
                                </svg>
                                <span>Código <span class="code"><?= e($proxima['codci']) ?></span></span>
                            </li>
                        </ul>
                    </div>

                    <div class="prox-acciones">
                        <a href="ticket.php?codigo=<?= e($proxima['codci']) ?>" class="button primary">Ver ticket</a>
                        <a href="mis_citas.php" class="button secondary">Ver todas mis citas</a>
                    </div>
                </div>
            </section>
            <?php else: ?>
            <section class="panel">
                <div class="empty-state">
                    <div class="empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="5" width="18" height="16" rx="2" />
                            <path d="M8 3v4M16 3v4M3 10h18M12 13v5M9.5 15.5h5" />
                        </svg>
                    </div>
                    <h3>No tienes citas pendientes</h3>
                    <p class="muted">Cuando reserves una cita aparecerá aquí con su fecha, médico y centro.</p>
                    <a href="reservar.php" class="button primary">Reservar ahora</a>
                </div>
            </section>
            <?php endif; ?>
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