<?php

namespace App\Config;

/**
 * Archivo de conexión a la BD.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class Database {
    private \PDO $pdo;

    public function __construct() {

        try {
            $this->pdo = new \PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, 
                DB_USER, 
                DB_PASS
            );

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC); // Resultados como array asociativo
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); // Preparaciones reales

            //echo "Conexión a la base de datos establecida exitosamente!!!.";
        } catch (\PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    // Método para obtener la conexión
    public function getConnection(): \PDO {
        return $this->pdo;
    }
}