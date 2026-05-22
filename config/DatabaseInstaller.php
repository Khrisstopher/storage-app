<?php

namespace App\Config;

/**
 * Clase para la instalación automática de la base de datos si no existe.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class DatabaseInstaller {
    
    /**
     * Revisa si la base de datos existe y tiene tablas. 
     * Si está vacía, ejecuta el archivo consultas.sql de forma automática.
     */
    public static function checkAndInstall() {
        try {
            // Nos conectamos al servidor de MySQL de XAMPP
            $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
            
            $pdo = new \PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
            
            $pdo->exec("USE `" . DB_NAME . "`;");

            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            $tablesExist = $stmt->fetch();

            if (!$tablesExist) {
                $sqlPath = ROOT_PATH . '/sql/consultas.sql';
                
                if (!file_exists($sqlPath)) {
                    throw new \Exception("No se encontró el archivo SQL de instalación en: " . $sqlPath);
                }

                $sqlContent = file_get_contents($sqlPath);
                $pdo->exec($sqlContent);
                
                error_log("INSTALADOR AUTOMÁTICO: Base de datos configurada e instalada con éxito.");
            }
            
        } catch (\PDOException $e) {
            error_log("ERROR DE CONEXIÓN EN INSTALADOR: " . $e->getMessage());
            die("Error de conexión al servidor de bases de datos. Asegúrate de que XAMPP (MySQL) esté encendido.");
        } catch (\Exception $e) {
            error_log("ERROR CRÍTICO EN INSTALACIÓN: " . $e->getMessage());
            die("Error crítico durante la instalación automática: " . $e->getMessage());
        }
    }
}