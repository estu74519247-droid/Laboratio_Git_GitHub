<?php
// file_conexion.php — Conexión y utilidades comunes (estilo ByteCore/procedural)

require_once __DIR__ . '/config.php';

// Estilo procedural: las funciones mysqli devuelven false en lugar de lanzar
// excepciones, lo que permite crear la base de datos o mostrar un mensaje claro.
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

// Sesión con cookie protegida (HttpOnly + SameSite). Los scripts no deben llamar
// a session_start() antes de incluir este archivo.
if (session_status() === PHP_SESSION_NONE && !defined('PRIMELAB_SIN_SESION')) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ------------------------------------------------------------------------
 * Contraseñas
 * ---------------------------------------------------------------------- */

/** Largo mínimo / máximo de una contraseña (bcrypt solo usa los primeros 72 bytes). */
function primelab_clave_valida($clave)
{
    $n = strlen((string) $clave);
    return $n >= 8 && $n <= 72;
}

/** Indica si el texto ya es un hash de contraseña. */
function primelab_es_hash($texto)
{
    $texto = (string) $texto;
    return strpos($texto, '$2y$') === 0 || strpos($texto, '$argon2') === 0;
}

/** Genera el hash bcrypt (60 caracteres, cabe en VARCHAR(60)). */
function primelab_hash($clave)
{
    return password_hash((string) $clave, PASSWORD_BCRYPT);
}

/**
 * Verifica una contraseña. Acepta hash y, solo por compatibilidad con bases
 * antiguas, texto plano (en ese caso $rehash = true para que se guarde el hash).
 */
function primelab_verificar_clave($guardada, $ingresada, &$rehash = false)
{
    $rehash = false;
    $guardada = (string) $guardada;
    $ingresada = (string) $ingresada;
    if ($guardada === '') return false;
    if (primelab_es_hash($guardada)) {
        return password_verify($ingresada, $guardada);
    }
    if (hash_equals($guardada, $ingresada)) {
        $rehash = true;
        return true;
    }
    return false;
}

/* ------------------------------------------------------------------------
 * Límite de intentos (anti fuerza bruta) — archivos en la carpeta temporal
 * ---------------------------------------------------------------------- */

function primelab_ip()
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

function primelab_archivo_intentos($clave)
{
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'primelab_rl';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    return $dir . DIRECTORY_SEPARATOR . md5((string) $clave) . '.json';
}

function primelab_intentos($clave, $ventana)
{
    $archivo = primelab_archivo_intentos($clave);
    $lista = is_file($archivo) ? json_decode((string) @file_get_contents($archivo), true) : [];
    if (!is_array($lista)) $lista = [];
    $limite = time() - (int) $ventana;
    return array_values(array_filter($lista, function ($t) use ($limite) { return (int) $t > $limite; }));
}

/** true si ya se superó el máximo de intentos en la ventana (segundos). */
function primelab_bloqueado($clave, $max, $ventana)
{
    return count(primelab_intentos($clave, $ventana)) >= (int) $max;
}

function primelab_registrar_intento($clave, $ventana)
{
    $lista = primelab_intentos($clave, $ventana);
    $lista[] = time();
    @file_put_contents(primelab_archivo_intentos($clave), json_encode($lista), LOCK_EX);
}

function primelab_limpiar_intentos($clave)
{
    @unlink(primelab_archivo_intentos($clave));
}

/* ------------------------------------------------------------------------
 * CSRF: todo formulario POST de una sesión iniciada lleva un token oculto
 * (se inserta automáticamente) y se valida antes de procesar la petición.
 * ---------------------------------------------------------------------- */

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function primelab_hay_usuario()
{
    return !empty($_SESSION['paciente_codp']) || !empty($_SESSION['medico_codm']) || !empty($_SESSION['admin_logged']);
}

if (!defined('PRIMELAB_SIN_SESION') && session_status() === PHP_SESSION_ACTIVE && primelab_hay_usuario()) {
    $primelab_script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $primelab_exentos = ['login.php', 'login_paciente.php', 'login_medico.php', 'registro.php'];
    if (!in_array($primelab_script, $primelab_exentos, true)) {
        $primelab_token = csrf_token();
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $enviado = (string) ($_POST['csrf_token'] ?? '');
            if ($enviado === '' || !hash_equals($primelab_token, $enviado)) {
                http_response_code(403);
                die('Solicitud no válida (token de seguridad vencido). Vuelve atrás, recarga la página e inténtalo de nuevo.');
            }
        }
        ob_start(function ($html) use ($primelab_token) {
            return preg_replace_callback(
                '/<form\b[^>]*\bmethod\s*=\s*["\']?post["\']?[^>]*>/i',
                function ($m) use ($primelab_token) {
                    return $m[0] . '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($primelab_token, ENT_QUOTES, 'UTF-8') . '">';
                },
                $html
            );
        });
    }
}

/** Horarios de atención disponibles para reservar (bloques fijos). */
function primelab_horarios()
{
    return ['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','14:00','14:30','15:00','15:30','16:00','16:30'];
}

/** Estados en los que una cita NO ocupa el horario del médico. */
function primelab_sql_estados_libres()
{
    return "('cancelada','reprogramada')";
}

/** Ejecuta una consulta ignorando errores (para migraciones automáticas). */
function primelab_query($mysqli, $sql)
{
    try {
        return $mysqli->query($sql);
    } catch (\Throwable $e) {
        return false;
    }
}

/** Indica si una tabla tiene una columna determinada. */
function primelab_tiene_columna($mysqli, $tabla, $columna)
{
    $tabla = preg_replace('/[^A-Za-z0-9_]/', '', $tabla);
    $res = primelab_query($mysqli, "SHOW COLUMNS FROM `$tabla` LIKE '" . $mysqli->real_escape_string($columna) . "'");
    return ($res && $res->num_rows > 0);
}

/**
 * Abre la conexión con MySQL. Si la base de datos todavía no existe la crea,
 * de modo que el sistema arranca en cualquier instalación de XAMPP.
 */
function primelab_abrir_conexion($detener = true)
{
    if (!function_exists('mysqli_init')) {
        if ($detener) die("La extensión mysqli de PHP no está habilitada. Actívala en XAMPP.");
        return null;
    }

    $mysqli = mysqli_init();
    if (!$mysqli) {
        if ($detener) die("No se pudo inicializar la conexión a MySQL.");
        return null;
    }
    @$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 4);
    @$mysqli->real_connect(
        PRIMELAB_DB_HOST,
        PRIMELAB_DB_USER,
        PRIMELAB_DB_PASS,
        PRIMELAB_DB_NAME,
        (int) PRIMELAB_DB_PORT
    );

    // Error 1049 = base de datos inexistente: la creamos automáticamente.
    if ($mysqli->connect_errno === 1049) {
        $creador = @mysqli_init();
        if ($creador) {
            @$creador->options(MYSQLI_OPT_CONNECT_TIMEOUT, 4);
            @$creador->real_connect(PRIMELAB_DB_HOST, PRIMELAB_DB_USER, PRIMELAB_DB_PASS, '', (int) PRIMELAB_DB_PORT);
            if (!$creador->connect_errno) {
                @$creador->query("CREATE DATABASE IF NOT EXISTS `" . PRIMELAB_DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci");
            }
            @$creador->close();
            @$mysqli->close();
            $mysqli = mysqli_init();
            @$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 4);
            @$mysqli->real_connect(PRIMELAB_DB_HOST, PRIMELAB_DB_USER, PRIMELAB_DB_PASS, PRIMELAB_DB_NAME, (int) PRIMELAB_DB_PORT);
        }
    }

    if ($mysqli->connect_errno) {
        if ($detener) {
            die("Error de conexión a MySQL (" . PRIMELAB_DB_HOST . ":" . PRIMELAB_DB_PORT . "): " . $mysqli->connect_error
                . "<br>Verifica que MySQL esté iniciado en XAMPP y que el puerto sea el correcto.");
        }
        return null;
    }

    $mysqli->set_charset("utf8mb4");
    return $mysqli;
}

/**
 * Abre una conexión con MySQL usando la configuración de config.php.
 * Devuelve null si el servidor no está disponible (sin detener la página).
 */
function ConectarseOpcional()
{
    $mysqli = primelab_abrir_conexion(false);
    if (!$mysqli) return null;
    asegurarEsquemaPaciente($mysqli);
    asegurarEsquemaCita($mysqli);
    return $mysqli;
}

function Conectarse()
{
    $mysqli = primelab_abrir_conexion(true);
    asegurarBaseInicial($mysqli);
    asegurarEsquemaPaciente($mysqli);
    asegurarEsquemaMedico($mysqli);
    asegurarEsquemaCita($mysqli);
    asegurarAdministradores($mysqli);
    return $mysqli;
}

/** Contraseña inicial de los médicos de demostración (cámbiala desde el panel). */
const PRIMELAB_CLAVE_MEDICO_INICIAL = 'Medico2026!';

/**
 * Agrega la columna `contrasena` a `medico` y, a los que no la tengan,
 * les asigna el hash de la contraseña inicial.
 */
function asegurarEsquemaMedico($mysqli)
{
    static $verificado = false;
    if ($verificado) return;
    $verificado = true;

    if (!primelab_tiene_columna($mysqli, 'medico', 'contrasena')) {
        primelab_query($mysqli, "ALTER TABLE medico ADD COLUMN contrasena VARCHAR(60) DEFAULT NULL");
    }
    $sin = primelab_query($mysqli, "SELECT 1 FROM medico WHERE contrasena IS NULL OR contrasena = '' LIMIT 1");
    if ($sin && $sin->num_rows > 0) {
        $hash = primelab_hash(PRIMELAB_CLAVE_MEDICO_INICIAL);
        $stmt = @$mysqli->prepare("UPDATE medico SET contrasena = ? WHERE contrasena IS NULL OR contrasena = ''");
        if ($stmt) {
            $stmt->bind_param("s", $hash);
            @$stmt->execute();
            $stmt->close();
        }
    }
}

/**
 * Si la base de datos está vacía, importa el script BD/bd_primelab.sql.
 * Así el sistema queda operativo en cualquier XAMPP recién instalado.
 */
function asegurarBaseInicial($mysqli)
{
    static $verificado = false;
    if ($verificado) return;
    $verificado = true;

    $res = primelab_query($mysqli, "SHOW TABLES LIKE 'paciente'");
    if ($res && $res->num_rows > 0) return;

    $archivo = __DIR__ . '/BD/bd_primelab.sql';
    if (!is_file($archivo)) return;
    $sql = @file_get_contents($archivo);
    if (!is_string($sql) || trim($sql) === '') return;

    // Se elimina CREATE DATABASE / USE para importar sobre la base ya conectada.
    $sql = preg_replace('/^\s*CREATE DATABASE[^;]*;/mi', '', $sql);
    $sql = preg_replace('/^\s*USE\s+`[^`]+`\s*;/mi', '', $sql);

    if (@$mysqli->multi_query($sql)) {
        do {
            if ($res = @$mysqli->store_result()) {
                $res->free();
            }
        } while (@$mysqli->more_results() && @$mysqli->next_result());
    }
}

/**
 * Ajustes idempotentes para bases de datos creadas con la versión anterior.
 * - Agrega la columna `contrasena` si no existe.
 * - Permite teléfono nulo para cuentas importadas desde la API.
 * - Asigna como contraseña inicial el DNI de cada paciente.
 */
function asegurarEsquemaPaciente($mysqli)
{
    static $verificado = false;
    if ($verificado) return;
    $verificado = true;

    $agregada = false;
    if (!primelab_tiene_columna($mysqli, 'paciente', 'contrasena')) {
        if (primelab_query($mysqli, "ALTER TABLE paciente ADD COLUMN contrasena VARCHAR(60) DEFAULT NULL AFTER correo")) {
            $agregada = true;
        }
    }

    $colTel = primelab_query($mysqli, "SHOW COLUMNS FROM paciente LIKE 'telp'");
    if ($colTel && ($infoTel = $colTel->fetch_assoc()) && strtoupper($infoTel['Null']) === 'NO') {
        primelab_query($mysqli, "ALTER TABLE paciente MODIFY telp CHAR(9) NULL DEFAULT NULL");
    }

    // Cuentas sin contraseña (bases antiguas): contraseña inicial = DNI (se guarda como hash abajo).
    $sinClave = primelab_query($mysqli, "SELECT 1 FROM paciente WHERE contrasena IS NULL OR contrasena = '' LIMIT 1");
    if ($sinClave && $sinClave->num_rows > 0) {
        primelab_query($mysqli, "UPDATE paciente SET contrasena = dni WHERE contrasena IS NULL OR contrasena = ''");
    }

    // Migración: toda contraseña guardada en texto plano pasa a hash bcrypt.
    $planas = primelab_query($mysqli, 'SELECT codp, contrasena FROM paciente WHERE contrasena NOT LIKE \'$2y$%\' LIMIT 200');
    if ($planas && $planas->num_rows > 0) {
        $filas = [];
        while ($fila = $planas->fetch_assoc()) $filas[] = $fila;
        $stmt = @$mysqli->prepare("UPDATE paciente SET contrasena = ? WHERE codp = ?");
        if ($stmt) {
            foreach ($filas as $fila) {
                $hash = primelab_hash($fila['contrasena']);
                $stmt->bind_param("ss", $hash, $fila['codp']);
                @$stmt->execute();
            }
            $stmt->close();
        }
    }
}

/**
 * Auto-reparación del esquema de `cita` y `cita_historial`.
 * Evita el error fatal "Unknown column 'c.diagnostico'" cuando la base de
 * datos fue importada con una versión anterior del sistema.
 */
function asegurarEsquemaCita($mysqli)
{
    static $verificado = false;
    if ($verificado) return;
    $verificado = true;

    if (!primelab_tiene_columna($mysqli, 'cita', 'diagnostico')) {
        primelab_query($mysqli, "ALTER TABLE cita ADD COLUMN diagnostico TEXT DEFAULT NULL");
    }
    if (!primelab_tiene_columna($mysqli, 'cita', 'indicaciones')) {
        primelab_query($mysqli, "ALTER TABLE cita ADD COLUMN indicaciones TEXT DEFAULT NULL");
    }
    if (!primelab_tiene_columna($mysqli, 'cita', 'motivo_reprogramacion')) {
        primelab_query($mysqli, "ALTER TABLE cita ADD COLUMN motivo_reprogramacion VARCHAR(255) DEFAULT NULL");
    }
    if (!primelab_tiene_columna($mysqli, 'cita', 'reprogramada_de')) {
        primelab_query($mysqli, "ALTER TABLE cita ADD COLUMN reprogramada_de CHAR(6) DEFAULT NULL");
    }
    if (!primelab_tiene_columna($mysqli, 'cita', 'observaciones')) {
        primelab_query($mysqli, "ALTER TABLE cita ADD COLUMN observaciones VARCHAR(255) DEFAULT NULL");
    }
    // El estado ahora admite 'extendida' y 'reprogramada'.
    primelab_query($mysqli, "ALTER TABLE cita MODIFY estado VARCHAR(20) NOT NULL DEFAULT 'pendiente'");

    if (!primelab_tiene_columna($mysqli, 'cita_historial', 'usuario_accion')) {
        primelab_query($mysqli, "ALTER TABLE cita_historial ADD COLUMN usuario_accion VARCHAR(40) DEFAULT 'Sistema'");
    }
}

/**
 * Crea la tabla `administrador` y siembra tres cuentas de acceso si está vacía.
 * El administrador principal sigue siendo `prime`.
 */
function asegurarAdministradores($mysqli)
{
    static $verificado = false;
    if ($verificado) return;
    $verificado = true;

    $ok = primelab_query($mysqli, "
        CREATE TABLE IF NOT EXISTS `administrador` (
          `idadm` int(11) NOT NULL AUTO_INCREMENT,
          `usuario` varchar(30) NOT NULL,
          `correo` varchar(80) NOT NULL,
          `contrasena` varchar(60) NOT NULL,
          `nombre` varchar(80) DEFAULT NULL,
          `activo` tinyint(1) NOT NULL DEFAULT 1,
          PRIMARY KEY (`idadm`),
          UNIQUE KEY `uq_admin_usuario` (`usuario`),
          UNIQUE KEY `uq_admin_correo` (`correo`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
    ");
    if (!$ok) return;

    $res = primelab_query($mysqli, "SELECT COUNT(*) AS n FROM administrador");
    $n = $res ? (int) $res->fetch_assoc()['n'] : 0;

    if ($n === 0) {
        foreach (administradoresDefecto() as $a) {
            $hash = primelab_hash($a['contrasena']);
            $stmt = @$mysqli->prepare("INSERT INTO administrador (usuario, correo, contrasena, nombre, activo) VALUES (?, ?, ?, ?, 1)");
            if (!$stmt) continue;
            $stmt->bind_param("ssss", $a['usuario'], $a['correo'], $hash, $a['nombre']);
            @$stmt->execute();
            $stmt->close();
        }
        return;
    }

    // Migración: contraseñas de administrador en texto plano -> hash bcrypt.
    $planas = primelab_query($mysqli, 'SELECT idadm, contrasena FROM administrador WHERE contrasena NOT LIKE \'$2y$%\'');
    if ($planas && $planas->num_rows > 0) {
        $filas = [];
        while ($fila = $planas->fetch_assoc()) $filas[] = $fila;
        $stmt = @$mysqli->prepare("UPDATE administrador SET contrasena = ? WHERE idadm = ?");
        if ($stmt) {
            foreach ($filas as $fila) {
                $hash = primelab_hash($fila['contrasena']);
                $stmt->bind_param("si", $hash, $fila['idadm']);
                @$stmt->execute();
            }
            $stmt->close();
        }
    }
}

/** Administradores de ejemplo que se siembran SOLO si la tabla está vacía (se guardan con hash). */
function administradoresDefecto()
{
    return [
        ['usuario' => 'prime',  'correo' => 'primelab@centrosalud.pe', 'contrasena' => 'pr1m3l4b', 'nombre' => 'Administrador principal'],
        ['usuario' => 'admin2', 'correo' => 'gestion@centrosalud.pe',  'contrasena' => 'PrimeLab2026', 'nombre' => 'Coordinación de citas'],
        ['usuario' => 'admin3', 'correo' => 'soporte@centrosalud.pe',  'contrasena' => 'PrimeLab2026', 'nombre' => 'Soporte técnico'],
    ];
}

/** Devuelve los administradores activos desde la base de datos. */
function administradoresActivos($mysqli)
{
    $lista = [];
    $res = primelab_query($mysqli, "SELECT usuario, correo, contrasena, nombre FROM administrador WHERE activo = 1");
    if ($res) {
        while ($fila = $res->fetch_assoc()) {
            $lista[] = $fila;
        }
    }
    return $lista;
}

/** Busca un administrador por usuario + correo (sin distinguir mayúsculas). */
function buscarAdministrador($mysqli, $usuario, $correo)
{
    $usuario = mb_strtolower(trim((string) $usuario), 'UTF-8');
    $correo  = mb_strtolower(trim((string) $correo), 'UTF-8');

    foreach (administradoresActivos($mysqli) as $a) {
        if (mb_strtolower($a['usuario'], 'UTF-8') === $usuario && mb_strtolower($a['correo'], 'UTF-8') === $correo) {
            return $a;
        }
    }
    // Sin respaldo con claves fijas: un administrador desactivado o eliminado no puede entrar.
    return null;
}

/**
 * Carga la copia local de `usuarios.json` incluida en el proyecto.
 * Así la búsqueda por DNI funciona incluso si la API Node.js está apagada.
 */
function usuariosLocales()
{
    static $usuarios = null;
    if (is_array($usuarios)) return $usuarios;
    $usuarios = [];

    $rutas = [
        __DIR__ . '/API_Citas_Medicas/data/usuarios.json',
    ];
    foreach ($rutas as $ruta) {
        if (!is_file($ruta)) continue;
        $json = json_decode((string) @file_get_contents($ruta), true);
        if (isset($json['usuarios']) && is_array($json['usuarios'])) {
            $usuarios = $json['usuarios'];
            break;
        }
    }
    return $usuarios;
}

/** Busca un usuario en la copia local de usuarios.json por DNI. */
function usuarioLocalPorDni($dni)
{
    $dni = trim((string) $dni);
    if (!preg_match('/^\d{8}$/', $dni)) return null;
    foreach (usuariosLocales() as $u) {
        if ((string) ($u['dni'] ?? '') === $dni) {
            return $u;
        }
    }
    return null;
}

/**
 * Devuelve el usuario (local o de la API interna) que corresponde al DNI.
 */
function usuarioPorDni($dni)
{
    $local = usuarioLocalPorDni($dni);
    if ($local) return $local;
    return apiUsuarioPorDni($dni);
}

/** Convierte un texto a formato título: primera letra de cada palabra en mayúscula. */
function tituloPersona($texto)
{
    $texto = trim(preg_replace('/\s+/', ' ', (string)$texto));
    if ($texto === '') return '';
    return mb_convert_case(mb_strtolower($texto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

/**
 * Busca un usuario por DNI en la API interna de usuarios.
 * Devuelve null si la API no está disponible o el DNI no existe.
 */
function apiUsuarioPorDni($dni)
{
    static $cache = [];
    $dni = trim((string)$dni);
    if (!preg_match('/^\d{8}$/', $dni)) return null;
    if (array_key_exists($dni, $cache)) return $cache[$dni];

    $url = PRIMELAB_API_URL . '/api/usuarios/buscar?dni=' . urlencode($dni);
    $body = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT        => 4,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($resp !== false && $code >= 200 && $code < 300) {
            $body = $resp;
        }
    }

    if ($body === null && ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'timeout'       => 3,
                'header'        => "Accept: application/json\r\n",
                'ignore_errors' => true,
            ],
        ]);
        $resp = @file_get_contents($url, false, $ctx);
        if (is_string($resp) && $resp !== '') $body = $resp;
    }

    $usuario = null;
    if (is_string($body) && $body !== '') {
        $json = json_decode($body, true);
        if (is_array($json)) {
            $u = $json['usuario'] ?? $json;
            if (is_array($u) && (string)($u['dni'] ?? '') === $dni) {
                $usuario = $u;
            }
        }
    }

    $cache[$dni] = $usuario;
    return $usuario;
}

/**
 * Busca nombres y apellidos por DNI en el directorio local (usuarios.json) y,
 * si no está, en la API interna. NO consulta la tabla de pacientes: así no se
 * pueden obtener los datos de una cuenta ya registrada a partir de su DNI.
 */
function buscarPersonaPorDni($dni)
{
    $dni = trim((string)$dni);
    if (!preg_match('/^\d{8}$/', $dni)) return null;

    // 2) Copia local de usuarios.json incluida en el proyecto (sin depender de la API).
    $local = usuarioLocalPorDni($dni);
    if ($local) {
        return [
            'dni'       => $dni,
            'nombres'   => tituloPersona($local['nombres'] ?? ''),
            'apellidos' => tituloPersona($local['apellidos'] ?? ''),
            'telefono'  => (string)($local['telefono'] ?? ''),
            'correo'    => (string)($local['correo'] ?? ''),
            'fuente'    => 'Directorio local de usuarios',
        ];
    }

    // 3) Usuarios registrados en la API interna.
    $api = apiUsuarioPorDni($dni);
    if ($api) {
        return [
            'dni'       => $dni,
            'nombres'   => tituloPersona($api['nombres'] ?? ''),
            'apellidos' => tituloPersona($api['apellidos'] ?? ''),
            'telefono'  => (string)($api['telefono'] ?? ''),
            'correo'    => (string)($api['correo'] ?? ''),
            'fuente'    => 'API interna',
        ];
    }

    return null;
}

/** Escapa texto para mostrarlo en HTML. */
function e($texto)
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

/** Redirige y termina la ejecución. */
function redirigir($url)
{
    header("Location: " . $url);
    exit;
}

/** Guarda un mensaje flash en sesión. */
function flash($tipo, $texto)
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

/** Muestra y limpia el mensaje flash si existe. */
function mostrarFlash()
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $clase = ($f['tipo'] === 'exito' || $f['tipo'] === 'success') ? 'success' : 'error';
    echo '<div class="alert show ' . $clase . '" role="alert">' . e($f['texto']) . '</div>';
}

/** Exige sesión de paciente; si no hay, redirige al inicio. */
function requerirPaciente()
{
    if (empty($_SESSION['paciente_codp'])) {
        redirigir("index.html?msg=sesion");
    }
}

/** Exige sesión de administrador. */
function requerirAdmin()
{
    if (empty($_SESSION['admin_logged'])) {
        redirigir("admin_login.html");
    }
}

/** Genera un código correlativo tipo PREF001, PREF002... */
function generarCodigo($mysqli, $tabla, $campo, $prefijo)
{
    // Orden numérico (CIT999 -> CIT1000 no debe ordenarse como texto).
    $inicioNum = strlen($prefijo) + 1;
    $sql = "SELECT $campo AS cod FROM $tabla WHERE $campo LIKE ? ORDER BY CAST(SUBSTRING($campo, $inicioNum) AS UNSIGNED) DESC, $campo DESC LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $like = $prefijo . '%';
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $ultimo = $res->fetch_assoc();
    $stmt->close();

    $num = 0;
    if ($ultimo && preg_match('/(\d+)$/', $ultimo['cod'], $m)) {
        $num = (int)$m[1];
    }
    $num++;
    return $prefijo . str_pad((string)$num, 3, '0', STR_PAD_LEFT);
}

/** Formatea fecha YYYY-MM-DD a texto legible. */
function fechaTexto($fecha)
{
    if (!$fecha) return '';
    $ts = strtotime($fecha);
    if (!$ts) return $fecha;
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $d = date('j', $ts);
    $m = (int)date('n', $ts);
    $y = date('Y', $ts);
    return $d . ' de ' . $meses[$m - 1] . ' de ' . $y;
}

/** Formatea hora HH:MM:SS a HH:MM. */
function horaTexto($hora)
{
    if (!$hora) return '';
    return substr($hora, 0, 5);
}

/** Devuelve el paciente actual desde sesión (o null). */
function pacienteActual($mysqli)
{
    if (empty($_SESSION['paciente_codp'])) return null;
    $stmt = $mysqli->prepare("SELECT codp, nomp, apep, dni, telp, correo, seguro, fechanac, direccion, distrito, fechareg FROM paciente WHERE codp = ? LIMIT 1");
    $stmt->bind_param("s", $_SESSION['paciente_codp']);
    $stmt->execute();
    $res = $stmt->get_result();
    $pac = $res->fetch_assoc();
    $stmt->close();
    return $pac ?: null;
}

/** Exige sesión de médico. */
function requerirMedico()
{
    if (empty($_SESSION['medico_codm'])) {
        redirigir("medico_login.html?msg=sesion");
    }
}

/** Devuelve el médico actual desde sesión (o null). */
function medicoActual($mysqli)
{
    if (empty($_SESSION['medico_codm'])) return null;
    $stmt = $mysqli->prepare("SELECT m.codm, m.nomm, m.cmpm, m.codesp, m.dni, m.telefono, m.correo, m.anios_experiencia, m.formacion, e.nomesp
                              FROM medico m JOIN especialidad e ON e.codesp = m.codesp
                              WHERE m.codm = ? LIMIT 1");
    $stmt->bind_param("s", $_SESSION['medico_codm']);
    $stmt->execute();
    $res = $stmt->get_result();
    $med = $res->fetch_assoc();
    $stmt->close();
    return $med ?: null;
}

/** Calcula el costo de la cita según el tipo de seguro. */
function calcularCosto($seguro)
{
    $s = strtoupper(trim((string)$seguro));
    if ($s === 'EPS') return 20.00;
    if ($s === 'PRIVADO') return 50.00;
    return 0.00; // SIS u otros
}

/** Devuelve texto legible del costo. */
function costoTexto($seguro, $costo = null)
{
    if ($costo === null) $costo = calcularCosto($seguro);
    $monto = number_format((float)$costo, 2);
    $s = strtoupper(trim((string)$seguro));
    if ((float)$costo <= 0) return 'S/ 0.00 (cubierto por ' . ($s ?: 'SIS') . ')';
    return 'S/ ' . $monto;
}

/** Nombre legible del seguro. */
function textoSeguro($seguro)
{
    $s = strtoupper(trim((string)$seguro));
    if ($s === 'EPS') return 'EPS';
    if ($s === 'PRIVADO') return 'Seguro privado';
    return 'SIS';
}

/**
 * Devuelve el HTML de la foto/avatar del médico.
 * Usa img/medicos/{codm}.svg si existe; si no, muestra un avatar con inicial.
 */
function fotoMedico($codm, $nomm = '', $tam = 44, $clase = 'medico-foto')
{
    $codm = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$codm);
    $archivo = __DIR__ . '/img/medicos/' . $codm . '.svg';
    $src = 'img/medicos/' . $codm . '.svg';
    $alt = $nomm !== '' ? $nomm : 'Médico';
    $style = 'width:' . (int)$tam . 'px;height:' . (int)$tam . 'px;';
    if ($codm !== '' && file_exists($archivo)) {
        return '<img src="' . e($src) . '" alt="' . e($alt) . '" class="' . e($clase) . '" style="' . $style . '">';
    }
    $inicial = $nomm !== '' ? mb_strtoupper(mb_substr(trim($nomm), 0, 1, 'UTF-8'), 'UTF-8') : 'M';
    return '<span class="' . e($clase) . ' medico-foto-fallback" style="' . $style . '" aria-label="' . e($alt) . '">' . e($inicial) . '</span>';
}
?>