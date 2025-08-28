<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

use App\Config\App;
use App\Config\Database;
use Ramsey\Uuid\Uuid;

App::init();

echo "🌱 Iniciando inserción de datos de prueba...\n";

try {
    $pdo = Database::getConnection();
    
    // Verificar si ya hay datos
    $stmt = $pdo->query("SELECT COUNT(*) FROM libros WHERE is_deleted = false");
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "⚠️  Ya existen {$count} libros en la base de datos.\n";
        echo "¿Desea continuar y agregar más datos? (y/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim(strtolower($line)) !== 'y') {
            echo "Operación cancelada.\n";
            exit(0);
        }
    }
    
    // Datos de prueba
    $books = [
        [
            'name' => 'Cien años de soledad',
            'author' => 'Gabriel García Márquez',
            'price' => 29.99,
            'description' => 'Una obra maestra del realismo mágico que narra la historia de la familia Buendía.'
        ],
        [
            'name' => 'Don Quijote de la Mancha',
            'author' => 'Miguel de Cervantes',
            'price' => 24.50,
            'description' => 'La historia del ingenioso hidalgo y sus aventuras con Sancho Panza.'
        ],
        [
            'name' => 'El túnel',
            'author' => 'Ernesto Sabato',
            'price' => 19.99,
            'description' => 'Una novela psicológica intensa sobre obsesión y soledad.'
        ],
        [
            'name' => 'Rayuela',
            'author' => 'Julio Cortázar',
            'price' => 26.75,
            'description' => 'Una novela experimental que puede leerse de múltiples maneras.'
        ],
        [
            'name' => 'La casa de los espíritus',
            'author' => 'Isabel Allende',
            'price' => 22.80,
            'description' => 'La saga de una familia a través de varias generaciones.'
        ],
        [
            'name' => 'Pedro Páramo',
            'author' => 'Juan Rulfo',
            'price' => 18.90,
            'description' => 'Un pueblo fantasmal y la búsqueda de un padre ausente.'
        ],
        [
            'name' => 'Ficciones',
            'author' => 'Jorge Luis Borges',
            'price' => 21.60,
            'description' => 'Colección de cuentos que exploran laberintos, espejos y bibliotecas infinitas.'
        ],
        [
            'name' => 'La ciudad y los perros',
            'author' => 'Mario Vargas Llosa',
            'price' => 25.40,
            'description' => 'La vida en un colegio militar y los códigos de honor y violencia.'
        ],
        [
            'name' => 'Como agua para chocolate',
            'author' => 'Laura Esquivel',
            'price' => 20.30,
            'description' => 'Una novela donde la cocina y las emociones se entrelazan mágicamente.'
        ],
        [
            'name' => 'El amor en los tiempos del cólera',
            'author' => 'Gabriel García Márquez',
            'price' => 28.50,
            'description' => 'Una historia de amor que perdura a través del tiempo y las adversidades.'
        ],
        [
            'name' => 'La sombra del viento',
            'author' => 'Carlos Ruiz Zafón',
            'price' => 23.90,
            'description' => 'Un misterio literario ambientado en la Barcelona de posguerra.'
        ],
        [
            'name' => 'El perfume',
            'author' => 'Patrick Süskind',
            'price' => 22.15,
            'description' => 'La historia de un asesino obsesionado con los aromas.'
        ],
        [
            'name' => '1984',
            'author' => 'George Orwell',
            'price' => 19.75,
            'description' => 'Una distopía sobre el totalitarismo y la manipulación de la verdad.'
        ],
        [
            'name' => 'Matar a un ruiseñor',
            'author' => 'Harper Lee',
            'price' => 21.90,
            'description' => 'Una reflexión sobre la injusticia racial en el sur de Estados Unidos.'
        ],
        [
            'name' => 'El gran Gatsby',
            'author' => 'F. Scott Fitzgerald',
            'price' => 20.45,
            'description' => 'La decadencia del sueño americano en los años 20.'
        ]
    ];
    
    $sql = "INSERT INTO libros (id_libro, name, author, price, description) 
            VALUES (:id, :name, :author, :price, :description)";
    
    $stmt = $pdo->prepare($sql);
    
    $inserted = 0;
    
    foreach ($books as $book) {
        try {
            $id = Uuid::uuid4()->toString();
            
            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':name', $book['name']);
            $stmt->bindValue(':author', $book['author']);
            $stmt->bindValue(':price', $book['price']);
            $stmt->bindValue(':description', $book['description']);
            
            $stmt->execute();
            $inserted++;
            
            echo "✅ Insertado: {$book['name']} - {$book['author']}\n";
            
        } catch (PDOException $e) {
            echo "❌ Error insertando {$book['name']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 Proceso completado: {$inserted} libros insertados exitosamente.\n";
    
    // Mostrar estadísticas finales
    $stmt = $pdo->query("SELECT COUNT(*) FROM libros WHERE is_deleted = false");
    $totalBooks = $stmt->fetchColumn();
    
    echo "📊 Total de libros en la base de datos: {$totalBooks}\n";
    
} catch (PDOException $e) {
    echo "❌ Error de conexión a la base de datos: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    exit(1);
}