<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../../config/db_connection.php';

class AuthController extends Controller {
    private AuthService $authService;

    public function __construct(PDO $db) {
        $this->authService = new AuthService($db);
    }


    private function getRequestMethod(): string {
        $method = $_SERVER['REQUEST_METHOD'];

        $allowed = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

        if (!in_array($method, $allowed)) {
            $this->response(false, 'Método HTTP no permitido');
        }

        return $method;
    }

    // Definir rutas para cada método HTTP
    private array $routes = [
        'POST' => [
            'register' => 'register',
            'login' => 'login',
            'logout' => 'logout'
        ],
        'GET' => [
            'me' => 'me'
        ]
    ];

    public function handleRequest() {
        $method = $this->getRequestMethod();

        $action = $_REQUEST['action'] ?? null;

        if (!$action) {
            $this->response(false, 'Acción requerida');
        }

        if (!isset($this->routes[$method][$action])) {
            $this->response(false, 'Ruta no válida');
        }

        $methodName = $this->routes[$method][$action];

        if (!method_exists($this->authService, $methodName)) {
            $this->response(false, 'Método no implementado');
        }

        // Ejecutar método dinámicamente
        $result = $this->authService->$methodName($_REQUEST);

        $this->response(
            $result['status'],
            $result['msg'],
            $result['data'] ?? null
        );
    }
}

// Instanciar la clase Database y obtener la conexión
$database = new Database();
$pdo = $database->getConnection();

// Ejecutar
$controller = new AuthController($pdo);
$controller->handleRequest();