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
        try {
            $stmt = $this->pdo->query("SELECT extension FROM blocked_extensions");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN, 0) ?: [];
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Guardar extensiones restringidas.
     * @throws \PDOException
     */
    public function saveBlockedExtensions(array $extensions): void {
        try {
            $this->pdo->beginTransaction();

            // Limpiar tabla actual
            $this->pdo->exec("DELETE FROM blocked_extensions");

            $stmt = $this->pdo->prepare("INSERT INTO blocked_extensions (extension) VALUES (:ext)");
            
            foreach ($extensions as $ext) {
                $stmt->execute([':ext' => $ext]);
            }

            $this->pdo->commit();

        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}