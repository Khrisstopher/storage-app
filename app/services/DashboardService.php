<?php

namespace App\Services;

use App\Models\DashboardModel;
use App\Services\Handlers\StorageHandler;
use App\Helpers\FileHelper;

require_once __DIR__ . '/../models/DashboardModel.php';
require_once __DIR__ . '/handlers/StorageHandler.php';
require_once __DIR__ . '/../helpers/FileHelper.php';

/**
 * Servicio de gestión de archivos.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class DashboardService {
    private \PDO $pdo;
    private DashboardModel $dashboardModel;
    private StorageHandler $storage;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
        $this->dashboardModel = new DashboardModel($pdo);
        $this->storage = new StorageHandler();
    }

    private function resolveOriginalName(string $originalName, int $userId): string {
        $info = pathinfo($originalName);
        $basename = $info['filename'];
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
        
        $finalName = $originalName;
        $counter = 1;

        // Bucle: Mientras el nombre exista en la tabla para ese user_id, incrementamos
        while ($this->dashboardModel->originalNameExists($finalName, $userId)) {
            $finalName = $basename . " ($counter)" . $extension;
            $counter++;
        }

        return $finalName;
    }

    private function validateQuota(int $userId, int $fileSize): void {
        $used = $this->dashboardModel->getTotalSizeByUser($userId);
        
        $limit = $this->dashboardModel->getUserQuotaLimit($userId);

        if (($used + $fileSize) > $limit) {
            $available = ($limit - $used) / (1024 * 1024);
            $availableFormatted = number_format($available, 2);
            
            throw new \Exception("Cuota excedida. Solo te quedan {$availableFormatted} MB disponibles.");
        }
    }
    
    private function validateZip(string $tmpPath, array $blockedExtensions): void {

        if (!class_exists('\ZipArchive')) {
            throw new \Exception('El servidor no tiene habilitada la función para revisar archivos ZIP.');
        }

        $zip = new \ZipArchive();

        if ($zip->open($tmpPath) !== TRUE) {
            throw new \Exception('No se pudo analizar el archivo ZIP');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileInside = $zip->getNameIndex($i);
            $extInside = strtolower(pathinfo($fileInside, PATHINFO_EXTENSION));

            if (in_array($extInside, $blockedExtensions)) {
                $zip->close();
                throw new \Exception("El archivo '$fileInside' dentro del ZIP no está permitido");
            }
        }

        $zip->close();
    }

    public function upload(array $file, int $userId): array {

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Archivo con error o no recibido');
        }

        $originalName = $file['name'];
        $tmpPath = $file['tmp_name'];
        $size = $file['size'];

        $extension = FileHelper::getExtension($originalName);
        $blocked = $this->dashboardModel->getBlockedExtensions();

        if (in_array($extension, $blocked)) {
            throw new \Exception("El tipo de archivo .$extension no está permitido");
        }

        if ($extension === 'zip') {
            $this->validateZip($tmpPath, $blocked);
        }

        $this->validateQuota($userId, $size);
        $originalName = $this->resolveOriginalName($originalName, $userId);
        $newName = FileHelper::generateUniqueName($extension);
        $userExternalId = $this->dashboardModel->getUserExternalId($userId);

        $fileId = null;
        $path = null;

        // Iniciamos la transacción antes de tocar la base de datos y el disco
        $this->pdo->beginTransaction();

        try {
            // Guardamos físicamente en storage/uploads
            $path = $this->storage->store($tmpPath, $userExternalId, $newName);

            $fileData = [
                'user_id'       => $userId,
                'filename'      => $newName,
                'original_name' => $originalName,
                'file_size'     => $size,
                'file_type'     => $extension
            ];
            
            // Insertamos el registro en BD
            $fileId = $this->dashboardModel->save($fileData);
            $this->pdo->commit();

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            if ($path && file_exists($path)) { 
                $this->storage->remove($path); // Borrar archivo
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
            throw new \Exception('El id de usuario es obligatorio.');
        }

        $files = $this->dashboardModel->findAllFiles($userId);
        $externalId = $this->dashboardModel->getUserExternalId($userId);

        return array_map(function ($file) use ($externalId) {
            return [
                'id'               => $file['id'],
                'filename'         => $file['filename'],
                'user_external_id' => $externalId,
                'name'             => $file['original_name'],
                'size'             => FileHelper::formatSize($file['file_size']),
                'type'             => $file['file_type'],
                'date'             => $file['created_at']
            ];
        }, $files);
    }

    public function delete(int $fileId, int $userId): void {
        $file = $this->dashboardModel->findByIdAndUser($fileId, $userId);
        $userExternalId = $this->dashboardModel->getUserExternalId($userId);

        if (!$file) {
            throw new \Exception('Archivo no encontrado o no autorizado');
        }

        $this->pdo->beginTransaction();

        try {
            $this->dashboardModel->delete($fileId);
            $this->pdo->commit();

            $absolutePath = $this->storage->getFilePath($userExternalId, $file['filename']);
            $this->storage->remove($absolutePath);

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function getFileForDownload(int $fileId, int $userId): array {
        $file = $this->dashboardModel->findByIdAndUser($fileId, $userId);
        $userExternalId = $this->dashboardModel->getUserExternalId($userId);

        if (!$file) {
            throw new \Exception('El archivo solicitado no existe o no tienes permiso para acceder a él.');
        }

        // Le agregamos un nuevo índice al array con la ruta física real
        $file['absolute_path'] = $this->storage->getFilePath($userExternalId, $file['filename']);

        return $file;
    }
}