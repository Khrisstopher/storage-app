<?php

namespace App\Services;

use App\Models\AuthModel;

require_once __DIR__ . '/../models/AuthModel.php';

/**
 * Servicio de autenticación y sesión.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class AuthService {
    private \PDO $pdo;
    private AuthModel $authModel;

    /**
     * @param \PDO $pdo Instancia de conexión a la base de datos y carga el servicio de autennticación.
     */
    public function __construct(\PDO $pdo) {
        $this->authModel = new AuthModel($pdo);
    }

    /**
     * Valida el email.
     * @throws \Exception
     */
    private function validateEmail($email){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \Exception('Correo inválido');
    }

    /**
     * Valida, procesa y registra un nuevo usuario en el sistema.
     * * @param array $data Información del formulario (name, email, password).
     * @return bool True si el registro fue exitoso.
     * @throws \Exception Si las validaciones fallan o el email ya existe.
     */
    public function register($data) {
        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if (!$name || !$email || !$password) throw new \Exception('Datos incompletos');
        if (strlen($name) < 3) throw new \Exception('El nombre es muy corto');
        $this->validateEmail($email);
        if (strlen($password) < 8) throw new \Exception('La contraseña debe tener al menos 8 caracteres');

        if ($this->authModel->emailExists($email)) throw new \Exception('El correo ya está registrado');

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userExternalId = bin2hex(random_bytes(16));

        $this->authModel->createUser([
            'external_id' => $userExternalId,
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword
        ]);
        return true;
    }

    /**
     * Valida las credenciales e inicia sesión de un usuario.
     * * @param array $data Información del formulario (email, password).
     * @return array Datos del usuario autenticado (id, name, email, role_id).
     * @throws \Exception Si los datos son incompletos, el formato es inválido o las credenciales no coinciden.
     */
    public function login($data) {
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if (!$email || !$password) throw new \Exception('Datos incompletos');
        $this->validateEmail($email);

        $user = $this->authModel->getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            throw new \Exception('Credenciales inválidas');
        };
        unset($user['password']); // Para no regresar la contraseña

        return $user;
    }
}