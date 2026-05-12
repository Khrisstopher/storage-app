<?php

class AuthModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Verificar si un email ya existe
    public function emailExists($email) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }

    // Insertar el nuevo usuario
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

    // app/models/AuthModel.php
    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT id, name, email, password, role_id 
                                    FROM users 
                                    WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}