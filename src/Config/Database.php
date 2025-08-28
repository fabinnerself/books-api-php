<?php

namespace App\Config;

use PDO;
use PDOException;
use App\Helpers\Logger;

class Database
{
    private static ?PDO $instance = null;
    
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }
        
        return self::$instance;
    }
    
    private static function createConnection(): PDO
    {
        $host = App::getEnv('DB_HOST', 'localhost');
        $port = App::getEnv('DB_PORT', '5432');
        $dbname = App::getEnv('DB_NAME', 'library');
        $user = App::getEnv('DB_USER', 'postgres');
        $password = App::getEnv('DB_PASSWORD', '');
        $sslmode = App::getEnv('DB_SSLMODE', 'disable');
        
        try {
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
            
            // Configuración especial para Neon.tech
            if (strpos($host, 'neon.tech') !== false) {
                $dsn .= ";sslmode=require";
                
                // Extraer endpoint ID del host
                $endpoint_id = explode('.', $host)[0];
                $dsn .= ";options=endpoint={$endpoint_id}";
                
                Logger::info("Neon.tech detected - Endpoint: {$endpoint_id}");
            } else {
                $dsn .= ";sslmode={$sslmode}";
            }
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 30,
                PDO::ATTR_PERSISTENT => false
            ];
            
            $pdo = new PDO($dsn, $user, $password, $options);
            
            Logger::info("Database connection established successfully");
            
            return $pdo;
            
        } catch (PDOException $e) {
            Logger::error("Database connection failed: " . $e->getMessage());
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function testConnection(): array
    {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SELECT version() as version, now() as current_time");
            $result = $stmt->fetch();
            
            return [
                'success' => true,
                'version' => $result['version'],
                'current_time' => $result['current_time']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}