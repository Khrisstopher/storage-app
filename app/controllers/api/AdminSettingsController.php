<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../services/AdminSettingsService.php';

class AdminSettingsController extends Controller {

    private AdminSettingsService $settingsService;

    public function __construct($pdo) {
        $this->settingsService = new AdminSettingsService($pdo);
    }

    public function fileRestrictions() {
        try {
            $this->requireAdmin();
            $restrictions = $this->settingsService->getFileRestrictions();
            $this->response(true, 'Restricciones de archivos obtenidas', $restrictions);
        } catch (Exception $e) {
            $this->logError($e, "GET_FILE_RESTRICTIONS");
        }
    }

    public function saveFileRestrictions() {
        try {

            $this->requireAdmin();

            $data = $this->getRequestData();

            if (!isset($data['extensions'])) {
                throw new Exception('No se recibieron extensiones.');
            }

            $extensions = [
                'extensions' => $data['extensions']
            ];

            $result = $this->settingsService->updateFileRestrictions($extensions);

            $this->response(
                true,
                'Extensiones restringidas de archivos actualizadas correctamente.',
                $result
            );

        } catch (Exception $e) {
            $this->logError($e, "FILE_RESTRICTIONS");
        }
    }

    public function updateGlobalQuota() {
        try {
            $this->requireAdmin();
            $data = $this->getRequestData();

            $result = $this->settingsService->updateGlobalQuota($data);

            $this->response(true, 'Límite global actualizado', $result);

        } catch (Exception $e) {
            $this->logError($e, "GLOBAL_QUOTA");
        }
    }
}