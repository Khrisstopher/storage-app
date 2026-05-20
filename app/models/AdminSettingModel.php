<?php

namespace App\Models;

/**
 * Modelo de administración de restricciones y permisos. 
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class AdminSettingModel {
    private \PDO $pdo;

    /**
     * @param \PDO $pdo Instancia de conexión a la base de datos.
     */
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtener extensiones restringidas.
     * @throws \PDOException
     */
    public function getFileRestrictions(): array {
        $stmt = $this->pdo->query("SELECT extension FROM blocked_extensions");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN, 0) ?: [];
    }

    public function clearBlockedExtensions(): void {
        $this->pdo->exec("DELETE FROM blocked_extensions");
    }

    public function insertBlockedExtension(string $ext): void {
        $stmt = $this->pdo->prepare("INSERT INTO blocked_extensions (extension) VALUES (:ext)");
        $stmt->execute([':ext' => $ext]);
    }

    public function getGlobalQuotaLimit(): int {
        $stmt = $this->pdo->query("SELECT max_upload_size FROM settings WHERE id = 1");
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    /**
     * Guarda o actualiza el límite máximo de peso acumulado para el sistema.
     * @param int $limit Peso en bytes (o la unidad que manejes).
     * @throws \PDOException
     */
    public function updateGlobalQuotaLimit(int $limit): void {
        $sql = "INSERT INTO settings (id, max_upload_size) 
                VALUES (1, :limit) 
                ON DUPLICATE KEY UPDATE max_upload_size = :limit2";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':limit'  => $limit,
            ':limit2' => $limit
        ]);
    }
}