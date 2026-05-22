<?php

namespace App\Models;

/**
 * Modelo de gestión de archivos.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class DashboardModel {
    private \PDO $pdo;

    /**
     * @param \PDO $pdo Instancia de conexión a la base de datos.
     */
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Verificar si ya existe el nombre.
     * @param string $name Nombre original del archivo a verificar.
     * @param int $userId ID del usuario para el que se verifica el nombre.
     * @return bool Retorna true si el nombre ya existe para ese usuario, false en caso contrario.
     */
    public function originalNameExists(string $name, int $userId): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM files WHERE user_id = ? AND original_name = ?");
        $stmt->execute([$userId, $name]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtener extensiones restringidas.
     * @return array Lista de extensiones bloqueadas.
     */
    public function getBlockedExtensions(): array {
        $stmt = $this->pdo->query("SELECT extension FROM blocked_extensions");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Guardar archivo.
     * @param array $data Datos del archivo a guardar.
     * @return int ID del archivo registrado en la base de datos.
     */
    public function save(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO files (user_id, filename, original_name, file_size, file_type) 
                                    VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'], 
            $data['filename'], 
            $data['original_name'], 
            $data['file_size'], 
            $data['file_type']
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Traer el getUserExternalId.
     * @param int $userId ID del usuario.
     * @return string ID externo del usuario o cadena vacía si no se encuentra.
     */
    public function getUserExternalId(int $userId): string {
        $stmt = $this->pdo->prepare("SELECT external_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $externalId = $stmt->fetchColumn();
        return $externalId ?: '';
    }

    /**
     * Traer todos los archivos del usuario por id.
     * @param int $userId ID del usuario.
     * @return array Lista de archivos asociados al usuario.
     */
    public function findAllFiles(int $userId): array {
        $sql = "SELECT *
                FROM files
                WHERE user_id = ? 
                ORDER BY created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Traer el archivo por id de usuario e id de archivo.
     * @param int $fileId ID del archivo.
     * @param int $userId ID del usuario.
     * @return array|null Datos del archivo o null si no se encuentra.
     */
    public function findByIdAndUser(int $fileId, int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
        $stmt->execute([$fileId, $userId]);

        $file = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $file ?: null;
    }

    /**
     * Elimina el archivo de la BD.
     * @param int $fileId ID del archivo a eliminar.
     * @return bool Retorna true si la eliminación fue exitosa, false en caso contrario
     */
    public function delete(int $fileId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM files WHERE id = ?");
        return $stmt->execute([$fileId]);
    }

    /**
     * Traer el almacenamiento que ya ha ocupado el usuario.
     * @param int $userId ID del usuario.
     * @return int Tamaño total en bytes de los archivos del usuario.
     */
    public function getTotalSizeByUser(int $userId): int {
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(file_size), 0) FROM files WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

   /**
     * Obtiene la cuota configurada para el usuario respetando la jerarquía desde la tabla quotas:
     * @param int $userId
     * @return int Límite de cuota en bytes o 10 MB por defecto.
     */
    public function getUserQuotaLimit(int $userId): int {
        $sql = "SELECT COALESCE(qu.quota_bytes, qg.quota_bytes, qs.quota_bytes, 10485760) as effective_quota
                FROM users u
                -- Intentamos traer los bytes de la cuota directa del usuario
                LEFT JOIN quotas qu ON u.quota_id = qu.id
                
                -- Intentamos traer los bytes de la cuota del grupo del usuario
                LEFT JOIN groups g ON u.group_id = g.id
                LEFT JOIN quotas qg ON g.quota_id = qg.id
                
                -- Intentamos traer los bytes de la cuota global activa en settings
                LEFT JOIN settings s ON s.id = 1
                LEFT JOIN quotas qs ON s.quota_id = qs.id
                
                WHERE u.id = :user_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ? (int)$result['effective_quota'] : (10 * 1024 * 1024);
    }
}