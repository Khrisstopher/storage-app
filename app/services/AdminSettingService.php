<?php

namespace App\Services;

use App\Models\AdminSettingModel;

require_once __DIR__ . '/../models/AdminSettingModel.php';

/**
 * Servicio de administración de restricciones y permisos.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class AdminSettingService {
    private \PDO $pdo;
    private AdminSettingModel $adminModel;

    /**
     * @param \PDO $pdo Instancia de conexión a la base de datos y carga el servicio de Administración de archivos.
     */
    public function __construct(\PDO $pdo) {
        $this->adminModel = new AdminSettingModel($pdo);
    }

    /**
     * Obtiene la lista de extensiones de archivos no permitidas.
     * * @return string[] Lista de extensiones bloqueadas (ej: ['exe', 'php', 'bat']).
     */
    public function getFileRestrictions(): array {
        $extensions = $this->adminModel->getFileRestrictions();
        if (!$extensions) { return []; };
        return array_map('strtolower', $extensions);
    }

    /**
     * Procesa, valida y guarda las nuevas restricciones de archivos.
     * * @param array $data Datos provenientes del formulario.
     * @return array La lista de extensiones que fueron procesadas y guardadas exitosamente.
     * @throws \Exception Si las validaciones de formato o longitud fallan.
     */
    public function updateFileRestrictions($data) {
        if (!isset($data['extensions'])) {
            throw new \Exception('No se recibieron extensiones.');
        }

        $extensions = trim($data['extensions']);
        if (empty($extensions)) {
            throw new \Exception('Debes ingresar al menos una extensión.');
        }

        $extensionsArray = explode(',', $extensions);
        $extensionsArray = array_values(array_unique(array_filter(
            array_map(fn($ext) => strtolower(trim($ext)), $extensionsArray)
        )));

        foreach ($extensionsArray as $ext) {
            if (!preg_match('/^[a-z0-9]+$/', $ext)) {
                throw new \Exception("La extensión '$ext' no es válida.");
            }
            if (strlen($ext) > 10) {
                throw new \Exception("La extensión '$ext' es demasiado larga.");
            }
        }
        $this->adminModel->saveBlockedExtensions($extensionsArray);

        return $extensionsArray;
    }
}