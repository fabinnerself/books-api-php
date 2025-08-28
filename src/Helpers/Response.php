<?php

namespace App\Helpers;

class Response
{
    public static function json(array $data, int $httpCode = 200): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    public static function success(mixed $data = null, string $message = null, int $httpCode = 200): void
    {
        $response = ['success' => true];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        self::json($response, $httpCode);
    }
    
    public static function successWithPagination(array $data, array $pagination, string $message = null): void
    {
        $response = [
            'success' => true,
            'data' => $data,
            'pagination' => $pagination
        ];
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        self::json($response, 200);
    }
    
    public static function error(string $message, int $httpCode = 400, string $code = null): void
    {
        $response = [
            'success' => false,
            'error' => $message
        ];
        
        if ($code !== null) {
            $response['code'] = $code;
        }
        
        self::json($response, $httpCode);
    }
    
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404, '404');
    }
    
    public static function methodNotAllowed(string $message = 'Method not allowed'): void
    {
        self::error($message, 405, '405');
    }
    
    public static function validationError(array $errors): void
    {
        $response = [
            'success' => false,
            'error' => 'Validation failed',
            'details' => $errors
        ];
        
        self::json($response, 422);
    }
    
    public static function serverError(string $message = 'Internal server error'): void
    {
        self::error($message, 500, '500');
    }
    
    public static function health(): void
    {
        self::success([
            'message' => 'API is running',
            'version' => \App\Config\App::VERSION,
            'timestamp' => date('c'),
            'env' => \App\Config\App::getEnv('APP_ENV', 'development')
        ]);
    }
}