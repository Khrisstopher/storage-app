<?php

namespace App\Models;

/**
 * Modelo para la gestión de grupos.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class GroupModel {
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los grupos con sus respectivas descripciones y cuotas
     * @return array Lista de grupos con detalles de cuota
     */
    public function getAllGroups() {
        $sql = "SELECT 
                    g.id, 
                    g.name, 
                    g.description, 
                    q.quota_bytes 
                FROM groups g
                LEFT JOIN quotas q ON g.quota_id = q.id 
                ORDER BY g.name";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca un grupo por su nombre para validaciones previas.
     * @param string $name Nombre del grupo a buscar.
     * @return array|null Datos del grupo encontrado o null si no existe.
     */
    public function getByName(string $name) {
        $stmt = $this->pdo->prepare("SELECT * FROM groups WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    #### FUNCIONES DE CREACIÓN ####

    /**
     * Inserta una nueva cuota en la tabla quotas.
     * @param string $name Nombre descriptivo de la cuota.
     * @param int $bytes Límite máximo en bytes.
     * @param string|null $description Descripción de la cuota.
     * @return int ID de la cuota generada.
     */
    public function insertQuota(string $name, int $bytes, ?string $description = null): int {
        $sql = "INSERT INTO quotas (name, quota_bytes, description) 
                VALUES (:name, :quota_bytes, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'quota_bytes' => $bytes,
            'description' => $description
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Inserta un nuevo grupo en la tabla groups.
     * @param string $name Nombre único del grupo.
     * @param int $quotaId ID de la cuota asociada.
     * @param string|null $description Descripción del grupo.
     * @return int ID del grupo generado.
     */
    public function insertGroupDetails(string $name, int $quotaId, ?string $description = null): int {
        $sql = "INSERT INTO groups (name, quota_id, description) 
                VALUES (:name, :quota_id, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'quota_id' => $quotaId,
            'description' => $description
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    #### FUNCIONES DE ACTUALIZACIÓN  ####

    /**
     * Obtiene un grupo por su ID junto con su quota_id.
     * @param int $id ID del grupo.
     * @return array|null
     */
    public function getGroupById($id) {
        $stmt = $this->pdo->prepare("SELECT id, name, quota_id, description FROM groups WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Actualiza la cuota asociada al grupo.
     * @param int $quotaId ID de la cuota a actualizar.
     * @param string $name Nombre de la cuota.
     * @param int $bytes Límite máximo en bytes.
     * @return bool
     */
    public function updateQuota($quotaId, $name, $bytes) {
        $sql = "UPDATE quotas SET name = :name, quota_bytes = :bytes WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'name' => $name,
            'bytes' => $bytes,
            'id' => $quotaId
        ]);
    }

    /**
     * Actualiza los datos básicos del grupo.
     * @param int $id ID del grupo.
     * @param string $name Nuevo nombre del grupo.
     * @param string|null $description Nueva descripción.
     * @return bool
     */
    public function updateGroupDetails($id, $name, $description) {
        $sql = "UPDATE groups SET name = :name, description = :description WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'name' => $name,
            'description' => $description,
            'id' => $id
        ]);
    }

    #### FUNCIONES DE ELIMINACIÓN ####

    /**
     * Obtiene el ID de la cuota asociada a un grupo.
     * @param int $id ID del grupo.
     * @return int|null
     */
    public function getQuotaIdByGroupId($id) {
        $stmt = $this->pdo->prepare("SELECT quota_id FROM groups WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? (int)$result['quota_id'] : null;
    }

    /**
     * Elimina un grupo por su ID.
     * @param int $id ID del grupo a eliminar.
     * @return bool
     */
    public function deleteGroup($id) {
        $stmt = $this->pdo->prepare("DELETE FROM groups WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Elimina una cuota específica de la tabla quotas.
     * @param int $quotaId ID de la cuota a eliminar.
     * @return bool
     */
    public function deleteQuota($quotaId) {
        $stmt = $this->pdo->prepare("DELETE FROM quotas WHERE id = :quotaId");
        return $stmt->execute(['quotaId' => $quotaId]);
    }
}