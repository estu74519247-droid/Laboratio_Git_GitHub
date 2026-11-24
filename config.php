<?php
/**
 * config.php — Configuración de conexiones de PrimeLab.
 *
 * El sistema es PORTABLE: funciona en cualquier IP y puerto de XAMPP.
 *  - El puerto de MySQL se detecta automáticamente (3306, 3307, 3308, 3309 o 3009).
 *  - La URL de la API interna se arma con el mismo host desde el que se abre el sitio.
 *  - Si necesitas fijar valores manualmente, crea un archivo `config.local.php`
 *    o define variables de entorno:
 *      PRIMELAB_DB_HOST, PRIMELAB_DB_PORT, PRIMELAB_DB_USER,
 *      PRIMELAB_DB_PASS, PRIMELAB_DB_NAME, PRIMELAB_API_URL
 */

/* Zona horaria del sistema (evita que date() use la de XAMPP, que suele ser otra). */
date_default_timezone_set('America/Lima');

/* -------------------------------------------------------------------------
 * 1) Detección automática del puerto de MySQL/MariaDB de XAMPP
 * ---------------------------------------------------------------------- */
if (!function_exists('primelab_detectar_puerto_mysql')) {
    /**
     * Prueba los puertos habituales de XAMPP y devuelve el primero abierto.
     * Si ninguno responde, devuelve 3306 (valor estándar de MySQL).
     */
    function primelab_detectar_puerto_mysql()
    {
        static $puerto = null;
        if ($puerto !== null) {
            return $puerto;
        }

        // El puerto detectado se guarda 5 minutos para no sondear en cada petición.
        $cache = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'primelab_mysql_port_' . md5(__DIR__) . '.txt';
        if (is_file($cache) && (time() - (int) @filemtime($cache)) < 300) {
            $guardado = (int) @file_get_contents($cache);
            if ($guardado > 0) {
                return $puerto = $guardado;
            }
        }

        $host = defined('PRIMELAB_DB_HOST') ? PRIMELAB_DB_HOST : (getenv('PRIMELAB_DB_HOST') ?: '127.0.0.1');
        $candidatos = [3306, 3307, 3308, 3309, 3009];

        foreach ($candidatos as $p) {
            $socket = @fsockopen($host, $p, $errno, $errstr, 0.4);
            if ($socket) {
                fclose($socket);
                @file_put_contents($cache, (string) $p);
                return $puerto = $p;
            }
        }

        return $puerto = 3306;
    }
}

/* -------------------------------------------------------------------------
 * 2) Valores opcionales definidos por el usuario (config.local.php)
 * ---------------------------------------------------------------------- */
$primelab_config_local = __DIR__ . '/config.local.php';
if (is_file($primelab_config_local)) {
    require_once $primelab_config_local;
}

/* -------------------------------------------------------------------------
 * 3) Constantes de conexión
 * ---------------------------------------------------------------------- */
if (!defined('PRIMELAB_DB_HOST')) {
    define('PRIMELAB_DB_HOST', getenv('PRIMELAB_DB_HOST') ?: '127.0.0.1');
}
if (!defined('PRIMELAB_DB_USER')) {
    define('PRIMELAB_DB_USER', getenv('PRIMELAB_DB_USER') ?: 'root');
}
if (!defined('PRIMELAB_DB_PASS')) {
    define('PRIMELAB_DB_PASS', getenv('PRIMELAB_DB_PASS') ?: '');
}
if (!defined('PRIMELAB_DB_NAME')) {
    define('PRIMELAB_DB_NAME', getenv('PRIMELAB_DB_NAME') ?: 'bd_primelab');
}
if (!defined('PRIMELAB_DB_PORT')) {
    $primelab_puerto_env = getenv('PRIMELAB_DB_PORT');
    if ($primelab_puerto_env !== false && $primelab_puerto_env !== '') {
        define('PRIMELAB_DB_PORT', (int) $primelab_puerto_env);
    } else {
        define('PRIMELAB_DB_PORT', primelab_detectar_puerto_mysql());
    }
}

/* -------------------------------------------------------------------------
 * 4) URL de la API interna (Node.js + Express)
 *    Se calcula con el mismo host de la petición para que funcione en LAN.
 * ---------------------------------------------------------------------- */
if (!defined('PRIMELAB_API_URL')) {
    // Valor fijo: la API solo escucha en 127.0.0.1. No se usa el header Host
    // porque lo controla el cliente (permitiría dirigir la petición a otro servidor).
    $primelab_api_env = getenv('PRIMELAB_API_URL');
    define('PRIMELAB_API_URL', $primelab_api_env ? rtrim($primelab_api_env, '/') : 'http://127.0.0.1:3000');
}

/* -------------------------------------------------------------------------
 * 5) Verificación SSL en las consultas externas (cURL).
 *    Si tu XAMPP no tiene certificados raíz configurados, define
 *    PRIMELAB_SSL_VERIFY=false SOLO en config.local.php para pruebas locales.
 * ---------------------------------------------------------------------- */
if (!defined('PRIMELAB_SSL_VERIFY')) {
    $primelab_ssl_env = getenv('PRIMELAB_SSL_VERIFY');
    define('PRIMELAB_SSL_VERIFY', !($primelab_ssl_env !== false && in_array(strtolower($primelab_ssl_env), ['0', 'false', 'no'], true)));
}
