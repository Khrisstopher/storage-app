<?php
/**
 * Archivo global de configuraciones.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */

define('BASE_URL', '/storage-app/public/');

// Ruta absoluta
define('ROOT_PATH', realpath(__DIR__ . '/../'));


ini_set('error_log', __DIR__ . '/../logs/debug.log');

function cargarEnv($ruta) {
    if (!file_exists($ruta)) return;
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        if (strpos(trim($linea), '#') === 0) continue;
        list($nombre, $valor) = explode('=', $linea, 2);
        $_ENV[trim($nombre)] = trim($valor);
    }
}

cargarEnv(ROOT_PATH . '/.env');

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'storage_app');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');