<?php

namespace App\Middleware;

use App\Helpers\Logger;
use App\Helpers\Response;
use App\Config\App;

class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleException(\Throwable $exception): void
    {
        Logger::logException($exception);
        
        if (App::isDebug()) {
            Response::error(
                $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine(),
                500
            );
        } else {
            Response::serverError('An internal error occurred');
        }
    }
    
    public static function handleError(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorMessage = "PHP Error: {$message} in {$file} on line {$line}";
        Logger::error($errorMessage);
        
        if (App::isDebug()) {
            Response::error($errorMessage, 500);
        } else {
            Response::serverError();
        }
        
        return true;
    }
    
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $errorMessage = "Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}";
            Logger::error($errorMessage);
            
            // Solo mostrar error si aún no se han enviado headers
            if (!headers_sent()) {
                if (App::isDebug()) {
                    Response::error($errorMessage, 500);
                } else {
                    Response::serverError();
                }
            }
        }
    }
    
    public static function handleNotFound(): void
    {
        Logger::info("404 Not Found: " . ($_SERVER['REQUEST_URI'] ?? ''));
        Response::notFound('Endpoint not found');
    }
    
    public static function handleMethodNotAllowed(array $allowedMethods = []): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        Logger::info("405 Method Not Allowed: {$method}");
        
        if (!empty($allowedMethods)) {
            header('Allow: ' . implode(', ', $allowedMethods));
        }
        
        Response::methodNotAllowed("Method {$method} not allowed");
    }
    
    public static function wrapInTryCatch(callable $callback): void
    {
        try {
            $callback();
        } catch (\InvalidArgumentException $e) {
            Logger::warning("Validation error: " . $e->getMessage());
            Response::error($e->getMessage(), 400);
        } catch (\PDOException $e) {
            Logger::error("Database error: " . $e->getMessage());
            if (App::isDebug()) {
                Response::error("Database error: " . $e->getMessage(), 500);
            } else {
                Response::serverError('Database operation failed');
            }
        } catch (\Throwable $e) {
            self::handleException($e);
        }
    }
}