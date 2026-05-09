<?php

// En FileService.php
require_once __DIR__ . '/../models/FileRepository.php';
require_once __DIR__ . '/handlers/StorageHandler.php';

class FileService {
    private PDO $pdo;
    private FileRepository $repo;
    private StorageHandler $storage;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->repo = new FileRepository($pdo);
        $this->storage = new StorageHandler();
    }

    // Funciones helper
    private function generateFileName(string $extension): string {
        return uniqid('', true) . '.' . $extension;
    }
    private function validateExtension(string $extension, array $blocked): void {
        if (in_array($extension, $blocked)) {
            throw new Exception("El tipo de archivo .$extension no está permitido");
        }
    }
    private function getExtension(string $filename): string {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
    private function formatSize($bytes) {

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    private function resolveOriginalName(string $originalName, int $userId): string {
        $info = pathinfo($originalName);
        $basename = $info['filename'];
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
        
        $finalName = $originalName;
        $counter = 1;

        // Bucle: Mientras el nombre exista en la tabla para ese user_id, incrementamos
        while ($this->repo->originalNameExists($finalName, $userId)) {
            $finalName = $basename . " ($counter)" . $extension;
            $counter++;
        }

        return $finalName;
    }

    private function validateQuota(int $userId, int $fileSize): void {
        $used = $this->repo->getTotalSizeByUser($userId);
        
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

        $extension = $this->getExtension($originalName);

        $blocked = $this->repo->getBlockedExtensions();

        $this->validateExtension($extension, $blocked);

        if ($extension === 'zip') {
            $this->repo->validateZip($tmpPath, $blocked);
        }

        $this->validateQuota($userId, $size);

        $originalName = $this->resolveOriginalName($originalName, $userId);

        $newName = $this->generateFileName($extension);

        $userExternalId = $this->repo->getUserExternalId($userId);

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
            $fileId = $this->repo->save($fileData);

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
            $files = $this->repo->findAllByUserId($userId);

            return array_map(function ($file) {
                return [
                    'id'               => $file['id'],
                    'filename'         => $file['filename'],
                    'user_external_id' => $file['external_id'],
                    'name'             => $file['original_name'],
                    'size'             => $this->formatSize($file['file_size']),
                    'type'             => $file['file_type'],
                    'date'             => $file['created_at']
                ];
            }, $files);

        } catch (Exception $e) {
            throw new Exception("No pudimos cargar tu lista de archivos en este momento.");
        }
    }

    public function delete(int $fileId, int $userId): void {
        $file = $this->repo->findByIdAndUser($fileId, $userId);

        if (!$file) {
            throw new Exception('Archivo no encontrado o no autorizado');
        }

        $this->pdo->beginTransaction();

        try {
            $this->repo->delete($fileId);

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
        $file = $this->repo->getDownloadDetails($fileId, $userId);

        if (!$file) {
            throw new Exception('El archivo solicitado no existe o no tienes permiso para acceder a él.');
        }

        return $file;
    }
}