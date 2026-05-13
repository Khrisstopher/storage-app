<?php

namespace App\Models;

/**
 * Modelo de gestión de archivos.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class FileModel {
    private \PDO $pdo;

    /**
     * @param \PDO $pdo Instancia de conexión a la base de datos.
     */
    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Verificar si ya existe el nombre.
     * @throws \PDOException
     */
    public function originalNameExists(string $name, int $userId): bool {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM files WHERE user_id = ? AND original_name = ?");
            $stmt->execute([$userId, $name]);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Obtener extensiones restringidas. Esto está repetido hay que centralizarlo tal vez.
     * @throws \PDOException
     */
    public function getBlockedExtensions(): array {
        try {
            $stmt = $this->pdo->query("SELECT extension FROM blocked_extensions");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Guardar archivo.
     * @throws \PDOException
     */
    public function save(array $data): int {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO files (user_id, filename, original_name, file_path, file_size, file_type) 
                                        VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['user_id'], 
                $data['filename'], 
                $data['original_name'], 
                $data['file_path'], 
                $data['file_size'], 
                $data['file_type']
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Traer el getUserExternalId.
     * @throws \PDOException
     */
    public function getUserExternalId(int $userId): string {
        try {
            $stmt = $this->pdo->prepare("SELECT external_id FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $externalId = $stmt->fetchColumn();
            return $externalId ?: '';
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Traer archivos del usuario por id.
     * @throws \PDOException
     */
    public function findAllByUserId(int $userId): array {
        try {
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
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Traer el archivo por id de usuario e id de archivo.
     * @throws \PDOException
     */
    public function findByIdAndUser(int $fileId, int $userId): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT file_path FROM files WHERE id = ? AND user_id = ?");
            $stmt->execute([$fileId, $userId]);
            $file = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $file ?: null;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Elimina el archivo de la BD.
     * @throws \PDOException
     */
    public function delete(int $fileId): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM files WHERE id = ?");
            return $stmt->execute([$fileId]);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Traer el almacenamiento que ya ha ocupado el usuario.
     * @throws \PDOException
     */
    public function getTotalSizeByUser(int $userId): int {
        try {
            $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(file_size), 0) FROM files WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Traer detalles del archivo para descarga.
     * @throws \PDOException
     */
    public function getDownloadDetails(int $fileId, int $userId): ?array {
        try {
            $sql = "SELECT f.filename, f.original_name, u.external_id 
                    FROM files f
                    INNER JOIN users u ON f.user_id = u.id
                    WHERE f.id = ? AND f.user_id = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$fileId, $userId]);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            throw $e;
        }
    }
}