<?php

namespace App\Helpers;

class Request
{
    private array $data;
    private array $queryParams;
    
    public function __construct()
    {
        $this->queryParams = $_GET;
        $this->data = $this->parseBody();
    }
    
    private function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $decoded = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON data');
            }
            
            return $decoded ?? [];
        }
        
        return $_POST;
    }
    
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
    
    public function query(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }
    
    public function all(): array
    {
        return $this->data;
    }
    
    public function queryAll(): array
    {
        return $this->queryParams;
    }
    
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }
    
    public function hasQuery(string $key): bool
    {
        return isset($this->queryParams[$key]);
    }
    
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    public function getUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '';
    }
    
    public function getPathInfo(): string
    {
        return $_SERVER['PATH_INFO'] ?? '';
    }
    
    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    public function getIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] 
            ?? $_SERVER['HTTP_X_REAL_IP'] 
            ?? $_SERVER['REMOTE_ADDR'] 
            ?? 'unknown';
    }
    
    // Pagination helpers
    public function getPage(): int
    {
        $page = (int) $this->query('page', 1);
        return max(1, $page);
    }
    
    public function getLimit(): int
    {
        $limit = (int) $this->query('limit', \App\Config\App::DEFAULT_PAGE_SIZE);
        return min(\App\Config\App::MAX_PAGE_SIZE, max(1, $limit));
    }
    
    public function getOffset(): int
    {
        return ($this->getPage() - 1) * $this->getLimit();
    }
    
    // Search and filters
    public function getSearchTerm(): ?string
    {
        $term = $this->query('q');
        return $term ? trim($term) : null;
    }
    
    public function getFilters(): array
    {
        $filters = [];
        
        if ($minPrice = $this->query('min_price')) {
            $filters['min_price'] = (float) $minPrice;
        }
        
        if ($maxPrice = $this->query('max_price')) {
            $filters['max_price'] = (float) $maxPrice;
        }
        
        if ($author = $this->query('author')) {
            $filters['author'] = trim($author);
        }
        
        return $filters;
    }
}