<?php

namespace App\Helpers;

use App\Config\App;

class Logger
{
    private const LOG_LEVELS = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3
    ];
    
    public static function debug(string $message, array $context = []): void
    {
        if (App::isDebug()) {
            self::log('DEBUG', $message, $context);
        }
    }
    
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }
    
    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }
    
    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }
    
    private static function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        
        $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextString}";
        
        // En serverless, usar error_log para que aparezca en los logs de la plataforma
        error_log($logMessage);
        
        // En desarrollo local, también escribir a archivo si es posible
        if (!App::isProduction()) {
            $logDir = __DIR__ . '/../../logs';
            if (!is_dir($logDir) && is_writable(__DIR__ . '/../..')) {
                mkdir($logDir, 0755, true);
            }
            
            $logFile = $logDir . '/app.log';
            if (is_writable($logDir)) {
                file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
        }
    }
    
    public static function logRequest(string $method, string $uri, array $data = []): void
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        self::info("Request: {$method} {$uri}", [
            'ip' => $ip,
            'user_agent' => substr($userAgent, 0, 100),
            'data' => !empty($data) ? array_keys($data) : []
        ]);
    }
    
    public static function logResponse(int $statusCode, string $message = ''): void
    {
        self::info("Response: {$statusCode}", [
            'message' => $message
        ]);
    }
    
    public static function logException(\Throwable $exception): void
    {
        self::error("Exception: " . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}