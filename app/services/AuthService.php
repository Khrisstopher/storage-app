<?php
// Métodos de autenticación: register, login, logout, me (obtener info del usuario autenticado).

class AuthService {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function register($data) {
        
        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        // Validación básica
        if (!$name || !$email || !$password) {
            return [
                'status' => false,
                'msg' => 'Datos incompletos'
            ];
        }

        // Verificar si el usuario ya existe
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            return [
                'status' => false,
                'msg' => 'El correo ya está registrado'
            ];
        }

        // Encriptar contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insertar usuario
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role_id) 
                                    VALUES (?, ?, ?, ?)");

        $success = $stmt->execute([$name, $email, $hashedPassword, 2]);

        if (!$success) {
            return [
                'status' => false,
                'msg' => 'Error al registrar usuario'
            ];
        }

        return [
            'status' => true,
            'msg' => 'Registro exitoso'
        ];
    }

    public function login($data) {
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return [
                'status' => false,
                'msg' => 'Datos incompletos'
            ];
        };

        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            return [
                'status' => false,
                'msg' => 'Credenciales inválidas'
            ];
        };

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];

        return [
            'status' => true, 
            'msg' => 'Login exitoso'
        ];
    }

    public function logout() {
        session_destroy();
        return ['success' => 'Logout exitoso'];
    }

    public function me() {
        if (!isset($_SESSION['user_id'])) {
            return ['error' => 'No autenticado'];
        }

        return [
            'user_id' => $_SESSION['user_id'],
            'role_id' => $_SESSION['role_id']
        ];
    }
}