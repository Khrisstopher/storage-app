<?php

namespace App\Core;

/**
 * Descripció: Clase para manejo de sesiones y autenticación
 * 
 * @author @KhrisstopherTube
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class Session {
    
    // Inicia la sesión de forma segura si no está iniciada
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Guarda datos en la sesión (Login)
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    // Obtiene un valor o un default si no existe
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    // Comprueba si el usuario está logueado
    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }

    // Azúcar sintáctico para datos comunes
    public static function userId() { return self::get('user_id'); }
    public static function userName() { return self::get('user_name'); }
    public static function userRole() { return self::get('role_id'); }

    // Seguridad: Regenerar ID para evitar Session Fixation
    public static function regenerate() {
        session_regenerate_id(true);
    }

    // Cerrar sesión completamente
    public static function destroy() {
        self::init();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}