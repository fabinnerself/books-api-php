# Crear directorios principales
mkdir -p api
mkdir -p src/Config src/Controllers src/Models src/Helpers src/Middleware
mkdir -p scripts
mkdir -p public
mkdir -p tests

# Crear archivos en api/
touch api/health.php
touch api/books.php

# Crear archivos en src/ (vacíos para que los llenes después)
touch src/Config/Database.php
touch src/Config/App.php

touch src/Controllers/BookController.php

touch src/Models/Book.php

touch src/Helpers/Response.php
touch src/Helpers/Request.php
touch src/Helpers/Validator.php
touch src/Helpers/Logger.php

touch src/Middleware/ErrorHandler.php

# Crear scripts
touch scripts/create_tables.sql
touch scripts/seed_data.php

# Crear punto de entrada para desarrollo local
touch public/index.php

# Crear tests (futuro)
touch tests/BookTest.php

# Opcional: crear archivos base del proyecto
touch .env
touch .env.example
touch .gitignore
touch composer.json
touch README.md