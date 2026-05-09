<?php

class StorageHandler {
    private string $basePath;

    public function __construct() {
        $this->basePath = ROOT_PATH . '/storage/uploads/';
    }

    public function store(string $tmpPath, string $userExtId, string $newName): string {
        $dir = $this->basePath . $userExtId . '/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        
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