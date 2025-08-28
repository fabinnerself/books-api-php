-- 1. Crear la base de datos 'library' con codificación UTF8 para soporte de acentos (español)
DROP DATABASE IF EXISTS library;
CREATE DATABASE library
  WITH OWNER = postgres
       ENCODING = 'UTF8'
       LC_COLLATE = 'es_ES.UTF-8'
       LC_CTYPE = 'es_ES.UTF-8'
       TEMPLATE = template0;

-- Nota: Debes conectarte a la base 'library' antes de seguir
-- En psql: \c library

-- 2. Habilitar extensión UUID si se va a usar (opcional, pero recomendado)
-- Descomenta si usas uuid_generate_v4()
-- CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
 

-- 4. Crear tabla 'usuarios'
CREATE TABLE "usuarios" (
  "id_user" uuid PRIMARY KEY DEFAULT gen_random_uuid(), -- Usa gen_random_uuid() en PG 13+
  "name" varchar(100) NOT NULL,
  "lastname" varchar(100),
  "email" varchar(255) UNIQUE NOT NULL,
  "phone" varchar(20),
  "birthday" date,
  "avatar" varchar(255),
  "role" varchar(50) DEFAULT 'user',
  "hashed_password" text NOT NULL,
  "is_active" boolean DEFAULT true,
  "is_verified" boolean DEFAULT false,
  "last_login" timestamp,
  "created_at" timestamp NOT NULL DEFAULT now(),
  "updated_at" timestamp DEFAULT now(),
  "deleted_at" timestamp,
  "is_deleted" boolean DEFAULT false
);

-- 5. Crear tabla 'libros'

CREATE TABLE "libros" (
	 "id_libro" uuid PRIMARY KEY DEFAULT gen_random_uuid(),
	"name" VARCHAR(255) NOT NULL,
	"author" VARCHAR(255) NOT NULL,
	"price" NUMERIC(10,2) NOT NULL,
	"description" TEXT NULL DEFAULT NULL,
	"created_at" TIMESTAMP NULL DEFAULT now(),
	"updated_at" TIMESTAMP NULL DEFAULT now(),
	"id_user" UUID NULL DEFAULT NULL,
	"is_deleted" BOOLEAN NULL DEFAULT false,	
	CONSTRAINT "fk_libros_usuario" FOREIGN KEY ("id_user") REFERENCES "usuarios" ("id_user") ON UPDATE NO ACTION ON DELETE SET NULL
);


-- 7. Insertar 5 usuarios de ejemplo
INSERT INTO "usuarios" (
  "name", "lastname", "email", "phone", "birthday", "avatar", 
  "role", "hashed_password", "is_active", "is_verified", "last_login"
) VALUES
('Juan', 'Pérez', 'juan.perez@email.com', '+57 300 123 4567', '1990-05-15', 'avatar_juan.jpg', 'user', '$2b$12$ejemplohashseguro1', true, true, '2025-08-19 10:30:00'),
('María', 'Gómez', 'maria.gomez@email.com', '+57 301 234 5678', '1985-12-03', 'avatar_maria.png', 'admin', '$2b$12$ejemplohashseguro2', true, true, '2025-08-18 14:20:00'),
('Carlos', 'Ruiz', 'carlos.ruiz@email.com', '+57 302 345 6789', '1992-08-22', NULL, 'user', '$2b$12$ejemplohashseguro3', true, false, '2025-08-17 09:15:00'),
('Ana', 'López', 'ana.lopez@email.com', '+57 303 456 7890', '1988-03-10', 'avatar_ana.jpg', 'user', '$2b$12$ejemplohashseguro4', false, true, NULL),
('Luis', 'Martínez', 'luis.martinez@email.com', '+57 304 567 8901', '1995-11-30', 'avatar_luis.png', 'user', '$2b$12$ejemplohashseguro5', true, true, '2025-08-19 08:45:00');

-- 8. Insertar 5 libros de ejemplo (asignados a usuarios existentes)
-- Nota: Se usa id_user de los usuarios insertados (podemos referenciar por email o usar subconsultas)
INSERT INTO "libros" (
  "name", "author", "price", "description", "id_user"
) VALUES
('El amor en los tiempos del cólera', 'Gabriel García Márquez', 25.50, 'Una novela emblemática del realismo mágico latinoamericano.', (SELECT "id_user" FROM "usuarios" WHERE "email" = 'maria.gomez@email.com')),
('Cien años de soledad', 'Gabriel García Márquez', 30.00, 'La historia de la familia Buendía en Macondo.', (SELECT "id_user" FROM "usuarios" WHERE "email" = 'juan.perez@email.com')),
('La sombra del viento', 'Carlos Ruiz Zafón', 22.00, 'Una misteriosa historia en el Barrio Gótico de Barcelona.', (SELECT "id_user" FROM "usuarios" WHERE "email" = 'carlos.ruiz@email.com')),
('Rayuela', 'Julio Cortázar', 18.75, 'Una novela experimental que invita al lector a elegir su orden.', (SELECT "id_user" FROM "usuarios" WHERE "email" = 'ana.lopez@email.com')),
('El túnel', 'Ernesto Sabato', 15.90, 'Una profunda exploración de la mente de un asesino.', (SELECT "id_user" FROM "usuarios" WHERE "email" = 'luis.martinez@email.com'));
