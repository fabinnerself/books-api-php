<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Inicializar aplicación
use App\Config\App;
use App\Middleware\ErrorHandler;

App::init();
ErrorHandler::register();

// Router simple para desarrollo local
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/api/v1';

// Remover el base path si existe
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Enrutamiento
switch (true) {
    case $requestUri === '/health':
        $_SERVER['PATH_INFO'] = '';
        require __DIR__ . '/../api/health.php';
        break;
        
    case $requestUri === '/books':
        $_SERVER['PATH_INFO'] = '';
        require __DIR__ . '/../api/books.php';
        break;
        
    case preg_match('/^\/books\/([a-f0-9\-]{36})$/', $requestUri, $matches):
        $_SERVER['PATH_INFO'] = $matches[1];
        require __DIR__ . '/../api/books.php';
        break;
        
    case $requestUri === '/':
        // Página de bienvenida básica
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Books API - Welcome',
            'version' => App::VERSION,
            'endpoints' => [
                'GET /api/v1/health' => 'API health check',
                'GET /api/v1/books' => 'List all books',
                'POST /api/v1/books' => 'Create a new book',
                'GET /api/v1/books/{id}' => 'Get book by ID',
                'PUT /api/v1/books/{id}' => 'Update book by ID',
                'DELETE /api/v1/books/{id}' => 'Delete book by ID'
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        break;
        
    default:
        ErrorHandler::handleNotFound();
}