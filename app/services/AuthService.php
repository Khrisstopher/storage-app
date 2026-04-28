<?php
// Métodos de autenticación: register, login.

class AuthService {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function register($data) {

        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if (!$name || !$email || !$password) {
            throw new Exception('Datos incompletos');
        }

        if (strlen($name) < 3) {
            throw new Exception('El nombre es muy corto');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Correo inválido');
        }

        if (strlen($password) < 8) {
            throw new Exception('La contraseña debe tener al menos 8 caracteres');
        }

        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            throw new Exception('El correo ya está registrado');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try{
            $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role_id) 
                                        VALUES (?, ?, ?, ?)");

            $stmt->execute([$name, $email, $hashedPassword, 2]);
        } catch (PDOException $e) {
            throw new Exception('Error al registrar usuario');
        }

        return [];
    }

    public function login($data) {
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            throw new Exception('Datos incompletos');
        };

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Correo inválido');
        }

        $stmt = $this->pdo->prepare("SELECT id, name, email, password, role_id 
                                    FROM users 
                                    WHERE email = ?");

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception('Credenciales inválidas');
        };

        return $user;
    }
}