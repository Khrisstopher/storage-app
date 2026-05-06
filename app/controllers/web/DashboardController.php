<?php 
require_once __DIR__ . '/../../core/Controller.php';

class DashboardController extends Controller {

    // Página de dashboard (gestión de archivos)
    public function dashboard() {

        $this->requireAuth(); // Solo usuarios autenticados

        $this->view('dashboard', [
            'title' => 'Dashboard - Storage App',
            'styles' => 'css/files/dashboard.css',
            'user_name' => $_SESSION['user_name'] ?? 'Usuario',
            'scripts' => [
                'js/files/dashboard.js',
                'js/auth/auth.js'
            ]
        ]);
    }
}