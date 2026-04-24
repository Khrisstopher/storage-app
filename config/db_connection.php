<?php
// Conexión a base de datos

class Database {
    private PDO $pdo;
    private string $host = "localhost";
    private string $dbName = "storage_app";
    private string $username = "root";
    private string $password = "";

    public function __construct() {

        try {
            $this->pdo = new PDO(
                "mysql:host=$this->host;dbname=$this->dbName", 
                $this->username, 
                $this->password
            );

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Resultados como array asociativo
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Preparaciones reales

            //echo "Conexión a la base de datos establecida exitosamente!!!.";
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    // Método para obtener la conexión
    public function getConnection(): PDO {
        return $this->pdo;
    }
}