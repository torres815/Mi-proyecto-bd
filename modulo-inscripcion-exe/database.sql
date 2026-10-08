CREATE DATABASE moduloIncrip;
USE moduloIncrip;

CREATE TABLE rol (
    id_rol INT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

CREATE TABLE usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);

-- Datos de prueba
INSERT INTO rol (id_rol, nombre) VALUES
(1, 'Preceptor/a'),
(2, 'Directivo/a'),
(3, 'Docente');

-- Contraseña de prueba: Preceptor2026 (bcrypt)
INSERT INTO usuario (nombre, apellido, email, password_hash, id_rol, activo) VALUES
('Ana', 'Gómez', 'ana.gomez@escuela.edu.ar',
 '$2b$12$tZ3JZjMqGjIbvC.7Tot.i.VZWP81RVfAMn54ID634JPe6p4hX7gBu', 1, TRUE),
('Luis', 'Pereyra', 'luis.pereyra@escuela.edu.ar',
 '$2b$12$tZ3JZjMqGjIbvC.7Tot.i.VZWP81RVfAMn54ID634JPe6p4hX7gBu', 1, FALSE);
