<?php

define('BASE_URL', '/storage-app/public/');

// Ruta absoluta
define('ROOT_PATH', realpath(__DIR__ . '/../'));

// Definimos la ruta absoluta al archivo de logs
ini_set('error_log', __DIR__ . '/../logs/debug.log');

define('DB_HOST', 'localhost');
define('DB_NAME', 'storage_app');
define('DB_USER', 'root');
define('DB_PASS', '');