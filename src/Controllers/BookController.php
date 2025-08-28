<?php

namespace App\Controllers;

use App\Models\Book;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Validator;
use App\Helpers\Logger;
use App\Middleware\ErrorHandler;

class BookController
{
    private Book $bookModel;
    private Request $request;
    
    public function __construct()
    {
        $this->bookModel = new Book();
        $this->request = new Request();
    }
    
    public function handleRequest(): void
    {
        ErrorHandler::wrapInTryCatch(function () {
            $method = $this->request->getMethod();
            $pathInfo = trim($this->request->getPathInfo(), '/');
            
            // Log de la petición
            Logger::logRequest($method, $pathInfo, $this->request->all());
            
            // Enrutamiento basado en el método y path
            if ($pathInfo === '') {
                // /api/v1/books
                $this->handleCollectionEndpoint($method);
            } else {
                // /api/v1/books/{id}
                $this->handleResourceEndpoint($method, $pathInfo);
            }
        });
    }
    
    private function handleCollectionEndpoint(string $method): void
    {
        switch ($method) {
            case 'GET':
                $this->index();
                break;
                
            case 'POST':
                $this->store();
                break;
                
            case 'OPTIONS':
                $this->handleOptions();
                break;
                
            default:
                ErrorHandler::handleMethodNotAllowed(['GET', 'POST', 'OPTIONS']);
        }
    }
    
    private function handleResourceEndpoint(string $method, string $id): void
    {
        // Validar UUID
        if (!Validator::isValidUuid($id)) {
            Response::error('Invalid book ID format', 400);
            return;
        }
        
        switch ($method) {
            case 'GET':
                $this->show($id);
                break;
                
            case 'PUT':
                $this->update($id);
                break;
                
            case 'DELETE':
                $this->destroy($id);
                break;
                
            case 'OPTIONS':
                $this->handleOptions();
                break;
                
            default:
                ErrorHandler::handleMethodNotAllowed(['GET', 'PUT', 'DELETE', 'OPTIONS']);
        }
    }
    
    public function index(): void
    {
        $page = $this->request->getPage();
        $limit = $this->request->getLimit();
        $offset = $this->request->getOffset();
        $searchTerm = $this->request->getSearchTerm();
        $filters = $this->request->getFilters();
        
        $result = $this->bookModel->getAll($offset, $limit, $searchTerm, $filters);
        
        $pagination = [
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'total_pages' => (int) ceil($result['total'] / $limit)
        ];
        
        Logger::logResponse(200, "Books retrieved: " . count($result['books']));
        Response::successWithPagination($result['books'], $pagination);
    }
    
    public function show(string $id): void
    {
        $book = $this->bookModel->getById($id);
        
        if (!$book) {
            Logger::logResponse(404, "Book not found: {$id}");
            Response::notFound('Book not found');
            return;
        }
        
        Logger::logResponse(200, "Book retrieved: {$id}");
        Response::success($book);
    }
    
    public function store(): void
    {
        $data = $this->request->all();
        
        // Sanitizar datos de entrada
        $data['name'] = Validator::sanitizeString($data['name'] ?? null);
        $data['author'] = Validator::sanitizeString($data['author'] ?? null);
        $data['description'] = Validator::sanitizeString($data['description'] ?? null);
        
        // Validar datos
        $errors = Validator::validateBookData($data);
        
        if (!empty($errors)) {
            Logger::logResponse(422, "Validation failed for book creation");
            Response::validationError($errors);
            return;
        }
        
        // Crear el libro
        $book = $this->bookModel->create($data);
        
        Logger::logResponse(201, "Book created: " . $book['id_libro']);
        Response::success($book, 'Book created successfully', 201);
    }
    
    public function update(string $id): void
    {
        $data = $this->request->all();
        
        // Verificar que el libro existe
        if (!$this->bookModel->exists($id)) {
            Logger::logResponse(404, "Book not found for update: {$id}");
            Response::notFound('Book not found');
            return;
        }
        
        // Filtrar solo los campos permitidos para actualización
        $allowedFields = ['name', 'author', 'price', 'description'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        if (empty($updateData)) {
            Response::error('No valid fields provided for update', 400);
            return;
        }
        
        // Sanitizar datos de entrada
        if (isset($updateData['name'])) {
            $updateData['name'] = Validator::sanitizeString($updateData['name']);
        }
        if (isset($updateData['author'])) {
            $updateData['author'] = Validator::sanitizeString($updateData['author']);
        }
        if (isset($updateData['description'])) {
            $updateData['description'] = Validator::sanitizeString($updateData['description']);
        }
        
        // Validar datos
        $errors = Validator::validateBookData($updateData, true);
        
        if (!empty($errors)) {
            Logger::logResponse(422, "Validation failed for book update: {$id}");
            Response::validationError($errors);
            return;
        }
        
        // Actualizar el libro
        $book = $this->bookModel->update($id, $updateData);
        
        if (!$book) {
            Logger::logResponse(404, "Book not found after update: {$id}");
            Response::notFound('Book not found');
            return;
        }
        
        Logger::logResponse(200, "Book updated: {$id}");
        Response::success($book, 'Book updated successfully');
    }
    
    public function destroy(string $id): void
    {
        $deleted = $this->bookModel->softDelete($id);
        
        if (!$deleted) {
            Logger::logResponse(404, "Book not found for deletion: {$id}");
            Response::notFound('Book not found');
            return;
        }
        
        Logger::logResponse(200, "Book soft deleted: {$id}");
        Response::success(null, 'Book deleted successfully');
    }
    
    private function handleOptions(): void
    {
        header('Allow: GET, POST, PUT, DELETE, OPTIONS');
        http_response_code(200);
        exit;
    }
}