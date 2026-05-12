<?php

require_once __DIR__ . '/../../core/Controller.php';

class AdminSettingsController extends Controller {

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