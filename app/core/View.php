<?php

namespace App\Core;

/**
 * Archivo encargado de renderizar vistas con un layout común.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class View {

    public static function render($view, $data = []) {
        $view = str_replace(['..', '//'], '', $view);

        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        $layoutPath = __DIR__ . '/../../views/layouts/main.php';

        if (!file_exists($viewPath)) {
            http_response_code(404);
            die("Vista no encontrada: $view");
        }

        ob_start();
        
        // Extracción segura
        extract($data, EXTR_SKIP);

        require $viewPath;
        $content = ob_get_clean();

        require $layoutPath;
    }
}