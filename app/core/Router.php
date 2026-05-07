<?php

require_once __DIR__ . '/../../config/db_connection.php';

class Router {

    private $routes = [
        // [Carpeta, Controlador, Método, Verbo]

        // Rutas para vistas (web)
        ''                  => ['web','PageController', 'home', 'GET'],
        'home'              => ['web','PageController', 'home', 'GET'],
        'login'             => ['web','PageController', 'login', 'GET'],
        'register'          => ['web','PageController', 'register', 'GET'],
        'dashboard'         => ['web','DashboardController', 'dashboard', 'GET'],
        'admin/settings'    => ['web','AdminSettingsController', 'index', 'GET'],

        // Rutas para lógica de negocio (API)

        // Rutas para autenticación
        'auth/register' => ['api','AuthController', 'register', 'POST'],
        'auth/login'    => ['api','AuthController', 'login', 'POST'],
        'auth/logout'   => ['api','AuthController', 'logout', 'POST'],

        // Rutas para gestión de archivos
        'files/list'    => ['api','FileController', 'list', 'GET'],
        'files/upload'  => ['api','FileController', 'upload', 'POST'],
        'files/delete'  => ['api','FileController', 'delete', 'POST'],

        // Rutas para configuración de administración
        'settings/file-restrictions'      => ['api','AdminSettingsController', 'fileRestrictions', 'GET'],
        'settings/file-restrictions/save' => ['api','AdminSettingsController', 'saveFileRestrictions', 'POST'],
    ];

    private function jsonResponse($status, $msg, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'msg' => $msg
        ]);
        exit;
    }

    public function dispatch() {

        // Inicializo la sesión en el punto de entrada de la aplicación 
        // para garantizar disponibilidad global y evitar duplicación en los controladores.
        session_start();

        $url = trim($_GET['url'] ?? '', '/');

        if (!array_key_exists($url, $this->routes)) {
            $this->jsonResponse(false, 'Ruta no encontrada', 404);
        }

        // Asigna el controlador y método a las variables según la ruta
        [$folder, $controllerName, $method, $httpMethod] = $this->routes[$url];

        if ($_SERVER['REQUEST_METHOD'] !== $httpMethod) {
            $this->jsonResponse(false, 'Método no permitido', 405);
        }
        
        require_once __DIR__ . "/../controllers/$folder/$controllerName.php";

        // Verifica que la clase del controlador exista y que el método esté implementado
        if (!class_exists($controllerName)) {
            $this->jsonResponse(false, 'Controlador no encontrado', 500);
        }
        if (!method_exists($controllerName, $method)) {
            $this->jsonResponse(false, 'Método no implementado', 500);
        }

        $database = new Database();
        $pdo = $database->getConnection();

        // Instancia el controlador pasándole la conexión PDO
        $controller = new $controllerName($pdo);

        // Ejecuta el método correspondiente
        $controller->$method(); 
    }
}