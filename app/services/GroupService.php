<?php

namespace App\Services;

use App\Models\GroupModel;
use App\Helpers\FileHelper;

require_once __DIR__ . '/../models/GroupModel.php';
require_once __DIR__ . '/../helpers/FileHelper.php';

/**
 * Servicio para la gestión de grupos.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class GroupService {
    private \PDO $pdo;
    private GroupModel $groupModel;

    /**
     * Constructor del servicio de grupos.
     * @param \PDO $pdo Conexión PDO para la base de datos.
     */
    public function __construct(\PDO $pdo) {
        $this->groupModel = new GroupModel($pdo);
        $this->pdo = $pdo;
    }

    /**
     * Valida los datos de un grupo antes de crear o actualizar.
     * @param array $data Datos del grupo a validar.
     */
    private function validateGroupData($data) {
        $name = trim($data['name'] ?? '');
        $quotaMb = $data['quota_mb'] ?? null;

        if (!$name) throw new \Exception('El nombre del grupo es requerido');
        if (strlen($name) < 4) throw new \Exception('El nombre del grupo es muy corto');
        if ($quotaMb === null || (!is_numeric($quotaMb) || $quotaMb < 0)) {
            throw new \Exception('La cuota debe ser un número positivo');
        }
    }

    /**
     * Obtiene la lista de grupos con su cuota en MB.
      * @return array Lista de grupos con cuota en MB.
     */
    public function getGroups() {
        $groups = $this->groupModel->getAllGroups();
        foreach ($groups as &$group) {
            $group['quota_mb'] = FileHelper::bytesToMb($group['quota_bytes']);
        }
        unset($group);
        return $groups;
    }

    /**
     * Crea un nuevo grupo con su cuota correspondiente dentro de una transacción segura.
     * @param array $data Datos del grupo a crear (debe incluir 'name', 'quota_mb').
     * @return array Datos combinados del registro creado listos para la capa superior.
     * @throws \Exception|\Throwable
     */
    public function createGroup($data) {
        $this->validateGroupData($data);
        
        $name = trim($data['name'] ?? '');
        $quotaMb = isset($data['quota_mb']) ? (int)$data['quota_mb'] : 10; // Fallback seguro a 10MB
        $description = isset($data['description']) ? trim($data['description']) : null;

        $existing = $this->groupModel->getByName($name);
        if ($existing) {
            throw new \Exception("El grupo '{$name}' ya existe.");
        }

        try {
            $this->pdo->beginTransaction();

            $quotaBytes = FileHelper::mbToBytes($quotaMb);
            $quotaDescription = "Límite asignado automáticamente al crear el grupo.";

            $quotaId = $this->groupModel->insertQuota("Cuota Grupo: " . $name, $quotaBytes, $quotaDescription);

            $groupId = $this->groupModel->insertGroupDetails($name, $quotaId, $description);

            $this->pdo->commit();

            return [
                'id' => $groupId,
                'name' => $name,
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
     * @param array $data Datos enviados desde el formulario/controlador (debe incluir 'id', 'name', 'quota_mb').
     * @return array Datos del grupo actualizado listos para responder.
     * @throws \Exception|\Throwable
     */
    public function updateGroup($data) {
        $this->validateGroupData($data);
        
        $id = isset($data['id']) ? (int)$data['id'] : null;
        if (!$id || $id <= 0) {
            throw new \Exception("ID de grupo inválido.");
        }

        $name = trim($data['name'] ?? '');
        $quotaMb = isset($data['quota_mb']) ? (int)$data['quota_mb'] : 10; // Fallback seguro a 10MB
        $description = isset($data['description']) ? trim($data['description']) : null;

        $currentGroup = $this->groupModel->getGroupById($id);
        if (!$currentGroup) {
            throw new \Exception("El grupo especificado no existe.");
        }

        $existing = $this->groupModel->getByName($name);
        if ($existing && (int)$existing['id'] !== $id) {
            throw new \Exception("Ya existe otro grupo registrado con el nombre '{$name}'");
        }

        try {
            $this->pdo->beginTransaction();

            $quotaBytes = FileHelper::mbToBytes($quotaMb);
            $quotaId = $currentGroup['quota_id'];

            $quotaUpdated = $this->groupModel->updateQuota($quotaId, "Cuota Grupo: " . $name, $quotaBytes);
            if (!$quotaUpdated) {
                throw new \Exception("No se pudo actualizar la cuota en el sistema.");
            }

            $groupUpdated = $this->groupModel->updateGroupDetails($id, $name, $description);
            if (!$groupUpdated) {
                throw new \Exception("No se pudieron actualizar los detalles del grupo.");
            }

            $this->pdo->commit();

            return [
                'id' => $id,
                'name' => $name,
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
     * Elimina un grupo existente y su cuota asociada dentro de una transacción.
     * @param int $id ID del grupo a eliminar.
     * @return bool Indica si la eliminación fue exitosa.
     */
    public function deleteGroup($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                throw new \Exception("ID de grupo inválido");
            }
            $id = (int)$id;
            
            $this->pdo->beginTransaction(); 
            $quotaId = $this->groupModel->getQuotaIdByGroupId($id);

            if (!$quotaId) {
                throw new \Exception("El grupo no existe o no tiene una cuota asociada.");
            }

            $groupDeleted = $this->groupModel->deleteGroup($id);
            if (!$groupDeleted) {
                throw new \Exception("No se pudo eliminar el grupo.");
            }

            $quotaDeleted = $this->groupModel->deleteQuota($quotaId);
            if (!$quotaDeleted) {
                throw new \Exception("No se pudo eliminar la cuota asociada al grupo.");
            }

            $this->pdo->commit();
            return true;

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}