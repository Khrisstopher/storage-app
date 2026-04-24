<?php

class Router {

    private $routes = [
        // Rutas para páginas
        ''          => ['PageController', 'home'],
        'home'      => ['PageController', 'home'],
        'login'     => ['PageController', 'login'],
        'register'  => ['PageController', 'register'],

        // NUEVAS rutas para lógica
        'auth/register' => ['AuthController', 'handleRequest'],
        'auth/login'    => ['AuthController', 'handleRequest'],
    ];

    public function dispatch() {

        $url = trim($_GET['url'] ?? '', '/');

        if (!array_key_exists($url, $this->routes)) {
            http_response_code(404);
            die("404 - Página no encontrada");
        }

        [$controllerName, $method] = $this->routes[$url]; // Asigna el controlador y método a las variables según la ruta

        require_once __DIR__ . '/../controllers/' . $controllerName . '.php'; // Carga el archivo del controlador

        $controller = new $controllerName(); // Instancia el controlador

        $controller->$method(); // Ejecuta el método correspondiente
    }
}