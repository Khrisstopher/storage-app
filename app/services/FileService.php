<?php

class FileService {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Formatear tamaño de archivo para mostrar en frontend
    private function formatSize($bytes) {

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    private function validateUpload(array $file): void {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Archivo con error o no recibido');
        }
    }

    private function getExtension(string $filename): string {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    private function validateExtension(string $extension, array $blocked): void {
        if (in_array($extension, $blocked)) {
            throw new Exception("El tipo de archivo .$extension no está permitido");
        }
    }

    private function generateFileName(string $extension): string {
        return uniqid('', true) . '.' . $extension;
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

    private function validateQuota(int $userId, int $fileSize): void {
        // 1. Buscamos en la tabla 'files' cuánto espacio ha ocupado este ID
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(file_size), 0) 
                                    FROM files 
                                    WHERE user_id = ?");
        $stmt->execute([$userId]);

        $used = (int) $stmt->fetchColumn();
        
        // 2. Definimos el límite de 10MB de forma legible
        $limit = 10 * 1024 * 1024; 

        // 3. La prueba de fuego
        if (($used + $fileSize) > $limit) {
            // Calculamos cuánto falta para que el mensaje sea más amable
            $available = ($limit - $used) / (1024 * 1024);
            $availableFormatted = number_format($available, 2);
            
            throw new Exception("Cuota excedida. Solo te quedan {$availableFormatted} MB disponibles.");
        }
    }

    // Guardar archivo en el servidor
    private function storeFile(string $tmpPath, string $newName, string $userExternalId): string {
        $dir = __DIR__ . '/../../storage/uploads/' . $userExternalId . '/';

        if (!is_dir($dir)) {
            // El 'true' permite crear carpetas anidadas automáticamente
            mkdir($dir, 0777, true);
        }

        $destination = $dir . $newName;

        if (!move_uploaded_file($tmpPath, $destination)) {
            throw new Exception('Error al guardar el archivo en tu carpeta personal');
        }

        return $destination;
    }

    // Guardar metadata del archivo en la base de datos
    private function saveFile(
        int $userId,
        string $newName,
        string $originalName,
        string $path,
        int $size,
        string $extension): int {

        $stmt = $this->pdo->prepare("INSERT INTO files 
                                    (user_id, filename, original_name, file_path, file_size, file_type)
                                    VALUES (?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $userId,
            $newName,
            $originalName,
            $path,
            $size,
            $extension
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function getUserExternalId(int $userId): string {
        $stmt = $this->pdo->prepare("SELECT external_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        $externalId = $stmt->fetchColumn();

        if (!$externalId) {
            throw new Exception('Usuario no encontrado');
        }

        return $externalId;
    }

    private function getBlockedExtensions(): array {

        $stmt = $this->pdo->query("SELECT extension FROM blocked_extensions");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // #### Funciones públicas para el controlador ####
    public function listByUser($userId) {

        try{
            $stmt = $this->pdo->prepare("SELECT 
                    f.id,
                    f.filename,
                    f.original_name, 
                    f.file_size, 
                    f.file_type, 
                    f.created_at, 
                    u.external_id 
                FROM files f
                INNER JOIN users u ON f.user_id = u.id
                WHERE f.user_id = ? 
                ORDER BY f.created_at DESC");
            $stmt->execute([$userId]);
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatear datos para frontend
            return array_map(function ($file) {
                return [
                    'id'   => $file['id'],
                    'filename' => $file['filename'],
                    'user_external_id' => $file['external_id'],
                    'name' => $file['original_name'],
                    'size' => $this->formatSize($file['file_size']),
                    'type' => $file['file_type'],
                    'date' => $file['created_at']
                ];
            }, $files);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener la lista de archivos");
        }
    }

    public function upload(array $file, int $userId): array {

        $this->validateUpload($file);

        $originalName = $file['name'];
        $tmpPath = $file['tmp_name'];
        $size = $file['size'];

        $extension = $this->getExtension($originalName);

        $blocked = $this->getBlockedExtensions();

        $this->validateExtension($extension, $blocked);

        if ($extension === 'zip') {
            $this->validateZip($tmpPath, $blocked);
        }

        $this->validateQuota($userId, $size);

        $newName = $this->generateFileName($extension);

        $userExternalId = $this->getUserExternalId($userId);

        $fileId = null;
        $path = null;

        try {
            $path = $this->storeFile($tmpPath, $newName, $userExternalId);

            $fileId = $this->saveFile(
                $userId,
                $newName,
                $originalName,
                $path,
                $size,
                $extension
            );

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

    public function delete(int $fileId, int $userId): void {

        $stmt = $this->pdo->prepare("SELECT file_path 
            FROM files 
            WHERE id = ? AND user_id = ?");

        $stmt->execute([$fileId, $userId]);

        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$file) {
            throw new Exception('Archivo no encontrado o no autorizado');
        }

        $this->pdo->beginTransaction();

        try {
            // 2. Eliminar de la base de datos
            $stmt = $this->pdo->prepare("DELETE FROM files WHERE id = ?");
            $stmt->execute([$fileId]);

            // 3. Eliminar del filesystem
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }

            $this->pdo->commit();

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception('Error al eliminar el archivo');
        }
    }

    public function getFileForDownload($fileId, $userId) {
        $stmt = $this->pdo->prepare("SELECT f.filename, f.original_name, u.external_id 
            FROM files f
            INNER JOIN users u ON f.user_id = u.id
            WHERE f.id = ? AND f.user_id = ?");
        $stmt->execute([$fileId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}