<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../services/FileService.php';

class FileController extends Controller {
    private FileService $fileService;

    public function __construct($pdo) {
        $this->fileService = new FileService($pdo);
    }

    //  Listar archivos del usuario
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

    // Subir nuevo archivo
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
}