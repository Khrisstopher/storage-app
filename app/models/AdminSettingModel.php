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
     * @return array Lista de extensiones bloqueadas.
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

    #### Actualización de cuota global ####

    /**
     * Obtiene el ID de la cuota global desde los settings.
     * @return int|null
     */
    public function getGlobalQuotaId(): ?int {
        $stmt = $this->pdo->query("SELECT quota_id FROM settings WHERE id = 1");
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    /**
     * Actualiza los bytes de una cuota existente
     * @param int $quotaId ID de la cuota a actualizar
     * @param int $limit Nuevo límite en bytes
     * @return bool Retorna true si la actualización fue exitosa, false en caso contrario
     */
    public function updateQuotaBytes(int $quotaId, int $limit): bool {
        $sql = "UPDATE quotas SET quota_bytes = :limit WHERE id = :quota_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':limit' => $limit, ':quota_id' => $quotaId]);
    }

    /**
     * Inserta una nueva cuota global y la vincula en settings.
     * @param int $limit Límite en bytes para la cuota global.
     * @return void
     */
    public function createGlobalQuotaSetting(int $limit): void {
        // Insertar la cuota
        $sqlNewQuota = "INSERT INTO quotas (name, quota_bytes, description) 
                        VALUES ('Sistema Global', :limit, 'Cuota por defecto para todo el sistema')";
        $stmtNewQuota = $this->pdo->prepare($sqlNewQuota);
        $stmtNewQuota->execute([':limit' => $limit]);
        
        $newQuotaId = $this->pdo->lastInsertId();

        // Vincular en settings
        $sqlSettings = "INSERT INTO settings (id, quota_id) VALUES (1, :quota_id)";
        $stmtSettings = $this->pdo->prepare($sqlSettings);
        $stmtSettings->execute([':quota_id' => $newQuotaId]);
    }

    /**
     * Busca si el usuario tiene una cuota específica asignada directamente.
     * @param int $userId ID del usuario.
     * @return int|null Retorna el límite en bytes o null si no tiene cuota personalizada.
     */
    public function getQuotaByUserId(int $userId): ?int {
        $sql = "SELECT q.quota_bytes 
                FROM users u
                INNER JOIN quotas q ON u.quota_id = q.id 
                WHERE u.id = :user_id";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $res = $stmt->fetchColumn();
        return $res !== false ? (int)$res : null;
    }

    /**
     * Busca la cuota asignada al grupo al que pertenece el usuario.
     * @param int $userId ID del usuario.
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