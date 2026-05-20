<?php
// Esto lo vamos a implementar más adelante, por ahora no hace nada, 
// pero la idea es que aquí tengamos métodos estáticos para enviar respuestas estandarizadas en toda la aplicación.
namespace App\Helpers;

/**
 * Métodos estáticos para estandarizar las respuestas de la aplicación.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class ResponseHelper {

    /**
     * Envía una respuesta JSON estandarizada y finaliza la ejecución.
     */
    public static function jsonResponse(bool $status, string $message, $data = null, int $code = 200): void {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ]);
        exit;
    }
}