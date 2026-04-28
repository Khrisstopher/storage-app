<?php
// Archivo central del controlador, con funciones comunes para todos los controladores.
require_once __DIR__ . '/View.php';

class Controller {

    // Renderizar vista con datos
    protected function view($view, $data = []) { 
        // Acá puedo añadir datos comunes a todas las vistas, como el usuario logueado, etc.
        View::render($view, $data);
    }

    // Respuesta JSON estándar
    protected function response($status, $msg, $data = null, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'msg'    => $msg,
            'data'   => $data
        ]);exit;
    }

    // Validar el formato correcto de los datos recibidos en el cuerpo de la solicitud
    protected function getRequestData(): array {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }

        return $_POST;
    }

    // Verificar que el usuario esté autenticado, redirigiendo a login si no lo está
    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
}