<?php

require_once __DIR__ . '/../models/AuthModel.php';

class AuthService {
    private PDO $pdo;
    private AuthModel $authModel;


    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->authModel = new AuthModel($pdo);
    }

    public function register($data) {
        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if (!$name || !$email || !$password) throw new Exception('Datos incompletos');
        if (strlen($name) < 3) throw new Exception('El nombre es muy corto');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Correo inválido');
        if (strlen($password) < 8) throw new Exception('La contraseña debe tener al menos 8 caracteres');

        if ($this->authModel->emailExists($email)) {
            throw new Exception('El correo ya está registrado');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userExternalId = bin2hex(random_bytes(16));

        try {
            $this->authModel->createUser([
                'external_id' => $userExternalId,
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword
            ]);
        } catch (Exception $e) {
            throw new Exception('Error al registrar usuario en el sistema');
        }

        return true;
    }

    public function login($data) {
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if (!$email || !$password) throw new Exception('Datos incompletos');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Correo inválido');

        try {
            $user = $this->authModel->getUserByEmail($email);
        } catch (Exception $e) {
            throw new Exception('Error al iniciar sesión');
        }

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception('Credenciales inválidas');
        };
        unset($user['password']); // Para no regresar la contraseña

        return $user;
    }
}