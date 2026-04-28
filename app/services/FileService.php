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

    // Listar archivos del usuario
    public function listByUser($userId) {

        try{
            $stmt = $this->pdo->prepare("SELECT id, original_name, file_size, file_type, created_at 
                                        FROM files WHERE user_id = ? 
                                        ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatear datos para frontend
            return array_map(function ($file) {
                return [
                    'id'   => $file['id'],
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

    // Subir nuevo archivo
    public function upload($file, $userId) {

        // 1. Validar que el archivo exista
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo');
        }

        // 2. Datos básicos
        $originalName = $file['name'];
        $tmpPath = $file['tmp_name'];
        $fileSize = $file['size'];

        // 3. Obtener extensión
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // 4. Validar extensiones prohibidas
        $blockedExtensions = ['exe', 'bat', 'js', 'php', 'sh'];

        if (in_array($extension, $blockedExtensions)) {
            throw new Exception("El tipo de archivo .$extension no está permitido");
        }

        // 5. Validar ZIP
        if ($extension === 'zip') {
            $zip = new ZipArchive();

            if ($zip->open($tmpPath) === TRUE) {

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileInside = $zip->getNameIndex($i);
                    $extInside = strtolower(pathinfo($fileInside, PATHINFO_EXTENSION));

                    if (in_array($extInside, $blockedExtensions)) {
                        $zip->close();
                        throw new Exception("El archivo '$fileInside' dentro del ZIP no está permitido");
                    }
                }

                $zip->close();
            } else {
                throw new Exception('No se pudo analizar el archivo ZIP');
            }
        }

        // 6. Validar cuota (versión básica por ahora)
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(file_size), 0) as total 
                                    FROM files 
                                    WHERE user_id = ?");
        $stmt->execute([$userId]);
        $usedSpace = (int) $stmt->fetchColumn();

        $maxQuota = 10 * 1024 * 1024; // 10MB (luego lo haces dinámico)

        if (($usedSpace + $fileSize) > $maxQuota) {
            throw new Exception('Cuota de almacenamiento excedida (10MB)');
        }

        // 7. Generar nombre único
        $newName = uniqid() . '.' . $extension;

        // 8. Ruta destino
        $uploadDir = __DIR__ . '/../../storage/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $destination = $uploadDir . $newName;

        // 9. Mover archivo
        if (!move_uploaded_file($tmpPath, $destination)) {
            throw new Exception('Error al guardar el archivo');
        }

        // 10. Guardar en DB
        $stmt = $this->pdo->prepare("INSERT INTO files (user_id, filename, original_name, file_path, file_size, file_type)
                                    VALUES (?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $userId,
            $newName,
            $originalName,
            $destination,
            $fileSize,
            $extension
        ]);

        $fileId = $this->pdo->lastInsertId();

        // 11. Respuesta
        return [
            'id' => $fileId,
            'name' => $originalName,
            'size' => $fileSize
        ];
    }
}