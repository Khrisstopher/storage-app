<?php

namespace App\Services\Handlers;

/**
 * Servicio de guardado de archivo en directorio local.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class StorageHandler {
    private string $basePath;


    /**
     * Define la ruta base de almacenamiento y asegura la existencia del directorio.
     * Configura el path principal utilizando la constante ROOT_PATH y crea
     * la estructura de carpetas necesaria con permisos de escritura.
     */
    public function __construct() {
        $this->basePath = ROOT_PATH . '/storage/uploads/';

        // Asegurar que la carpeta principal de uploads existe
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0777, true);
        }
    }

    /**
     * Construye y retorna la ruta absoluta de un archivo almacenado.
     * 
     * @param string $userExtId ID externo del usuario (nombre de la subcarpeta).
     * @param string $filename Nombre físico del archivo en el disco.
     * @return string Ruta absoluta completa del archivo.
     */
    public function getFilePath(string $userExtId, string $filename): string {
        return $this->basePath . $userExtId . '/' . $filename;
    }
    
    /**
     * Guardar el archivo en la ruta especificada.
     * @return string
     * @throws \Exception
     */
    public function store(string $tmpPath, string $userExtId, string $newName): string {
        $dir = $this->basePath . $userExtId . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        
        $destination = $dir . $newName;
        if (!move_uploaded_file($tmpPath, $destination)) {
            throw new \Exception('Error físico al guardar el archivo');
        }
        return $destination;
    }

    /**
     * Elimina un archivo físico del servidor si existe.
     * @param string $path Ruta absoluta o relativa del archivo.
     * @return void
     */
    public function remove(string $path): void {
        if (file_exists($path)) unlink($path);
    }
}