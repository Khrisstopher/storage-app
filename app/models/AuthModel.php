<?php

namespace App\Models;

/**
 * Modelo de autenticación y sesiones.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class AuthModel {
    private \PDO $pdo;

    /**
     * @param \PDO $pdo Instancia de conexión a la base de datos.
     */
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Verificar si un email ya existe.
     * @param string $email El email a verificar.
     * @return bool Retorna true si el email existe, false si no.
     */
    public function emailExists($email) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }

    /**
     * Insertar el nuevo usuario.
     * @param array $data Datos del usuario a insertar.
     * @return bool Retorna true si la inserción fue exitosa, false si no
     */
    public function createUser($data) {
        $stmt = $this->pdo->prepare("INSERT INTO users (external_id, name, email, password, role_id) 
                                    VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['external_id'],
            $data['name'],
            $data['email'],
            $data['password'],
            2 // Rol para usuario normal
        ]);
    }

    /**
     * Obtener usuario por email.
     * @param string $email El email del usuario a obtener.
     * @return array|false Retorna los datos del usuario si se encuentra, false si
     */
    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT id, name, email, password, role_id 
                                    FROM users 
                                    WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}