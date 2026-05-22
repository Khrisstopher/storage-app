<?php

namespace App\Models;

/**
 * Modelo para la gestión de usuarios.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class UserModel {
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los usuarios con sus respectivos grupos y los bytes de su cuota exclusiva.
     * @return array Lista de usuarios con detalles de grupo y bytes de cuota propia o null
     */
    public function getAllUsers() {
        $sql = "SELECT 
                    u.id, 
                    u.name, 
                    u.email, 
                    u.group_id,
                    g.name AS group_name,
                    uq.quota_bytes AS custom_quota_bytes
                FROM users u
                LEFT JOIN groups g ON u.group_id = g.id
                LEFT JOIN quotas uq ON u.quota_id = uq.id
                ORDER BY u.name ASC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los datos mínimos de un usuario para lógica de control
     * @param int $id
     * @return array|false
     */
    public function getUserQuotaDetails(int $id) {
        $stmt = $this->pdo->prepare("SELECT name, quota_id FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Registra una nueva cuota y retorna su ID
     */
    public function createQuota(string $name, int $bytes): int {
        $sql = "INSERT INTO quotas (name, quota_bytes, description) 
                VALUES (:name, :bytes, 'Límite exclusivo asignado al usuario.')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['name' => $name, 'bytes' => $bytes]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Actualiza una cuota existente
     */
    public function updateQuota(int $quotaId, int $bytes): void {
        $sql = "UPDATE quotas SET quota_bytes = :bytes WHERE id = :quota_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['bytes' => $bytes, 'quota_id' => $quotaId]);
    }

    /**
     * Elimina una cuota específica
     */
    public function deleteQuota(int $quotaId): void {
        $stmt = $this->pdo->prepare("DELETE FROM quotas WHERE id = :quota_id");
        $stmt->execute(['quota_id' => $quotaId]);
    }

    /**
     * Actualiza el grupo y la cuota de un usuario en el sistema
     */
    public function updateUserFields(int $id, ?int $groupId, ?int $quotaId): bool {
        $sql = "UPDATE users SET group_id = :group_id, quota_id = :quota_id WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'group_id' => $groupId,
            'quota_id' => $quotaId,
            'id' => $id
        ]);
    }
}