<?php
// Archivo central del controlador, con funciones comunes para todos los controladores.
require_once __DIR__ . '/View.php';

class Controller {

    // Renderizar vista con datos
    protected function view($view, $data = []) { 

        // Datos globales
        $globalData = [
            'user_name' => $_SESSION['user_name'] ?? null,
            'role_id'   => $_SESSION['role_id'] ?? null,
            'user_id'   => $_SESSION['user_id'] ?? null,
        ];

        // Guardarlos en $data
        $data = array_merge($globalData, $data);

        // Acá puedo añadir datos comunes a todas las vistas, como el usuario logueado, etc.
        View::render($view, $data);
    }

    // Respuesta JSON estándar
    protected function response($status, $message, $data = null, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status,
            'message'    => $message,
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
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    // Redirige a dashboard si no es admin y la vista lo requiere
    protected function requireAdmin() {
        $this->requireAuth();
        if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== 1) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
    }

    // No permitir acceso a esta página si el usuario ya está autenticado, redirigiendo a dashboard
    protected function requireGuest() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
    }

    // Para registrar errores en el log y enviar una respuesta genérica al usuario, evitando mostrar detalles técnicos
    protected function logError(Exception $e, $context = "GENERAL") {
        error_log(" [{$context}_ERROR] " . $e->getMessage());

        $mensajeUsuario = $e->getMessage();

        // Filtramos SOLO si detectamos errores técnicos conocidos
        if (strpos($mensajeUsuario, 'SQLSTATE') !== false) {
            $mensajeUsuario = "Error de base de datos. Por favor, contacte al soporte.";
        }

        $this->response(false, $mensajeUsuario);
    }
}