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
     * @throws \PDOException
     */
    public function emailExists($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch() !== false;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Insertar el nuevo usuario.
     * @throws \PDOException
     */
    public function createUser($data) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (external_id, name, email, password, role_id) 
                                        VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([
                $data['external_id'],
                $data['name'],
                $data['email'],
                $data['password'],
                2 // Rol para usuario normal
            ]);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Obtener usuario por email.
     * @throws \PDOException
     */
    public function getUserByEmail($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name, email, password, role_id 
                                        FROM users 
                                        WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw $e;
        }
    }
}