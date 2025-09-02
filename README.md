# 📚 Lybrasy API - RESTful API en PHP Nativo, backend lybrary ne php neon.tech console postgreSQL BE-LPPN

Una API RESTful completa para la gestión de libros, desarrollada en PHP nativo con PostgreSQL, diseñada para ejecutarse en entornos serverless como Vercel.

#🔗 Rutas Está disponible el siguiente recurso:

https://books-api-php.onrender.com/

## ✨ Características

- ✅ **API RESTful completa** con operaciones CRUD
- ✅ **PHP 8.0+ nativo** sin frameworks
- ✅ **PostgreSQL** como base de datos
- ✅ **Serverless Functions** compatible con Vercel
- ✅ **Paginación** eficiente
- ✅ **Búsqueda textual** en nombre y autor
- ✅ **Filtros avanzados** por precio y autor
- ✅ **Validación robusta** de datos
- ✅ **Soft delete** para libros
- ✅ **Logging** integrado
- ✅ **Manejo de errores** centralizado
- ✅ **UUIDs** como identificadores
- ✅ **CORS** habilitado
- ✅ **Variables de entorno** para configuración

## 🏗️ Arquitectura

```
/
├── api/                     # Serverless Functions
│   ├── health.php          # Endpoint de salud
│   └── books.php           # Endpoints de libros
├── src/
│   ├── Config/             # Configuración
│   ├── Controllers/        # Lógica de negocio
│   ├── Models/            # Modelos de datos
│   ├── Helpers/           # Utilidades
│   └── Middleware/        # Middleware
├── scripts/               # Scripts de BD
├── public/                # Router para desarrollo
└── tests/                # Tests (futuro)
```

## 🚀 Instalación

### Prerrequisitos

- PHP 8.0+
- PostgreSQL 12+
- Composer
- Extensiones: `pdo`, `pdo_pgsql`, `json`, `mbstring`

### 1. Clonar el repositorio

```bash
git clone <repository-url>
cd books-api
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar variables de entorno

```bash
cp .env.example .env
```

Editar `.env` con tus credenciales:

```env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=library
DB_USER=postgres
DB_PASSWORD=tu_password
DB_SSLMODE=disable

APP_ENV=development
APP_DEBUG=true
```

### 4. Crear la base de datos

```bash
# Conectarse a PostgreSQL y ejecutar:
psql -U postgres -f scripts/create_tables.sql
```

### 5. Insertar datos de prueba (opcional)

```bash
php scripts/seed_data.php
```

## 🏃‍♂️ Ejecutar localmente

```bash
# Opción 1: Con Composer
composer start

# Opción 2: Con PHP nativo
php -S localhost:8000 -t public

# Opción 3: Con Docker
docker-compose up
```

La API estará disponible en `http://localhost:8000`

temp: http://localhost/p/pg/belppn/public/

## 📡 Endpoints

### Health Check

```http
GET /api/v1/health
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "message": "API is running",
    "version": "1.0.0",
    "timestamp": "2025-01-28T10:00:00Z",
    "env": "development",
    "database": {
      "success": true,
      "version": "PostgreSQL 15.0"
    }
  }
}
```

### Listar Libros

```http
GET /api/v1/books?page=1&limit=10&q=garcia&min_price=20&max_price=50&author=gabriel
```

**Parámetros de consulta:**
- `page`: Número de página (default: 1)
- `limit`: Elementos por página (default: 10, max: 100)
- `q`: Búsqueda textual en nombre y autor
- `min_price`: Precio mínimo
- `max_price`: Precio máximo  
- `author`: Filtrar por autor (búsqueda parcial)

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id_libro": "uuid-here",
      "name": "Cien años de soledad",
      "author": "Gabriel García Márquez",
      "price": 29.99,
      "description": "Una obra maestra...",
      "created_at": "2025-01-28T10:00:00Z",
      "updated_at": "2025-01-28T10:00:00Z",
      "id_user": null
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 10,
    "total": 150,
    "total_pages": 15
  }
}
```

### Obtener Libro por ID

```http
GET /api/v1/books/{id}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": { ... }
}
```

**No encontrado (404):**
```json
{
  "success": false,
  "error": "Book not found",
  "code": "404"
}
```

### Crear Libro

```http
POST /api/v1/books
Content-Type: application/json

{
  "name": "El túnel",
  "author": "Ernesto Sabato", 
  "price": 19.99,
  "description": "Una novela psicológica intensa"
}
```

**Validaciones:**
- `name`: requerido, string, 3-255 caracteres
- `author`: requerido, string, 3-255 caracteres  
- `price`: requerido, número positivo, máx 2 decimales
- `description`: opcional, string, máx 1000 caracteres

**Respuesta (201):**
```json
{
  "success": true,
  "data": { ... },
  "message": "Book created successfully"
}
```

### Actualizar Libro

```http
PUT /api/v1/books/{id}
Content-Type: application/json

{
  "name": "Nuevo título",
  "price": 25.50
}
```

Solo se actualizan los campos enviados. Respuesta similar al GET.

### Eliminar Libro (Soft Delete)

```http
DELETE /api/v1/books/{id}
```

**Respuesta (200):**
```json
{
  "success": true,
  "message": "Book deleted successfully"
}
```

## 🌐 Despliegue

### Vercel

1. **Configurar variables de entorno en Vercel:**
   ```
   DB_HOST=your-neon-endpoint.neon.tech
   DB_NAME=library
   DB_USER=your-user
   DB_PASSWORD=your-password
   DB_SSLMODE=require
   APP_ENV=production
   ```

2. **Desplegar:**
   ```bash
   vercel --prod
   ```

### Render

1. **Conectar repositorio en Render**
2. **Configurar variables de entorno**
3. **Deploy automático**

### Docker

```bash
docker build -t books-api .
docker run -p 8000:8000 books-api
```

## 🧪 Ejemplos de Uso

### Con curl

```bash
# Health check
curl -X GET http://localhost:8000/api/v1/health

# Listar libros con filtros
curl -X GET "http://localhost:8000/api/v1/books?page=1&limit=5&q=garcia"

# Crear libro
curl -X POST http://localhost:8000/api/v1/books \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Nuevo libro",
    "author": "Autor Ejemplo", 
    "price": 25.50,
    "description": "Descripción del libro"
  }'

# Obtener libro
curl -X GET http://localhost:8000/api/v1/books/{uuid}

# Actualizar libro
curl -X PUT http://localhost:8000/api/v1/books/{uuid} \
  -H "Content-Type: application/json" \
  -d '{"price": 30.00}'

# Eliminar libro
curl -X DELETE http://localhost:8000/api/v1/books/{uuid}
```

### Con JavaScript (fetch)

```javascript
// Crear libro
const response = await fetch('/api/v1/books', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'Mi libro',
    author: 'Mi autor',
    price: 19.99,
    description: 'Descripción'
  })
});

const data = await response.json();
console.log(data);
```

## 🔧 Configuración Avanzada

### Variables de Entorno

| Variable | Descripción | Default |
|----------|-------------|---------|
| `DB_HOST` | Host de PostgreSQL | localhost |
| `DB_PORT` | Puerto de PostgreSQL | 5432 |
| `DB_NAME` | Nombre de la BD | library |
| `DB_USER` | Usuario de BD | postgres |
| `DB_PASSWORD` | Contraseña | (vacío) |
| `DB_SSLMODE` | Modo SSL | disable |
| `APP_ENV` | Entorno | development |
| `APP_DEBUG` | Debug activo | false |

### Neon.tech (Producción)

Para Neon.tech, configurar:
```env
DB_HOST=your-endpoint-id.neon.tech
DB_SSLMODE=require
```

La conexión se configurará automáticamente con SSL y endpoint.

## 🧪 Testing

```bash
# Ejecutar tests (futuro)
composer test

# Verificar sintaxis PHP
find src -name "*.php" -exec php -l {} \;
```

## 📊 Monitoreo y Logs

Los logs se escriben a:
- `error_log()` de PHP (serverless)
- `logs/app.log` (desarrollo local)

Ejemplo de logs:
```
[2025-01-28 10:00:00] [INFO] Request: GET /books {"ip": "127.0.0.1"}
[2025-01-28 10:00:01] [INFO] Books retrieved: 10
```

## ⚡ Rendimiento

- **Conexión BD**: Pool de conexiones con PDO
- **Paginación**: LIMIT/OFFSET eficiente
- **Índices**: En campos de búsqueda frecuente
- **Cache**: Headers de cache para endpoints estáticos

## 🔒 Seguridad

- ✅ **SQL Injection**: Consultas preparadas (PDO)
- ✅ **XSS**: Sanitización con `htmlspecialchars`
- ✅ **Validación**: Entrada validada y tipificada
- ✅ **CORS**: Headers configurados
- ✅ **UUID**: IDs no secuenciales
- ✅ **Soft Delete**: Datos preservados

## 🚦 Estados HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Operación exitosa |
| 201 | Created - Recurso creado |
| 400 | Bad Request - Datos inválidos |
| 404 | Not Found - Recurso no encontrado |
| 405 | Method Not Allowed |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Internal Server Error |

## 🤝 Contribución

1. Fork del proyecto
2. Crear rama de feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit cambios (`git commit -am 'Agregar funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)  
5. Crear Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE.md](LICENSE.md) para detalles.

## 🆘 Soporte

Si encuentras algún problema:

1. Revisa la [documentación](#)
2. Verifica los [logs](#-monitoreo-y-logs)
3. Crea un [issue](https://github.com/tu-usuario/books-api/issues)

---

**Desarrollado con ❤️ en PHP nativo**
