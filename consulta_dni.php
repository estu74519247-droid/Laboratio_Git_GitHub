<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// La consulta puede probar varias fuentes externas; se amplía el límite de tiempo
// para que XAMPP no corte la respuesta a la mitad.
@set_time_limit(45);

// Este endpoint solo devuelve JSON: no necesita abrir sesión.
if (!defined('PRIMELAB_SIN_SESION')) define('PRIMELAB_SIN_SESION', true);

require_once __DIR__ . '/file_conexion.php';

// consulta_dni.php — Obtiene nombres/apellidos por DNI.
// Primero consulta la base de datos y la API interna de PrimeLab (Node.js)
// y, si no hay datos, prueba servicios públicos/terceros disponibles sin token.
// Uso: consulta_dni.php?dni=12345678

function limpiarTexto($texto) {
    $texto = trim((string)$texto);
    if ($texto === '') return '';
    $texto = preg_replace('/\s+/', ' ', $texto);
    return mb_convert_case(mb_strtolower($texto, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

function responder($ok, $dni, $nombres = '', $apellidos = '', $mensaje = '', $extra = []) {
    $out = [
        'ok'        => $ok,
        'dni'       => $dni,
        'nombres'   => $nombres,
        'apellidos' => $apellidos,
        'mensaje'   => $mensaje
    ];
    if (!empty($extra)) $out['detalle'] = $extra;
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

function fetchRaw($url, $timeout = 10) {
    // Se limita el tiempo para no agotar el máximo de ejecución de PHP.
    $timeout = min((int)$timeout, 6);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_SSL_VERIFYPEER => PRIMELAB_SSL_VERIFY,
        CURLOPT_SSL_VERIFYHOST => PRIMELAB_SSL_VERIFY ? 2 : 0,
        CURLOPT_ENCODING       => '',
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: es-PE,es;q=0.9,en;q=0.8',
            'Cache-Control: no-cache'
        ]
    ]);
    $resp = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['body' => $resp, 'code' => $code, 'error' => $err];
}

function parseJson($body) {
    if (!is_string($body) || $body === '') return null;
    $json = json_decode($body, true);
    return is_array($json) ? $json : null;
}

function normalizarPersona($nombres, $apPaterno = '', $apMaterno = '', $apellidos = '') {
    $n = limpiarTexto($nombres);
    $ap = limpiarTexto(trim($apPaterno . ' ' . $apMaterno));
    if ($ap === '') $ap = limpiarTexto($apellidos);
    // si viene todo junto en nombres, intentar no duplicar
    if ($n === '' && $ap !== '') { $n = ''; }
    return ['nombres' => $n, 'apellidos' => $ap];
}

$dni = trim($_GET['dni'] ?? '');
$debug = false; // el modo debug se eliminó: filtraba detalles internos a cualquier visitante

if (!preg_match('/^\d{8}$/', $dni)) {
    responder(false, $dni, '', '', 'El DNI debe tener exactamente 8 dígitos numéricos.');
}

$intentos = [];

// Límite de consultas por IP: evita recorrer DNI uno por uno para sacar nombres.
$limiteDni = 'dni|' . primelab_ip();
if (primelab_bloqueado($limiteDni, 20, 600)) {
    http_response_code(429);
    responder(false, $dni, '', '', 'Demasiadas consultas seguidas. Espera unos minutos o escribe tus nombres manualmente.');
}
primelab_registrar_intento($limiteDni, 600);

// 0) Si el DNI ya tiene cuenta no se devuelven sus datos: solo se avisa.
$dbDni = ConectarseOpcional();
if ($dbDni) {
    $stDni = @$dbDni->prepare("SELECT 1 FROM paciente WHERE dni = ? LIMIT 1");
    if ($stDni) {
        $stDni->bind_param("s", $dni);
        $stDni->execute();
        $yaExiste = (bool) $stDni->get_result()->fetch_assoc();
        $stDni->close();
        if ($yaExiste) {
            $dbDni->close();
            responder(false, $dni, '', '', 'Ese DNI ya tiene una cuenta registrada. Inicia sesión desde la página de pacientes.');
        }
    }
    $dbDni->close();
}

// 1) Directorio local (usuarios.json) y API interna de usuarios (solo en este servidor).
$persona = null;
$local = usuarioLocalPorDni($dni) ?: apiUsuarioPorDni($dni);
if ($local) {
    $persona = [
        'nombres'   => tituloPersona($local['nombres'] ?? ''),
        'apellidos' => tituloPersona($local['apellidos'] ?? ''),
        'fuente'    => 'directorio de usuarios',
    ];
}
$intentos['sistema'] = ['fuente' => $persona['fuente'] ?? 'sin coincidencia'];
if ($persona && ($persona['nombres'] !== '' || $persona['apellidos'] !== '')) {
    responder(
        true,
        $dni,
        $persona['nombres'],
        $persona['apellidos'],
        'Datos encontrados en ' . $persona['fuente'] . '.',
        []
    );
}

if (!function_exists('curl_init')) {
    responder(false, $dni, '', '', 'El servidor no tiene habilitada la extensión cURL de PHP. Activa php_curl en XAMPP/Apache para consultar el DNI.', ['curl' => false]);
}

// 1) SUNAT (consulta pública usada en algunos servicios)
$r = fetchRaw('https://ww1.sunat.gob.pe/ol-ti-itelav455nameregsunat/api/registro/solicitud?nrodocumento=' . $dni, 12);
$j = parseJson($r['body']);
$intentos['sunat'] = ['http' => $r['code'], 'error' => $r['error']];
if ($j) {
    $nombreCompleto = trim($j['nombre'] ?? '');
    if ($nombreCompleto !== '' && $nombreCompleto !== ',') {
        $partes = explode(',', $nombreCompleto, 2);
        $apellidos = limpiarTexto($partes[0] ?? '');
        $nombres = limpiarTexto($partes[1] ?? '');
        if ($nombres !== '' || $apellidos !== '') {
            responder(true, $dni, $nombres, $apellidos, 'Datos encontrados (SUNAT).', []);
        }
    }
}

// 2) apis.net.pe v1 (pública sin token en algunos casos, puede requerir token)
$r = fetchRaw('https://api.apis.net.pe/v1/dni?numero=' . $dni, 10);
$j = parseJson($r['body']);
$intentos['apis.net.pe.v1'] = ['http' => $r['code'], 'error' => $r['error']];
if ($j && empty($j['message']) && (isset($j['nombres']) || isset($j['apellidoPaterno']) || isset($j['nombre']))) {
    $p = normalizarPersona($j['nombres'] ?? ($j['nombre'] ?? ''), $j['apellidoPaterno'] ?? '', $j['apellidoMaterno'] ?? '', $j['apellidos'] ?? '');
    if ($p['nombres'] !== '' || $p['apellidos'] !== '') {
        responder(true, $dni, $p['nombres'], $p['apellidos'], 'Datos encontrados.', []);
    }
}

// 3) apis.net.pe v2
$r = fetchRaw('https://api.apis.net.pe/v2/reniec/dni?numero=' . $dni, 10);
$j = parseJson($r['body']);
$intentos['apis.net.pe.v2'] = ['http' => $r['code'], 'error' => $r['error']];
if ($j && empty($j['message']) && (isset($j['nombres']) || isset($j['apellidoPaterno']))) {
    $p = normalizarPersona($j['nombres'] ?? '', $j['apellidoPaterno'] ?? '', $j['apellidoMaterno'] ?? '');
    if ($p['nombres'] !== '' || $p['apellidos'] !== '') {
        responder(true, $dni, $p['nombres'], $p['apellidos'], 'Datos encontrados.', []);
    }
}

// 4) miapi.cloud
$r = fetchRaw('https://miapi.cloud/v1/dni/' . $dni, 10);
$j = parseJson($r['body']);
$intentos['miapi.cloud'] = ['http' => $r['code'], 'error' => $r['error']];
if ($j && (isset($j['nombres']) || isset($j['datos']['nombres']) || isset($j['name']))) {
    $src = isset($j['datos']) && is_array($j['datos']) ? $j['datos'] : $j;
    $p = normalizarPersona($src['nombres'] ?? ($src['name'] ?? ''), $src['apellido_paterno'] ?? ($src['apellidoPaterno'] ?? ''), $src['apellido_materno'] ?? ($src['apellidoMaterno'] ?? ''), $src['apellidos'] ?? ($src['apellido'] ?? ''));
    if ($p['nombres'] !== '' || $p['apellidos'] !== '') {
        responder(true, $dni, $p['nombres'], $p['apellidos'], 'Datos encontrados.', []);
    }
}

// 5) apiperu.dev (público - puede cambiar)
$r = fetchRaw('https://apiperu.dev/api/dni/' . $dni, 10);
$j = parseJson($r['body']);
$intentos['apiperu.dev'] = ['http' => $r['code'], 'error' => $r['error']];
if ($j && (isset($j['data']) || isset($j['nombres']))) {
    $src = isset($j['data']) && is_array($j['data']) ? $j['data'] : $j;
    $p = normalizarPersona($src['nombres'] ?? '', $src['apellido_paterno'] ?? ($src['apellidoPaterno'] ?? ''), $src['apellido_materno'] ?? ($src['apellidoMaterno'] ?? ''), $src['nombre_completo'] ?? '');
    if ($p['nombres'] === '' && $p['apellidos'] === '' && !empty($src['nombre_completo'])) {
        // intentar split simple
        $nc = limpiarTexto($src['nombre_completo']);
        $p['nombres'] = $nc;
    }
    if ($p['nombres'] !== '' || $p['apellidos'] !== '') {
        responder(true, $dni, $p['nombres'], $p['apellidos'], 'Datos encontrados.', []);
    }
}

// 6) reniec.cloud (servicio alternativo de consulta RENIEC)
$r = fetchRaw('https://api.reniec.cloud/dni/' . $dni, 10);
$j = parseJson($r['body']);
$intentos['reniec.cloud'] = ['http' => $r['code'], 'error' => $r['error']];
if ($j && (isset($j['nombres']) || isset($j['apellidoPaterno']) || isset($j['nombres_completos']))) {
    $p = normalizarPersona(
        $j['nombres'] ?? ($j['nombres_completos'] ?? ''),
        $j['apellidoPaterno'] ?? ($j['apellido_paterno'] ?? ''),
        $j['apellidoMaterno'] ?? ($j['apellido_materno'] ?? '')
    );
    if ($p['nombres'] !== '' || $p['apellidos'] !== '') {
        responder(true, $dni, $p['nombres'], $p['apellidos'], 'Datos encontrados (RENIEC).', []);
    }
}

$mensaje = 'No se pudo obtener los nombres y apellidos para este DNI en el directorio interno ni en las fuentes RENIEC consultadas. ';
$mensaje .= 'RENIEC no ofrece una API pública oficial gratuita y directa; los servicios alternativos pueden estar caídos, limitados o requerir token. ';
$mensaje .= 'Verifica que tengas internet y que la extensión cURL esté habilitada en PHP. ';
$mensaje .= 'También puedes escribir tus nombres y apellidos manualmente (el sistema lo permite como respaldo).';

responder(false, $dni, '', '', $mensaje, []);
?>