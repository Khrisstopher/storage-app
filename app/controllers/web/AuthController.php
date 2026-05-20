<?php

namespace App\Controllers\Web;

use App\Core\Controller;

require_once __DIR__ . '/../../core/Controller.php';

/**
 * Controlador de vista de autenticación y Home.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class AuthController extends Controller {

    // Página de entrada
    public function home() {
        $this->requireGuest();
        $this->view('home', [
            'title' => 'Inicio - Storage App',
            'styles' => 'css/auth/home.css'
        ]);
    }

    // Página de login
    public function login() {
        $this->requireGuest();
        $this->view('login', [
            'title' => 'Iniciar sesión - Storage App',
            'styles' => 'css/auth/login.css',
            'scripts' => 'js/auth/login.js'
        ]);
    }

    // Página de registro
    public function register() {
        $this->requireGuest();
        $this->view('register', [
            'title' => 'Regístro - Storage App',
            'styles' => 'css/auth/register.css',
            'scripts' => 'js/auth/register.js'
        ]);
    }
}