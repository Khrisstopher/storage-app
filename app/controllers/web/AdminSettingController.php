<?php

namespace App\Controllers\Web;

use App\Core\Controller;

require_once __DIR__ . '/../../core/Controller.php';

/**
 * Controlador de vista de configuración de permisos y restricciones.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class AdminSettingController extends Controller {

    // Página de configuración de administración
    public function index() {
        $this->requireAdmin();
        $this->view('admin/settings', [
            'title' => 'Admin Settings - Storage App',
            'styles' => 'css/admin/settings.css',
            'scripts' => 'js/admin/settings.js'
        ]);
    }
}