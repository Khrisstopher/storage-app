<?php

namespace App\Services;

use App\Models\AdminSettingModel;
use App\Helpers\FileHelper;

require_once __DIR__ . '/../models/AdminSettingModel.php';
require_once __DIR__ . '/../helpers/FileHelper.php';

/**
 * Servicio de administración de restricciones y permisos.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class AdminSettingService {
    private \PDO $pdo;
    private AdminSettingModel $adminModel;

    /**
     * Constructor del servicio de administración.
     * @param \PDO $pdo Conexión PDO para la base de datos.
     */
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
        $this->adminModel = new AdminSettingModel($pdo);
    }

    /**
     * Obtiene la lista de extensiones de archivos no permitidas.
     * * @return string[] Lista de extensiones bloqueadas (ej: ['exe', 'php', 'bat']).
     */
    public function getFileRestrictions(): array {
        $extensions = $this->adminModel->getFileRestrictions();
        if (!$extensions) { return []; };
        return array_map('strtolower', $extensions);
    }

    /**
     * Procesa, valida y guarda las nuevas restricciones de archivos.
     * * @param array $data Datos provenientes del formulario.
     * @return array La lista de extensiones que fueron procesadas y guardadas exitosamente.
     * @throws \Exception Si las validaciones de formato o longitud fallan.
     */
    public function updateFileRestrictions($data) {
        if (!isset($data['extensions'])) {
            throw new \Exception('No se recibieron extensiones.');
        }

        $extensions = trim($data['extensions']);
        if (empty($extensions)) {
            throw new \Exception('Debes ingresar al menos una extensión.');
        }

        $extensionsArray = explode(',', $extensions);
        $extensionsArray = array_values(array_unique(array_filter(
            array_map(fn($ext) => strtolower(trim($ext)), $extensionsArray)
        )));

        foreach ($extensionsArray as $ext) {
            if (!preg_match('/^[a-z0-9]+$/', $ext)) {
                throw new \Exception("La extensión '$ext' no es válida.");
            }
            if (strlen($ext) > 10) {
                throw new \Exception("La extensión '$ext' es demasiado larga.");
            }
        }

        $this->pdo->beginTransaction();

        try {
           $this->adminModel->clearBlockedExtensions();

           foreach ($extensionsArray as $ext) {
               $this->adminModel->insertBlockedExtension($ext);
           }

           $this->pdo->commit();
           return $extensionsArray;

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Obtiene el límite de cuota global convertido a Megabytes para la interfaz de admin.
     * @return int Límite en MB
     */
    public function getGlobalQuotaLimit(): int {
        $limitInBytes = $this->adminModel->getGlobalQuotaLimit();
        return FileHelper::bytesToMb($limitInBytes) ?? 0;
    }

    /**
     * Procesa, valida y guarda el nuevo límite de cuota global dentro de una transacción.
     * @param array $data Datos provenientes del formulario.
     * @throws \Exception|\Throwable
     */
    public function updateGlobalQuota($data) {
        if (!isset($data['limit']) || $data['limit'] === '') {
            throw new \Exception('No se recibió el límite de cuota global.');
        }

        if (!is_numeric($data['limit'])) { 
            throw new \Exception('El límite de cuota global debe ser un valor numérico válido.');
        }

        $limitMb = (int) $data['limit'];
        if ($limitMb <= 0) {
            throw new \Exception('El límite de cuota global debe ser un número positivo.');
        }

        $limitInBytes = FileHelper::mbToBytes($limitMb);

        try {
            $this->pdo->beginTransaction();

            $quotaId = $this->adminModel->getGlobalQuotaId();

            if ($quotaId) {
                $this->adminModel->updateQuotaBytes($quotaId, $limitInBytes);
            } else {
                $this->adminModel->createGlobalQuotaSetting($limitInBytes);
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

    /**
     * Obtiene el límite de cuota para un usuario específico en Megabytes (MB),
     * considerando su cuota personalizada, la del grupo al que pertenece o la cuota global del sistema.
     * @param int $userId ID del usuario.
     * @return int Límite en MB.
     */
    public function getUserQuotaLimit(int $userId): int {
        $quotaInBytes = null;

        $userQuota = $this->adminModel->getQuotaByUserId($userId);
        if ($userQuota !== null) {
            $quotaInBytes = $userQuota;
        } else {
            $groupQuota = $this->adminModel->getQuotaByGroupId($userId);
            if ($groupQuota !== null) {
                $quotaInBytes = $groupQuota;
            } else {
                $quotaInBytes = $this->adminModel->getGlobalQuotaLimit();
            }
        }
        return (int) ($quotaInBytes / 1024 / 1024);
    }
}