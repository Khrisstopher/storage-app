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
        echo json_encode([
            'status' => $status,
            'msg'    => $msg,
            'data'   => $data
        ]);exit;
    }
}