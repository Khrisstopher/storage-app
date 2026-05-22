<?php

namespace App\Services;

use App\Models\UserModel;
use App\Helpers\FileHelper;

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../helpers/FileHelper.php';

/**
 * Servicio para manejar operaciones relacionadas con los usuarios.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class UserService {

    private UserModel $userModel;

    public function __construct(\PDO $pdo) {
        $this->userModel = new UserModel($pdo);
    }

    /**
     * Obtiene la lista de usuarios con sus respectivos grupos y su cuota exclusiva convertida a MB.
     * @return array Lista de usuarios con detalles de grupo y cuota individual en MB o null
     */
    public function getUsers() {
        $users = $this->userModel->getAllUsers();

        foreach ($users as &$user) {
            $user['custom_quota_mb'] = FileHelper::bytesToMb($user['custom_quota_bytes']);
            unset($user['custom_quota_bytes']);
        }

        return $users;
    }

    /**
     * Procesa la actualización del usuario gestionando sus cuotas y transacciones
     * @param array $data Datos del usuario a actualizar, incluyendo id, group_id y custom_quota_mb
     * @return bool Resultado de la operación
     */
    public function updateUser(int $id, array $data) {
        $currentUser = $this->userModel->getUserQuotaDetails($id);
        if (!$currentUser) {
            throw new \Exception("El usuario especificado no existe.");
        }

        $currentQuotaId = $currentUser['quota_id'] ? (int)$currentUser['quota_id'] : null;
        $groupId = !empty($data['group_id']) ? (int)$data['group_id'] : null;
        $quotaMb = isset($data['custom_quota_mb']) ? $data['custom_quota_mb'] : '';

        try {
            $this->pdo->beginTransaction();
            $newQuotaId = $currentQuotaId;

            if ($quotaMb !== '') {
                $quotaBytes = FileHelper::mbToBytes($quotaMb);

                if ($currentQuotaId === null) {
                    // Crear cuota
                    $quotaName = "Cuota Usuario: " . $currentUser['name'];
                    $newQuotaId = $this->userModel->createQuota($quotaName, $quotaBytes);
                } else {
                    // Actualizar cuota
                    $this->userModel->updateQuota($currentQuotaId, $quotaBytes);
                }
            } else {
                // ESCENARIO C: Remover cuota
                $newQuotaId = null;
            }

            // Actualizar el usuario
            $this->userModel->updateUserFields($id, $groupId, $newQuotaId);

            // Limpieza si aplica
            if ($currentQuotaId !== null && $newQuotaId === null) {
                $this->userModel->deleteQuota($currentQuotaId);
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