<?php

namespace App\Helpers;

/**
 * Métodos static de ayuda.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class FileHelper {

    /**
     * Obtiene la extensión de un archivo en minúsculas.
     */
    public static function getExtension(string $filename): string {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Genera un nombre único basado en microsegundos para evitar colisiones.
     */
    public static function generateUniqueName(string $extension): string {
        return uniqid('', true) . '.' . $extension;
    }

    /**
     * Formatea bytes a un formato legible (B, KB, MB).
     */
    public static function formatSize($bytes): string {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}