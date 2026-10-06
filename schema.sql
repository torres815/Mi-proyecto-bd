-- Base de Datos: Centro Educativo Nonogasta
-- Archivo de prueba para práctica de Git y GitHub

-- Tabla 1: Estudiantes (Cambio inicial para el primer commit)
CREATE TABLE estudiantes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    fecha_nacimiento DATE
);

-- Insertar datos ficticios de prueba
INSERT INTO estudiantes (nombre, apellido, email, fecha_nacimiento) VALUES
('Exe', 'Pérez', 'juan.perez@email.com', '2005-04-12'),
('María', 'Gómez', 'maria.gomez@email.com', '2004-08-25');


-- =======================================================
-- NOTA PARA EL VIDEO TUTORIAL:
-- Copia hasta aquí para tu PRIMER COMMIT.
-- La tabla de abajo agrégala cuando grabes el SEGUNDO COMMIT.
-- =======================================================

-- Tabla 2: Cursos (Segundo cambio para probar el flujo de Git)
CREATE TABLE cursos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_curso VARCHAR(50) NOT NULL,
    profesor VARCHAR(50) NOT NULL
);

-- Insertar curso ficticio
INSERT INTO cursos (nombre_curso, profesor) VALUES
('Base de Datos', 'Alex Perea Lazo');