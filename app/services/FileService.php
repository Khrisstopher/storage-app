<?php
/**
 * Archivo: app/services/FileService.php
 * Descripción: Clase con métodos para subir, listar, borrar y descargar archivos.
 * Autor: @KhrisstopherTube
 */
require_once __DIR__ . '/../models/FileModel.php';
require_once __DIR__ . '/handlers/StorageHandler.php';
require_once __DIR__ . '/../helpers/FileHelper.php';

class FileService {
    private PDO $pdo;
    private FileModel $fileModel;
    private StorageHandler $storage;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->fileModel = new FileModel($pdo);
        $this->storage = new StorageHandler();
    }

    private function resolveOriginalName(string $originalName, int $userId): string {
        $info = pathinfo($originalName);
        $basename = $info['filename'];
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
        
        $finalName = $originalName;
        $counter = 1;

        // Bucle: Mientras el nombre exista en la tabla para ese user_id, incrementamos
        while ($this->fileModel->originalNameExists($finalName, $userId)) {
            $finalName = $basename . " ($counter)" . $extension;
            $counter++;
        }

        return $finalName;
    }

    private function validateQuota(int $userId, int $fileSize): void {
        $used = $this->fileModel->getTotalSizeByUser($userId);
        
        // Definimos la regla de negocio (Límite de 10MB)
        $limit = 10 * 1024 * 1024; 

        if (($used + $fileSize) > $limit) {
            $available = ($limit - $used) / (1024 * 1024);
            $availableFormatted = number_format($available, 2);
            
            throw new Exception("Cuota excedida. Solo te quedan {$availableFormatted} MB disponibles.");
        }
    }
    
    private function validateZip(string $tmpPath, array $blockedExtensions): void {

        if (!class_exists('ZipArchive')) {
            throw new Exception('El servidor no tiene habilitada la función para revisar archivos ZIP.');
        }

        $zip = new ZipArchive();

        if ($zip->open($tmpPath) !== TRUE) {
            throw new Exception('No se pudo analizar el archivo ZIP');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileInside = $zip->getNameIndex($i);
            $extInside = strtolower(pathinfo($fileInside, PATHINFO_EXTENSION));

            if (in_array($extInside, $blockedExtensions)) {
                $zip->close();
                throw new Exception("El archivo '$fileInside' dentro del ZIP no está permitido");
            }
        }

        $zip->close();
    }

    // Lógica del servicio
    public function upload(array $file, int $userId): array {

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Archivo con error o no recibido');
        }

        $originalName = $file['name'];
        $tmpPath = $file['tmp_name'];
        $size = $file['size'];

        $extension = FileHelper::getExtension($originalName);

        $blocked = $this->fileModel->getBlockedExtensions();

        if (in_array($extension, $blocked)) {
            throw new Exception("El tipo de archivo .$extension no está permitido");
        }

        if ($extension === 'zip') {
            $this->validateZip($tmpPath, $blocked);
        }

        $this->validateQuota($userId, $size);

        $originalName = $this->resolveOriginalName($originalName, $userId);

        $newName = FileHelper::generateUniqueName($extension);

        $userExternalId = $this->fileModel->getUserExternalId($userId);

        $fileId = null;
        $path = null;

        try {
            $path = $this->storage->store($tmpPath, $userExternalId, $newName);

            $fileData = [
                'user_id'       => $userId,
                'filename'      => $newName,
                'original_name' => $originalName,
                'file_path'     => $path,
                'file_size'     => $size,
                'file_type'     => $extension
            ];
            $fileId = $this->fileModel->save($fileData);

        } catch (Exception $e) {
            if ($path && file_exists($path)) {
                unlink($path);
            }
            throw $e;
        }

        return [
            'id' => $fileId,
            'name' => $originalName,
            'size' => $size
        ];
    }

    public function listByUser(?int $userId): array {
        if (!$userId) {
            throw new Exception('El id de usuario es obligatorio.');
        }

        try {
            $files = $this->fileModel->findAllByUserId($userId);

            return array_map(function ($file) {
                return [
                    'id'               => $file['id'],
                    'filename'         => $file['filename'],
                    'user_external_id' => $file['external_id'],
                    'name'             => $file['original_name'],
                    'size'             => FileHelper::formatSize($file['file_size']),
                    'type'             => $file['file_type'],
                    'date'             => $file['created_at']
                ];
            }, $files);

        } catch (Exception $e) {
            throw new Exception("No pudimos cargar tu lista de archivos en este momento.");
        }
    }

    public function delete(int $fileId, int $userId): void {
        $file = $this->fileModel->findByIdAndUser($fileId, $userId);

        if (!$file) {
            throw new Exception('Archivo no encontrado o no autorizado');
        }

        $this->pdo->beginTransaction();

        try {
            $this->fileModel->delete($fileId);

            // Eliminar del filesystem usando el StorageHandler
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }

            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception('Error al eliminar el archivo');
        }
    }

    public function getFileForDownload(int $fileId, int $userId): array {
        $file = $this->fileModel->getDownloadDetails($fileId, $userId);

        if (!$file) {
            throw new Exception('El archivo solicitado no existe o no tienes permiso para acceder a él.');
        }

        return $file;
    }
}