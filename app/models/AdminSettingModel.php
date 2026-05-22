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

    /**
     * Elimina todas las extensiones bloqueadas para luego insertar las nuevas. 
     */
    public function clearBlockedExtensions(): void {
        $this->pdo->exec("DELETE FROM blocked_extensions");
    }

    /**
     * Agrega extensiones a la lista de bloqueadas.
     * @param string $ext Extensión sin el punto (ej: 'exe', 'php').
     * @throws \PDOException
     */
    public function insertBlockedExtension(string $ext): void {
        $stmt = $this->pdo->prepare("INSERT INTO blocked_extensions (extension) VALUES (:ext)");
        $stmt->execute([':ext' => $ext]);
    }

    /**
     * Obtiene el límite de cuota global en bytes para la configuración del sistema.
     * @return int Límite en bytes.
     */
    public function getGlobalQuotaLimit(): int {
        $sql = "SELECT q.quota_bytes 
                FROM settings s
                INNER JOIN quotas q ON s.quota_id = q.id 
                WHERE s.id = 1";
                
        $stmt = $this->pdo->query($sql);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    /**
     * Guarda o actualiza el límite máximo en bytes para la cuota del sistema global.
     * @param int $limit Peso en bytes.
     * @throws \PDOException
     */
    public function updateGlobalQuotaLimit(int $limit): void {
        try {
            $this->pdo->beginTransaction();

            // Verificar si ya existe una cuota global
            $stmt = $this->pdo->query("SELECT quota_id FROM settings WHERE id = 1");
            $quotaId = $stmt->fetchColumn();

            if ($quotaId) {
                // Si ya existe, actualizamos los bytes directamente en la tabla de cuotas
                $sqlQuota = "UPDATE quotas SET quota_bytes = :limit WHERE id = :quota_id";
                $stmtQuota = $this->pdo->prepare($sqlQuota);
                $stmtQuota->execute([
                    ':limit'    => $limit,
                    ':quota_id' => $quotaId
                ]);
            } else {
                // Si no existe, creamos la cuota primero
                $sqlNewQuota = "INSERT INTO quotas (name, quota_bytes, description) 
                                VALUES ('Sistema Global', :limit, 'Cuota por defecto para todo el sistema')";
                $stmtNewQuota = $this->pdo->prepare($sqlNewQuota);
                $stmtNewQuota->execute([':limit' => $limit]);
                
                $newQuotaId = $this->pdo->lastInsertId();

                // Y luego lo vinculamos en la tabla de settings
                $sqlSettings = "INSERT INTO settings (id, quota_id) VALUES (1, :quota_id)";
                $stmtSettings = $this->pdo->prepare($sqlSettings);
                $stmtSettings->execute([':quota_id' => $newQuotaId]);
            }

            $this->pdo->commit();
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Busca si el usuario tiene una cuota específica asignada directamente.
     * @return int|null Retorna el límite en bytes o null si no tiene cuota personalizada.
     */
    public function getQuotaByUserId(int $userId): ?int {
        $sql = "SELECT q.quota_bytes 
                FROM users u
                INNER JOIN quotas q ON u.quota_id = q.id 
                WHERE u.id = :user_id";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        // Retorna el entero si existe, o null si la columna quota_id es NULL
        $res = $stmt->fetchColumn();
        return $res !== false ? (int)$res : null;
    }

    /**
     * Busca la cuota asignada al grupo al que pertenece el usuario.
     * @return int|null Retorna el límite en bytes o null si el grupo no tiene cuota asignada.
     */
    public function getQuotaByGroupId(int $userId): ?int {
        $sql = "SELECT q.quota_bytes 
                FROM users u
                INNER JOIN groups g ON u.group_id = g.id
                INNER JOIN quotas q ON g.quota_id = q.id 
                WHERE u.id = :user_id";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        $res = $stmt->fetchColumn();
        return $res !== false ? (int)$res : null;
    }
}