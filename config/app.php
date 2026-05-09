<?php

define('BASE_URL', '/storage-app/public/');

// Ruta absoluta
define('ROOT_PATH', realpath(__DIR__ . '/../'));

// Definimos la ruta absoluta al archivo de logs
ini_set('error_log', __DIR__ . '/../logs/debug.log');