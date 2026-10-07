-- Base de datos del Módulo de Inscripción - CEN
CREATE DATABASE IF NOT EXISTS moduloIncrip CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE moduloIncrip;

DROP TABLE IF EXISTS usuario;
DROP TABLE IF EXISTS rol;

CREATE TABLE rol (
    id_rol INT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE usuario (
    id_usuario    INT PRIMARY KEY AUTO_INCREMENT,
    nombre        VARCHAR(100) NOT NULL,
    apellido      VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    id_rol        INT NOT NULL,
    activo        BOOLEAN DEFAULT TRUE,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
) ENGINE=InnoDB;

INSERT INTO rol (id_rol, nombre) VALUES
(1, 'Administrador'),
(2, 'Preceptoría');

-- Contraseñas con bcrypt (compatibles con password_verify de PHP):
--   silvana@cen.edu.ar      -> Silvana2026!
--   preceptoria@cen.edu.ar  -> Preceptoria2026!
INSERT INTO usuario (nombre, apellido, email, password_hash, id_rol, activo) VALUES
('Silvana', 'Preceptora', 'silvana@cen.edu.ar', '$2b$10$kgF4P71U.xp/aMEP0sVxqOlR6jZFY3cmgcuXTJ9ePHLvGZf2Xqa.W', 2, TRUE),
('Preceptoría', 'CEN', 'preceptoria@cen.edu.ar', '$2b$10$ByteFT/7eXXxFGb4Q9rTVeJMSHNvI24nXsL4fkflM3cxGGEVAiqwi', 2, TRUE);
