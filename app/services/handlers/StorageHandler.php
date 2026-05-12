<?php
/**
 * Archivo: app/services/handlers/StorageHandler.php
 * Descripción: Clase encargada de guardar el archivo en el directorio.
 * Autor: @KhrisstopherTube
 */
class StorageHandler {
    private string $basePath;

    public function __construct() {
        $this->basePath = ROOT_PATH . '/storage/uploads/';

        // Asegurar que la carpeta principal de uploads existe
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0777, true);
        }
    }

    public function store(string $tmpPath, string $userExtId, string $newName): string {
        $dir = $this->basePath . $userExtId . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        
        $destination = $dir . $newName;
        if (!move_uploaded_file($tmpPath, $destination)) {
            throw new Exception('Error físico al guardar el archivo');
        }
        return $destination;
    }

    public function remove(string $path): void {
        if (file_exists($path)) unlink($path);
    }
}