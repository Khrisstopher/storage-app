<?php
/**
 * Archivo: app/services/AdminSettingService.php
 * Descripción: Clase para poner restricciones a usuarios no admins.
 * Autor: @KhrisstopherTube
 */

class AdminSettingService {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getFileRestrictions() {
        $stmt = $this->pdo->query("SELECT extension FROM blocked_extensions");
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function updateFileRestrictions($data) {

        try {

            if (!isset($data['extensions'])) {
                throw new Exception('No se recibieron extensiones.');
            }

            $extensions = trim($data['extensions']);

            if (empty($extensions)) {
                throw new Exception('Debes ingresar al menos una extensión.');
            }

            // Convierte el string en array
            $extensionsArray = explode(',', $extensions);

            // Limpia espacios y convierte a minúsculas
            $extensionsArray = array_map(function($ext) {
                return strtolower(trim($ext));
            }, $extensionsArray);

            // Elimina valores vacíos
            $extensionsArray = array_filter($extensionsArray);

            // Elimina duplicados
            $extensionsArray = array_unique($extensionsArray);

            // Reindexa el array
            $extensionsArray = array_values($extensionsArray);

            // Validaciones
            foreach ($extensionsArray as $ext) {

                // Solo letras y números
                if (!preg_match('/^[a-z0-9]+$/', $ext)) {
                    throw new Exception("La extensión '$ext' no es válida.");
                }

                // Longitud máxima
                if (strlen($ext) > 10) {
                    throw new Exception("La extensión '$ext' es demasiado larga.");
                }
            }

            // Inicia transacción
            $this->pdo->beginTransaction();

            // Elimina restricciones anteriores
            $deleteStmt = $this->pdo->prepare("DELETE FROM blocked_extensions");

            $deleteStmt->execute();

            // Inserta nuevas extensiones
            $insertStmt = $this->pdo->prepare("INSERT INTO blocked_extensions (extension)
                VALUES (:extension)");

            foreach ($extensionsArray as $ext) {

                $insertStmt->execute([
                    ':extension' => $ext
                ]);
            }

            // Confirma cambios
            $this->pdo->commit();

            return $extensionsArray;

        } catch (PDOException $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw new Exception('Error al guardar las restricciones.');

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}