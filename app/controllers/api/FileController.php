<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../services/FileService.php';

class FileController extends Controller {
    private FileService $fileService;

    public function __construct($pdo) {
        $this->fileService = new FileService($pdo);
    }
    
    public function list() {
        try {
            $this->requireAuth();

            $userId = $_SESSION['user_id'];

            $files = $this->fileService->listByUser($userId);

            $this->response(true, 'Archivos consultados con exito.', $files);
        } catch (Exception $e) {
            $this->logError($e, "LIST_ARCHIVOS");
        }
    }

    public function upload() {
        try {
            $this->requireAuth();

            if (!isset($_FILES['file'])) {
                throw new Exception('No se recibió ningún archivo');
            }

            $userId = $_SESSION['user_id'];

            $result = $this->fileService->upload($_FILES['file'], $userId);

            $this->response(true, 'Archivo subido correctamente', $result);

        } catch (Exception $e) {
            $this->logError($e, "UPLOAD_ARCHIVO");
        }
    }

    public function delete() {
        try {
            $this->requireAuth();

            if (!isset($_POST['id'])) {
                throw new Exception('ID no recibido');
            }

            $userId = $_SESSION['user_id'];

            $this->fileService->delete((int)$_POST['id'], $userId);

            $this->response(true, 'Archivo eliminado correctamente');

        } catch (Exception $e) {
            $this->logError($e, "DELETE_ARCHIVO");
        }
    }

    public function download() {
        try {

            $this->requireAuth();

            $fileId = $_GET['id'] ?? null;
            if (!$fileId) {
                throw new Exception('ID de archivo requerido');
            }

            $file = $this->fileService->getFileForDownload($fileId, $_SESSION['user_id']);

            if (!$file) {
                throw new Exception('Archivo no encontrado o acceso denegado');
            }

            // 3. Construir ruta absoluta (fuera de public)
            $filePath = __DIR__ . "/../../../storage/uploads/" . $file['external_id'] . "/" . $file['filename'];

            if (!file_exists($filePath)) {
                throw new Exception('El archivo físico no existe en el servidor');
            }

            // 4. Configuración de Headers para descarga
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
                throw new Exception("Error al leer el archivo para la descarga.");
            }
            
            exit;

        } catch (Exception $e) {
            $this->logError($e, "DOWNLOAD_FILE");
        }
    }
}