<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
if (file_exists(__DIR__ . '/../.env') && !isset($_ENV['VERCEL_ENV'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Inicializar aplicación
use App\Config\App;
use App\Config\Database;
use App\Helpers\Response;
use App\Helpers\Logger;
use App\Middleware\ErrorHandler;

App::init();
ErrorHandler::register();

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    if ($method === 'OPTIONS') {
        header('Allow: GET, OPTIONS');
        http_response_code(200);
        exit;
    }
    
    if ($method !== 'GET') {
        Response::methodNotAllowed('Only GET method is allowed for health check');
    }
    
    Logger::logRequest($method, '/health');
    
    // Verificar conexión a base de datos
    $dbStatus = Database::testConnection();
    
    $response = [
        'message' => 'API is running',
        'version' => App::VERSION,
        'timestamp' => date('c'),
        'env' => App::getEnv('APP_ENV', 'development'),
        'database' => $dbStatus
    ];
    
    if ($dbStatus['success']) {
        Logger::logResponse(200, 'Health check passed');
        Response::success($response);
    } else {
        Logger::logResponse(503, 'Database connection failed');
        Response::json([
            'success' => false,
            'message' => 'Service partially unavailable',
            'details' => $response
        ], 503);
    }
    
} catch (\Throwable $e) {
    Logger::logException($e);
    
    if (App::isDebug()) {
        Response::error("Health check failed: " . $e->getMessage(), 500);
    } else {
        Response::serverError('Health check failed');
    }
}