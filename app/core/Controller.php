<?php

namespace App\Core;

use App\Core\View;

require_once __DIR__ . '/View.php';
require_once __DIR__ . '/Session.php';

/**
 * Controlador base de funcionalidades comunes
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class Controller {

    // Renderizar vista con datos
    protected function view($view, $data = []) { 

        $globalData = [
            'user_name' => Session::userName(),
            'role_id'   => Session::userRole(),
            'user_id'   => Session::userId(),
            'is_logged' => Session::check(),
        ];

        $data = array_merge($globalData, $data);
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
        ]);
        exit;
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
        if (!Session::check()) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    // Redirige a dashboard si no es admin y la vista lo requiere
    protected function requireAdmin() {
        $this->requireAuth();
        if ((int)Session::userRole() !== 1) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
    }

    // No permitir acceso a esta página si el usuario ya está autenticado, redirigiendo a dashboard
    protected function requireGuest() {
        if (Session::check()) {
            header('Location: ' . BASE_URL . 'dashboard');
            exit;
        }
    }

    // Para registrar errores en el log y enviar una respuesta genérica al usuario, evitando mostrar detalles técnicos
    protected function logError(\Throwable $e, $context = "GENERAL") {
        error_log(" [{$context}_ERROR] " . $e->getMessage());

        $mensajeUsuario = $e->getMessage();

        // Filtramos SOLO si detectamos errores técnicos conocidos
        if ($e instanceof \PDOException || $e instanceof \Error) {
            $mensajeUsuario = "Error de base de datos. Por favor, contacte al soporte.";
        }

        $this->response(false, $mensajeUsuario);
    }

    /**
     * Comprueba si el token CSRF enviado por el cliente es válido.
     * @return bool True si coincide, false en caso contrario.
     */
    protected function isValidCSRF(): bool {
        $headers = getallheaders();
        $headers = array_change_key_case($headers, CASE_LOWER);

        $clientToken = $headers['x-csrf-token'] ?? ($_POST['csrf_token'] ?? null);
        $serverToken = Session::get('csrf_token');

        if (!$clientToken || !$serverToken) {
            return false;
        }

        return hash_equals($serverToken, $clientToken);
    }

    /**
     * Si el token falla, mata la app con un 403.
     */
    protected function checkCSRFStrict() {
        if (!$this->isValidCSRF()) {
            $this->response(false, 'Acceso denegado: Token de seguridad inválido o expirado.', null, 403);
        }
    }
}