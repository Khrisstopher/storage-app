<?php

namespace App\Core;

use App\Config\DataBase;

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/Session.php';

/**
 * Descripción: Controlador de rutas
 * 
 * @author @KhrisstopherTube
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class Router {

    private $routes = [
        // [Carpeta, Controlador, Método, Verbo]

        // Rutas para vistas (web)
        ''                  => ['web','App\Controllers\Web\AuthController', 'home', 'GET'],
        'home'              => ['web','App\Controllers\Web\AuthController', 'home', 'GET'],
        'login'             => ['web','App\Controllers\Web\AuthController', 'login', 'GET'],
        'register'          => ['web','App\Controllers\Web\AuthController', 'register', 'GET'],
        'dashboard'         => ['web','App\Controllers\Web\DashboardController', 'dashboard', 'GET'],
        'admin/settings'    => ['web','App\Controllers\Web\AdminSettingController', 'index', 'GET'],

        // Rutas para lógica de negocio (API)

        // Rutas para autenticación
        'auth/register' => ['api','App\Controllers\Api\AuthController', 'register', 'POST'],
        'auth/login'    => ['api','App\Controllers\Api\AuthController', 'login', 'POST'],
        'auth/logout'   => ['api','App\Controllers\Api\AuthController', 'logout', 'POST'],

        // Rutas para gestión de archivos
        'files/list'    => ['api','App\Controllers\Api\DashboardController', 'list', 'GET'],
        'files/upload'  => ['api','App\Controllers\Api\DashboardController', 'upload', 'POST'],
        'files/delete'  => ['api','App\Controllers\Api\DashboardController', 'delete', 'POST'],
        'files/download' => ['api', 'App\Controllers\Api\DashboardController', 'download', 'GET'],

        // Rutas para configuración de administración
        'settings/file-restrictions' => [
            'api','App\Controllers\Api\AdminSettingController', 'fileRestrictions', 'GET'
        ],
        'settings/file-restrictions/save' => [
            'api','App\Controllers\Api\AdminSettingController', 'saveFileRestrictions', 'POST'
        ],
        'settings/quota-global-limit/save' => [
            'api','App\Controllers\Api\AdminSettingController', 'saveQuotaGlobalLimit', 'POST'
        ],
        'settings/quota-global-limit/list' => [
            'api','App\Controllers\Api\AdminSettingController', 'getQuotaGlobalLimit', 'GET'
        ],
    ];

    // No sé si esto se pueda omitir y usar el método de core/controller.php
    private function jsonResponse($status, $message, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'message' => $message
        ]);
        exit;
    }

    public function dispatch() {

        Session::init();

        $url = trim($_GET['url'] ?? '', '/');

        if (!array_key_exists($url, $this->routes)) {
            http_response_code(404);
            require_once __DIR__ . '/../../views/404.php';
            exit;
        }

        // Asigna el controlador y método a las variables según la ruta
        [$folder, $controllerName, $method, $httpMethod] = $this->routes[$url];

        if ($_SERVER['REQUEST_METHOD'] !== $httpMethod) {
            $this->jsonResponse(false, 'Método no permitido', 405);
        }

        // Extraemos el final de la ruta
        $parts = explode('\\', $controllerName);
        $fileName = end($parts);

        // Ahora cargamos el archivo usando el nombre corto ($fileName)
        $fileContent = __DIR__ . "/../controllers/$folder/$fileName.php";

        if (!file_exists($fileContent)) {
            $this->jsonResponse(false, "Archivo del controlador no encontrado", 500);
        }
        
        require_once $fileContent;

        // Verifica que la clase del controlador exista y que el método esté implementado
        if (!class_exists($controllerName)) {
            $this->jsonResponse(false, 'Controlador no encontrado' . $controllerName, 500);
        }
        if (!method_exists($controllerName, $method)) {
            $this->jsonResponse(false, 'Método no implementado', 500);
        }

        // Instanciamos la base de datos de forma uniforme para TODOS los controladores
        $database = new Database();
        $pdo = $database->getConnection();

        // Instancia el controlador pasándole la conexión PDO
        $controller = new $controllerName($pdo);

        // Ejecuta el método correspondiente
        $controller->$method(); 
    }
}