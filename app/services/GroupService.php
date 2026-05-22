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
     * Crea un nuevo grupo con los datos proporcionados.
     * @param array $data Datos del grupo a crear.
     * @return array Datos del grupo creado.
     */
    public function createGroup($data) {
        $this->validateGroupData($data);
        $name = trim($data['name'] ?? '');
        $quotaMb = $data['quota_mb'] ?? 10485760; // 10 MB por defecto

        $existing = $this->groupModel->getByName($name);
        if ($existing) {
            throw new \Exception("El grupo '{$name}' ya existe");
        }

        return $this->groupModel->createGroup([
            'name' => $name, 
            'quota_mb' => $quotaMb,
            'description' => trim($data['description'] ?? '')
        ]);
    }

    /**
     * Actualiza un grupo existente con los datos proporcionados.
     * @param array $data Datos del grupo a actualizar (debe incluir 'id').
     * @return array Datos del grupo actualizado.
     */
    public function updateGroup($data) {
        $this->validateGroupData($data);
        $name = trim($data['name'] ?? '');
        $quotaMb = $data['quota_mb'] ?? 10485760; // 10 MB por defecto
        
        $id = isset($data['id']) ? (int)$data['id'] : null;

        $existing = $this->groupModel->getByName($name);
        
        if ($existing && (int)$existing['id'] !== $id) {
            throw new \Exception("Ya existe otro grupo registrado con el nombre '{$name}'");
        }

        return $this->groupModel->updateGroup(
            $id, 
            [
                'name' => $name, 
                'quota_mb' => $quotaMb,
                'description' => trim($data['description'] ?? '')
            ]
        );
    }

    /**
     * Elimina un grupo existente.
     * @param int $id ID del grupo a eliminar.
     * @return bool Indica si la eliminación fue exitosa.
     */
    public function deleteGroup($id) {
        return $this->groupModel->deleteGroup($id);
    }
}