# Lista de archivos a crear (con sus rutas)
$files = @(
    "README.md",
    "render.yml",
    "Dockerfile",
    "docker-compose.yml",
    ".gitignore",
    ".env.example",
    "composer.json",
    "scripts/seed_data.php",
    "public/index.php",
    "api/books.php",
    "api/health.php",
    "src/Controllers/BookController.php",
    "src/Models/Book.php",
    "src/Middleware/ErrorHandler.php",
    "src/Helpers/Logger.php",
    "src/Helpers/Validator.php",
    "src/Helpers/Request.php",
    "src/Helpers/Response.php",
    "src/Config/Database.php",
    "src/Config/App.php"
)

# Contador para estadísticas
$createdCount = 0
$skippedCount = 0

Write-Host "🚀 Iniciando creación de estructura de archivos..." -ForegroundColor Cyan

foreach ($file in $files) {
    $dir = Split-Path $file -Parent
    
    # Crear directorio si es necesario
    if ($dir -ne "" -and !(Test-Path $dir)) {
        try {
            New-Item -ItemType Directory -Path $dir -Force -ErrorAction Stop | Out-Null
            Write-Host "📁 Directorio creado: $dir" -ForegroundColor Yellow
        }
        catch {
            Write-Host "❌ Error creando directorio $dir : $($_.Exception.Message)" -ForegroundColor Red
            continue
        }
    }
    
    # Crear archivo si no existe
    if (!(Test-Path $file)) {
        try {
            New-Item -ItemType File -Path $file -Force -ErrorAction Stop | Out-Null
            Write-Host "✅ Archivo creado: $file" -ForegroundColor Green
            $createdCount++
        }
        catch {
            Write-Host "❌ Error creando archivo $file : $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    else {
        Write-Host "⏭️  Archivo ya existe: $file" -ForegroundColor Gray
        $skippedCount++
    }
}

# Mostrar resumen
Write-Host "`n📊 Resumen de la operación:" -ForegroundColor Cyan
Write-Host "✅ Archivos creados: $createdCount" -ForegroundColor Green
Write-Host "⏭️  Archivos existentes: $skippedCount" -ForegroundColor Gray
Write-Host "📂 Total procesados: $($files.Count)" -ForegroundColor White

if ($createdCount -gt 0) {
    Write-Host "`n🎉 ¡Estructura creada exitosamente!" -ForegroundColor Green
}
else {
    Write-Host "`nℹ️  Todos los archivos ya existían." -ForegroundColor Yellow
}