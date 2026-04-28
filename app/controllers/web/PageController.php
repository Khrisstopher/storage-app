<?php

require_once __DIR__ . '/../../core/Controller.php';

class PageController extends Controller {

    // Página de entrada
    public function home() {
        $this->view('home', [
            'title' => 'Inicio - Storage App',
            'styles' => 'css/auth/home.css'
        ]);
    }

    // Página de login
    public function login() {
        $this->view('login', [
            'title' => 'Iniciar sesión - Storage App',
            'styles' => 'css/auth/login.css',
            'scripts' => 'js/auth/login.js'
        ]);
    }

    // Página de registro
    public function register() {
        $this->view('register', [
            'title' => 'Regístro - Storage App',
            'styles' => 'css/auth/register.css',
            'scripts' => 'js/auth/register.js'
        ]);
    }
}