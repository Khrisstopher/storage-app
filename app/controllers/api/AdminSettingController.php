<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Services\AdminSettingService;

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../services/AdminSettingService.php';

/**
 * Controlador de administrador de restricciones y permisos.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class AdminSettingController extends Controller {

    private AdminSettingService $settingsService;

    public function __construct(\PDO $pdo) {
        $this->settingsService = new AdminSettingService($pdo);
    }

    public function fileRestrictions() {
        try {
            $this->requireAdmin();
            $restrictions = $this->settingsService->getFileRestrictions();
            $this->response(true, 'Restricciones de archivos obtenidas', $restrictions);
        } catch (\Throwable $e) {
            $this->logError($e, "GET_FILE_RESTRICTIONS");
        }
    }

    public function saveFileRestrictions() {
        try {

            $this->requireAdmin();
            $data = $this->getRequestData();

            if (!isset($data['extensions'])) {
                throw new \Exception('No se recibieron extensiones.');
            }

            $extensions = [
                'extensions' => $data['extensions']
            ];

            $result = $this->settingsService->updateFileRestrictions($extensions);
            $this->response(true, 'Extensiones restringidas actualizadas correctamente.', $result);

        } catch (\Throwable $e) {
            $this->logError($e, "FILE_RESTRICTIONS");
        }
    }

    public function getQuotaGlobalLimit() {
        try {
            $this->requireAdmin();
            $limit = $this->settingsService->getGlobalQuotaLimit();
            $this->response(true, 'Límite global de cuota obtenido', $limit);
        } catch (\Throwable $e) {
            $this->logError($e, "GET_GLOBAL_QUOTA_LIMIT");
        }
    }

    public function saveQuotaGlobalLimit() {
        try {
            $this->requireAdmin();
            $data = $this->getRequestData();

            $result = $this->settingsService->updateGlobalQuota($data);
            $this->response(true, 'Límite global actualizado', $result);
        } catch (\Throwable $e) {
            $this->logError($e, "GLOBAL_QUOTA");
        }
    }
}