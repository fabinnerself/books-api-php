<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Config\Database;
use App\Helpers\Logger;
use Ramsey\Uuid\Uuid;

class Book
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    
    public function getAll(int $offset = 0, int $limit = 10, ?string $searchTerm = null, array $filters = []): array
    {
        $whereConditions = ["is_deleted = false"];
        $params = [];
        
        // Búsqueda textual en name y author
        if ($searchTerm) {
            $whereConditions[] = "(name ILIKE :search OR author ILIKE :search)";
            $params[':search'] = '%' . $searchTerm . '%';
        }
        
        // Filtros por precio
        if (isset($filters['min_price'])) {
            $whereConditions[] = "price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (isset($filters['max_price'])) {
            $whereConditions[] = "price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        // Filtro por autor
        if (isset($filters['author'])) {
            $whereConditions[] = "author ILIKE :author";
            $params[':author'] = '%' . $filters['author'] . '%';
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Consulta principal con paginación
        $sql = "SELECT * FROM libros WHERE {$whereClause} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            // Bind de parámetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $books = $stmt->fetchAll();
            
            // Contar total de registros
            $countSql = "SELECT COUNT(*) FROM libros WHERE {$whereClause}";
            $countStmt = $this->db->prepare($countSql);
            
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            
            $countStmt->execute();
            $total = $countStmt->fetchColumn();
            
            return [
                'books' => $this->formatBooks($books),
                'total' => (int) $total
            ];
            
        } catch (PDOException $e) {
            Logger::error("Error fetching books: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getById(string $id): ?array
    {
        $sql = "SELECT * FROM libros WHERE id_libro = :id AND is_deleted = false";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            
            $book = $stmt->fetch();
            
            return $book ? $this->formatBook($book) : null;
            
        } catch (PDOException $e) {
            Logger::error("Error fetching book by ID: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function create(array $data): array
    {
        $id = Uuid::uuid4()->toString();
        
        $sql = "INSERT INTO libros (id_libro, name, author, price, description,id_user) 
                VALUES (:id, :name, :author, :price, :description,'262786f6-a6bf-4249-a709-4229be7c39f1') 
                RETURNING *";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':author', $data['author']);
            $stmt->bindValue(':price', $data['price']);
            $stmt->bindValue(':description', $data['description'] ?? null); 
            
            $stmt->execute();
            $book = $stmt->fetch();
            
            Logger::info("Book created successfully", ['id' => $id]);
            
            return $this->formatBook($book);
            
        } catch (PDOException $e) {
            Logger::error("Error creating book: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function update(string $id, array $data): ?array
    {
        // Verificar que el libro existe y no está eliminado
        if (!$this->getById($id)) {
            return null;
        }
        
        $updateFields = [];
        $params = [':id' => $id];
        
        // Solo actualizar campos que se proporcionan
        if (isset($data['name'])) {
            $updateFields[] = 'name = :name';
            $params[':name'] = $data['name'];
        }
        
        if (isset($data['author'])) {
            $updateFields[] = 'author = :author';
            $params[':author'] = $data['author'];
        }
        
        if (isset($data['price'])) {
            $updateFields[] = 'price = :price';
            $params[':price'] = $data['price'];
        }
        
        if (array_key_exists('description', $data)) {
            $updateFields[] = 'description = :description';
            $params[':description'] = $data['description'];
        }
        
        if (empty($updateFields)) {
            // No hay nada que actualizar
            return $this->getById($id);
        }
        
        $sql = "UPDATE libros SET " . implode(', ', $updateFields) . " 
                WHERE id_libro = :id AND is_deleted = false 
                RETURNING *";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $book = $stmt->fetch();
            
            Logger::info("Book updated successfully", ['id' => $id]);
            
            return $book ? $this->formatBook($book) : null;
            
        } catch (PDOException $e) {
            Logger::error("Error updating book: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function softDelete(string $id): bool
    {
        $sql = "UPDATE libros SET is_deleted = true 
                WHERE id_libro = :id AND is_deleted = false";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            
            $affected = $stmt->rowCount();
            
            if ($affected > 0) {
                Logger::info("Book soft deleted successfully", ['id' => $id]);
                return true;
            }
            
            return false;
            
        } catch (PDOException $e) {
            Logger::error("Error soft deleting book: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function exists(string $id): bool
    {
        $sql = "SELECT 1 FROM libros WHERE id_libro = :id AND is_deleted = false";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            
            return $stmt->fetchColumn() !== false;
            
        } catch (PDOException $e) {
            Logger::error("Error checking book existence: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function formatBook(array $book): array
    {
        return [
            'id_libro' => $book['id_libro'],
            'name' => $book['name'],
            'author' => $book['author'],
            'price' => (float) $book['price'],
            'description' => $book['description'],
            'created_at' => $book['created_at'],
            'updated_at' => $book['updated_at'],
            'id_user' => $book['id_user']
        ];
    }
    
    private function formatBooks(array $books): array
    {
        return array_map([$this, 'formatBook'], $books);
    }
}   