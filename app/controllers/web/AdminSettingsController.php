<?php

require_once __DIR__ . '/../../core/Controller.php';

class AdminSettingsController extends Controller {

    // Página de configuración de administración
    public function index() {

        $this->requireAuth(); // Solo usuarios autenticados

        $this->view('admin/settings', [
            'title' => 'Admin Settings - Storage App',
            'styles' => 'css/admin/settings.css',
            'user_name' => $_SESSION['user_name'] ?? 'Usuario',
            'scripts' => 'js/admin/settings.js'
        ]);
    }
}