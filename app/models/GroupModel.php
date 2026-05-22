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

    /**
     * Crea una cuota y un grupo dentro de una transacción segura.
     * @param array $data Contiene 'name', 'quota_mb' y opcionalmente 'description'
     * @return array Datos combinados del registro creado.
     */
    public function createGroup(array $data) {
        try {
            $this->pdo->beginTransaction();

            $quotaMb = (int)$data['quota_mb'];
            $quotaBytes = $quotaMb * 1024 * 1024;

            // Insertar primero en la tabla `quotas`
            $quotaSql = "INSERT INTO quotas (name, quota_bytes, description) 
                         VALUES (:name, :quota_bytes, :description)";
            
            $quotaStmt = $this->pdo->prepare($quotaSql);
            $quotaStmt->execute([
                'name' => "Cuota Grupo: " . $data['name'],
                'quota_bytes' => $quotaBytes,
                'description' => "Límite asignado automáticamente al crear el grupo."
            ]);

            $quotaId = $this->pdo->lastInsertId();

            // Insertar en la tabla `groups` usando el quota_id obtenido
            $groupSql = "INSERT INTO groups (name, description, quota_id) 
                         VALUES (:name, :description, :quota_id)";
            
            $groupStmt = $this->pdo->prepare($groupSql);
            $groupStmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'quota_id' => $quotaId
            ]);

            $groupId = $this->pdo->lastInsertId();
            $this->pdo->commit();

            return [
                'id' => $groupId,
                'name' => $data['name'],
                'quota_id' => $quotaId,
                'quota_bytes' => $quotaBytes
            ];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Actualiza un grupo existente y su cuota asociada dentro de una transacción.
     * @param int $id ID del grupo a actualizar.
     * @param array $data Datos a actualizar ('name', 'quota_mb', 'description').
     * @return array Datos del grupo actualizado.
     */
    public function updateGroup($id, $data) {
        try {
            // Obtener el grupo actual para conocer su quota_id antes de modificar nada
            $stmtFetch = $this->pdo->prepare("SELECT quota_id FROM groups WHERE id = :id LIMIT 1");
            $stmtFetch->execute(['id' => $id]);
            $group = $stmtFetch->fetch(\PDO::FETCH_ASSOC);

            if (!$group) {
                throw new \Exception("El grupo especificado no existe.");
            }

            $quotaId = $group['quota_id'];

            $this->pdo->beginTransaction();

            $quotaMb = (int)$data['quota_mb'];
            $quotaBytes = $quotaMb * 1024 * 1024;

            $quotaSql = "UPDATE quotas 
                         SET name = :quota_name, quota_bytes = :quota_bytes 
                         WHERE id = :quota_id";
            
            $quotaStmt = $this->pdo->prepare($quotaSql);
            $quotaStmt->execute([
                'quota_name' => "Cuota Grupo: " . $data['name'],
                'quota_bytes' => $quotaBytes,
                'quota_id' => $quotaId
            ]);

            $groupSql = "UPDATE groups 
                         SET name = :name, description = :description 
                         WHERE id = :id";
            
            $groupStmt = $this->pdo->prepare($groupSql);
            $groupStmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'id' => $id
            ]);

            $this->pdo->commit();

            return [
                'id' => $id,
                'name' => $data['name'],
                'quota_id' => $quotaId,
                'quota_mb' => $quotaMb
            ];

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }


    /**
     * Elimina un grupo y su cuota asociada dentro de una transacción.
     * @param int $id ID del grupo a eliminar.
     * @return bool
     */
    public function deleteGroup($id) {
        $stmt = $this->pdo->prepare("DELETE FROM groups WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}