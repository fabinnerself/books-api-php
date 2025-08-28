<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
if (file_exists(__DIR__ . '/../.env') && !isset($_ENV['VERCEL_ENV'])) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Inicializar aplicación
use App\Config\App;
use App\Controllers\BookController;
use App\Middleware\ErrorHandler;

App::init();
ErrorHandler::register();

// Manejar la petición
$controller = new BookController();
$controller->handleRequest();