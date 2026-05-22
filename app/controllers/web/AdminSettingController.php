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

    // Pestaña de configuración global
    public function index() {
        $this->requireAdmin();
        $this->view('layouts/admin', [
            'title' => 'Configuraciones Globales - Storage App',
            'subView' => 'admin/global_settings',
            'activeTab' => 'global',
            'styles' => 'css/admin/settings.css',
            'scripts' => 'js/admin/global_settings.js'
        ]);
    }

    // Gestion de Grupos
    public function groups() {
        $this->requireAdmin();
        $this->view('layouts/admin', [
            'title'       => 'Gestión de Grupos - Storage App',
            'subView'     => 'admin/groups',
            'activeTab'   => 'groups',
            'styles'      => 'css/admin/settings.css',
            'scripts'     => 'js/admin/groups.js' // Solo carga el JS específico de grupos
        ]);
    }

    // Administracion de Usuarios
    public function users() {
        $this->requireAdmin();
        $this->view('layouts/admin', [
            'title'       => 'Administración de Usuarios - Storage App',
            'subView'     => 'admin/users',
            'activeTab'   => 'users',
            'styles'      => 'css/admin/settings.css',
            'scripts'     => 'js/admin/users.js' // Solo carga el JS específico de usuarios
        ]);
    }
}