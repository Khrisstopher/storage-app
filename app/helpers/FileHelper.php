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
     * @param string $filename
     * @return string
     */
    public static function getExtension(string $filename): string {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Genera un token aleatorio criptográficamente seguro.
     * @return string 32 caracteres hexadecimales.
     */
    public static function generateToken(): string {
        return bin2hex(random_bytes(16));
    }

    /**
     * Genera un nombre de archivo único y seguro utilizando el generador de tokens.
     * @param string $extension
     * @return string
     */
    public static function generateUniqueName(string $extension): string {
        return self::generateToken() . '.' . $extension;
    }

    /**
     * Formatea bytes a un formato legible (B, KB, MB).
     * @param int $bytes
     * @return string
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

    /**
     * Convierte un valor en Bytes a Megabytes enteros (útil para la interfaz de administración).
     * @param int|null $bytes
     * @return int|null
     */
    public static function bytesToMb(?int $bytes): ?int {
        if ($bytes === null) return null;
        return (int) ($bytes / 1024 / 1024);
    }

    /**
     * Convierte Megabytes a Bytes (útil para guardar límites en la base de datos).
     * @param int|float|null $mb
     * @return int|null
     */
    public static function mbToBytes($mb): ?int {
        if ($mb === null || $mb === '') return null;
        return (int) ($mb * 1024 * 1024);
    }
}