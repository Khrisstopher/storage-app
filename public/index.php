<?php

use App\Core\Router;
use App\Config\DatabaseInstaller;

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/DatabaseInstaller.php';

DatabaseInstaller::checkAndInstall();

require_once __DIR__ . '/../app/core/Router.php';

// Archivo de entrada principal, que maneja todas las solicitudes y enruta a los controladores correspondientes.
$router = new Router();
$router->dispatch();