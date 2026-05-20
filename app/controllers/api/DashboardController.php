<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Services\DashboardService;
use App\Core\Session;

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../services/DashboardService.php';
require_once __DIR__ . '/../../core/Session.php';

/**
 * Controlador de panel de control.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class DashboardController extends Controller {
    private DashboardService $dashboardService;

    public function __construct(\PDO $pdo) {
        $this->dashboardService = new DashboardService($pdo);
    }
    
    public function list() {
        try {
            $this->requireAuth();

            $userId = Session::userId();

            $files = $this->dashboardService->listByUser($userId);

            $this->response(true, 'Archivos consultados con exito.', $files);
        } catch (\Throwable $e) {
            $this->logError($e, "LIST_ARCHIVOS");
        }
    }

    public function upload() {
        try {
            $this->requireAuth();

            if (!isset($_FILES['file'])) {
                throw new \Exception('No se recibió ningún archivo');
            }

            $userId = Session::userId();

            $result = $this->dashboardService->upload($_FILES['file'], $userId);

            $this->response(true, 'Archivo subido correctamente', $result);

        } catch (\Throwable $e) {
            $this->logError($e, "UPLOAD_ARCHIVO");
        }
    }

    public function delete() {
        try {
            $this->requireAuth();

            if (!isset($_POST['id'])) {
                throw new \Exception('ID no recibido');
            }

            $userId = Session::userId();

            $this->dashboardService->delete((int)$_POST['id'], $userId);

            $this->response(true, 'Archivo eliminado correctamente');

        } catch (\Throwable $e) {
            $this->logError($e, "DELETE_ARCHIVO");
        }
    }

    public function download() {
        try {

            $this->requireAuth();

            $fileId = $_GET['id'] ?? null;
            if (!$fileId) {
                throw new \Exception('ID de archivo requerido');
            }

            $file = $this->dashboardService->getFileForDownload($fileId, Session::userId());

            if (!$file) {
                throw new \Exception('Archivo no encontrado o acceso denegado');
            }

            // Construir ruta absoluta
            $filePath = $file['absolute_path'];

            if (!file_exists($filePath)) {
                throw new \Exception('El archivo físico no existe en el servidor');
            }

            // Configuración de Headers para descarga
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream'); 
            header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            
            // Limpiamos el buffer para asegurar que no haya basura en el archivo
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            flush();
            
            // Enviamos el archivo al navegador
            if (readfile($filePath) === false) {
                throw new \Exception("Error al leer el archivo para la descarga.");
            }
            
            exit;

        } catch (\Throwable $e) {
            $this->logError($e, "DOWNLOAD_FILE");
        }
    }
}