<?php
/**
 * FileModel
 * 
 * Modelo que gestiona todas las operaciones de base de datos relacionadas con archivos.
 * Incluye métodos para crear, consultar, eliminar archivos y gestionar restricciones de extensiones.
 * 
 * @package app\models
 */
class FileModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo; 
    }

    public function originalNameExists(string $name, int $userId): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM files WHERE user_id = ? AND original_name = ?");
        $stmt->execute([$userId, $name]);
        return $stmt->fetchColumn() > 0;
    }

    public function getBlockedExtensions(): array {
        $stmt = $this->pdo->query("SELECT extension FROM blocked_extensions");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function save(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO files (user_id, filename, original_name, file_path, file_size, file_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['user_id'], $data['filename'], $data['original_name'], $data['file_path'], $data['file_size'], $data['file_type']]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getUserExternalId(int $userId): string {
        $stmt = $this->pdo->prepare("SELECT external_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $externalId = $stmt->fetchColumn();
        return $externalId ?: null;
    }

    public function findAllByUserId(int $userId): array {
        $sql = "SELECT 
                    f.id,
                    f.filename,
                    f.original_name, 
                    f.file_size, 
                    f.file_type, 
                    f.created_at, 
                    u.external_id 
                FROM files f
                INNER JOIN users u ON f.user_id = u.id
                WHERE f.user_id = ? 
                ORDER BY f.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByIdAndUser(int $fileId, int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT file_path FROM files WHERE id = ? AND user_id = ?");
        $stmt->execute([$fileId, $userId]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $file ?: null;
    }

    public function delete(int $fileId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM files WHERE id = ?");
        return $stmt->execute([$fileId]);
    }

    public function getTotalSizeByUser(int $userId): int {
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(file_size), 0) FROM files WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        return (int) $stmt->fetchColumn();
    }

    public function getDownloadDetails(int $fileId, int $userId): ?array {
        $sql = "SELECT f.filename, f.original_name, u.external_id 
                FROM files f
                INNER JOIN users u ON f.user_id = u.id
                WHERE f.id = ? AND f.user_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fileId, $userId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}