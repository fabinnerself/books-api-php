<?php

namespace App\Config;

class App
{
    public const VERSION = '1.0.0';
    public const BASE_PATH = '/api/v1';
    public const TIMEZONE = 'UTC';
    public const MAX_PAGE_SIZE = 100;
    public const DEFAULT_PAGE_SIZE = 10;
    
    public static function init(): void
    {
        // Configurar zona horaria
        date_default_timezone_set(self::TIMEZONE);
        
        // Configurar headers CORS básicos
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Configurar encoding
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');
    }
    
    public static function getEnv(string $key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
    
    public static function isProduction(): bool
    {
        return self::getEnv('APP_ENV') === 'production';
    }
    
    public static function isDebug(): bool
    {
        return self::getEnv('APP_DEBUG', 'false') === 'true';
    }
}