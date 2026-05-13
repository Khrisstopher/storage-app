<?php

namespace App\Controllers\Web;

use App\Core\Controller;

require_once __DIR__ . '/../../core/Controller.php';

/**
 * Controlador de vista de administración de archivos del usuario.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class DashboardController extends Controller {
    
    public function dashboard() {
        $this->requireAuth();
        $this->view('dashboard', [
            'title' => 'Dashboard - Storage App',
            'styles' => 'css/files/dashboard.css',
            'scripts' => 'js/files/dashboard.js'
        ]);
    }
}