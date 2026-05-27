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

    private const MIMES_PELIGROSOS = [
        'text/x-php', 
        'application/x-php', 
        'application/x-msdownload',
        'application/x-sh', 
        'text/javascript', 
        'application/javascript'
    ];

    /**
     * Constructor del servicio de dashboard.
     * @param \PDO $pdo Conexión PDO para la base de datos.
     */
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
        $this->dashboardModel = new DashboardModel($pdo);
        $this->storage = new StorageHandler();
    }

    /**
     * Resuelve un nombre de archivo original para evitar colisiones, agregando un sufijo numérico si es necesario.
     * @param string $originalName El nombre original del archivo.
     * @param int $userId ID del usuario propietario del archivo.
     * @return string El nombre final del archivo.
     */
    private function resolveOriginalName(string $originalName, int $userId): string {
        $info = pathinfo($originalName);
        $basename = $info['filename'];
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
        
        $finalName = $originalName;
        $counter = 1;

        // Mientras el nombre exista en la tabla para ese user_id, incrementamos
        while ($this->dashboardModel->originalNameExists($finalName, $userId)) {
            $finalName = $basename . " ($counter)" . $extension;
            $counter++;
        }

        return $finalName;
    }

    /**
     * Valida que el usuario no exceda su cuota al subir un nuevo archivo.
     * @param int $userId ID del usuario que sube el archivo.
     * @param int $fileSize Tamaño del archivo que se intenta subir, en bytes.
     */
    private function validateQuota(int $userId, int $fileSize): void {
        $used = $this->dashboardModel->getTotalSizeByUser($userId);
        
        $limit = $this->dashboardModel->getUserQuotaLimit($userId);

        if (($used + $fileSize) > $limit) {
            $available = ($limit - $used) / (1024 * 1024);
            $availableFormatted = number_format($available, 2);
            
            throw new \Exception("Cuota excedida. Solo te quedan {$availableFormatted} MB disponibles.");
        }
    }
    
    /**
     * Valida el contenido de un archivo ZIP para asegurarse de que no contenga archivos con extensiones bloqueadas.
     * @param string $tmpPath Ruta temporal del archivo ZIP a validar.
     * @param array $blockedExtensions Lista de extensiones bloqueadas (en minúsculas, sin punto).
     */
    private function validateZip(string $tmpPath, array $blockedExtensions): void {

        if (!class_exists('\ZipArchive')) {
            throw new \Exception('El servidor no tiene habilitada la función para revisar archivos ZIP.');
        }

        $zip = new \ZipArchive();

        if ($zip->open($tmpPath) !== TRUE) {
            throw new \Exception('No se pudo analizar el archivo ZIP');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileInside = $zip->getNameIndex($i);

            if (substr($fileInside, -1) === '/') {
                continue;
            }

            $extInside = strtolower(pathinfo($fileInside, PATHINFO_EXTENSION));
            if (in_array($extInside, $blockedExtensions)) {
                $zip->close();
                throw new \Exception("El archivo '$fileInside' dentro del ZIP no está permitido");
            }

            $streamPath = "zip://" . realpath($tmpPath) . "#" . $fileInside;
            $realMimeInside = $finfo->file($streamPath);

            if (in_array($realMimeInside, self::MIMES_PELIGROSOS)) {
                $zip->close();
                throw new \Exception("Seguridad: El archivo '$fileInside' dentro del ZIP contiene código o un formato peligroso prohibido.");
            }

            if (in_array($extInside, ['png', 'jpg', 'jpeg']) && strpos($realMimeInside, 'image') === false) {
                $zip->close();
                throw new \Exception("Seguridad: El archivo '$fileInside' dentro del ZIP tiene extensión de imagen pero su contenido real es diferente.");
            }
        }
        $zip->close();
    }

    /**
     * Sube un archivo para un usuario específico, validando la cuota y el tipo de archivo, 
     * y guardando la información en la base de datos.
     * @param array $file Información del archivo a subir (estructura de $_FILES).
     * @param int $userId ID del usuario que sube el archivo.
     * @return array Datos del archivo subido.
     */
    public function upload(array $file, int $userId): array {

        // Validar lo básico del archivo recibido
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Archivo con error o no recibido');
        }

        $originalName = $file['name'];
        $tmpPath = $file['tmp_name'];
        $size = $file['size'];

        // Validar límite individual por archivo
        $maxFileBytes = $this->dashboardModel->getSingleFileLimit($userId);
        
        if ($size > $maxFileBytes) {
            $maxLimitInMb = FileHelper::bytesToMb($maxFileBytes);
            throw new \Exception("El archivo excede el tamaño máximo permitido por archivo individual ({$maxLimitInMb}MB).");
        }

        // Validar extensión bloqueada
        $extension = FileHelper::getExtension($originalName);
        $blocked = $this->dashboardModel->getBlockedExtensions();

        if (in_array($extension, $blocked)) {
            throw new \Exception("El tipo de archivo .$extension no está permitido");
        }

        // Valida la cuota total del usuario antes de proceder con la subida
        $this->validateQuota($userId, $size);

        // Valida el contenido de los archivos
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($tmpPath);

        if (in_array($realMime, self::MIMES_PELIGROSOS)) {
            throw new \Exception("Seguridad: El contenido interno del archivo corresponde a un formato peligroso prohibido.");
        }

        if (($extension === 'pdf' && strpos($realMime, 'pdf') === false) ||
            (in_array($extension, ['png', 'jpg', 'jpeg']) && strpos($realMime, 'image') === false) ||
            ($extension === 'zip' && strpos($realMime, 'zip') === false)) {
            throw new \Exception("Seguridad: El contenido real del archivo no coincide con la extensión .$extension.");
        }

        // Si es un ZIP, validamos su contenido antes de guardarlo
        if ($extension === 'zip') {
            $this->validateZip($tmpPath, $blocked);
        }

        $originalName = $this->resolveOriginalName($originalName, $userId);
        $newName = FileHelper::generateUniqueName($extension);
        $userExternalId = $this->dashboardModel->getUserExternalId($userId);

        $fileId = null;
        $path = null;

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

    /**
     * Lista los archivos de un usuario específico.
     * @param int $userId ID del usuario.
     * @return array Lista de archivos del usuario.
     */
    public function listByUser(int $userId): array {
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

    /**
     * Borra un archivo específico de un usuario, eliminando tanto el registro en la base de datos 
     * como el archivo físico en el almacenamiento.
     * @param int $fileId ID del archivo a borrar.
     * @param int $userId ID del usuario propietario del archivo.
     */
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

    /**
     * Obtiene la información de un archivo específico para su descarga, 
     * verificando que el usuario tenga permiso para acceder a él.
     * @param int $fileId ID del archivo a descargar.
     * @param int $userId ID del usuario que intenta descargar el archivo.
     * @return array Información del archivo, incluyendo la ruta absoluta para su descarga.
     */
    public function getFileForDownload(int $fileId, int $userId): array {
        $file = $this->dashboardModel->findByIdAndUser($fileId, $userId);
        $userExternalId = $this->dashboardModel->getUserExternalId($userId);

        if (!$file) {
            throw new \Exception('El archivo solicitado no existe o no tienes permiso para acceder a él.');
        }

        $file['absolute_path'] = $this->storage->getFilePath($userExternalId, $file['filename']);

        $cleanName = str_replace(["\r", "\n"], "", basename($file['original_name']));
        $file['safe_name'] = addslashes($cleanName);

        return $file;
    }
}