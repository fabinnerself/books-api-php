#!/bin/bash

# Lista de archivos a crear (con sus rutas)
files=(
    "README.md"
    "render.yml"
    "Dockerfile"
    "docker-compose.yml"
    ".gitignore"
    ".env.example"
    "composer.json"
    "scripts/seed_data.php"
    "public/index.php"
    "api/books.php"
    "api/health.php"
    "src/Controllers/BookController.php"
    "src/Models/Book.php"
    "src/Middleware/ErrorHandler.php"
    "src/Helpers/Logger.php"
    "src/Helpers/Validator.php"
    "src/Helpers/Request.php"
    "src/Helpers/Response.php"
    "src/Config/Database.php"
    "src/Config/App.php"
)

# Contador para estadísticas
created_count=0
skipped_count=0

echo -e "\033[36m🚀 Iniciando creación de estructura de archivos...\033[0m"

for file in "${files[@]}"; do
    # Extraer el directorio
    dir=$(dirname "$file")
    
    # Crear directorio si es necesario y no está vacío
    if [ -n "$dir" ] && [ ! -d "$dir" ]; then
        if mkdir -p "$dir" 2>/dev/null; then
            echo -e "\033[33m📁 Directorio creado: $dir\033[0m"
        else
            echo -e "\033[31m❌ Error creando directorio $dir\033[0m"
            continue
        fi
    fi
    
    # Crear archivo si no existe
    if [ ! -f "$file" ]; then
        if touch "$file" 2>/dev/null; then
            echo -e "\033[32m✅ Archivo creado: $file\033[0m"
            ((created_count++))
        else
            echo -e "\033[31m❌ Error creando archivo $file\033[0m"
        fi
    else
        echo -e "\033[90m⏭️  Archivo ya existe: $file\033[0m"
        ((skipped_count++))
    fi
done

# Mostrar resumen
echo -e "\n\033[36m📊 Resumen de la operación:\033[0m"
echo -e "\033[32m✅ Archivos creados: $created_count\033[0m"
echo -e "\033[90m⏭️  Archivos existentes: $skipped_count\033[0m"
echo -e "\033[37m📂 Total procesados: ${#files[@]}\033[0m"

if [ "$created_count" -gt 0 ]; then
    echo -e "\n\033[32m🎉 ¡Estructura creada exitosamente!\033[0m"
else
    echo -e "\n\033[33mℹ️  Todos los archivos ya existían.\033[0m""
fi