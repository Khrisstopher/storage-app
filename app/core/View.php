<?php

namespace App\Core;
// Archivo central para renderizar vistas.
class View {

    public static function render($view, $data = []) {
        extract($data);

        // Seguridad básica (evitar rutas raras)
        $view = str_replace(['..', '//'], '', $view);

        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        $layoutPath = __DIR__ . '/../../views/layouts/main.php';

        if (!file_exists($viewPath)) {
            http_response_code(404);
            die("Vista no encontrada: $view");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require $layoutPath;
    }
}