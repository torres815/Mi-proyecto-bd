-- Tablas del formulario de inscripción - CEN
-- Ejecutar DESPUÉS de moduloIncrip.sql (necesita la tabla usuario).
USE moduloIncrip;

CREATE TABLE IF NOT EXISTS alumno (
    id_alumno           INT PRIMARY KEY AUTO_INCREMENT,
    apellido_nombres    VARCHAR(150) NOT NULL,
    dni                 VARCHAR(10)  NOT NULL UNIQUE,
    cuil                VARCHAR(13)  NULL,
    fecha_nacimiento    DATE         NOT NULL,
    lugar_nacimiento    VARCHAR(120) NULL,
    escuela_origen      VARCHAR(150) NULL,
    foto_path           VARCHAR(255) NULL,
    observaciones_salud TEXT         NULL,
    id_usuario          INT          NOT NULL,
    creado_en           TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_alumno_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tutor (
    id_tutor           INT PRIMARY KEY AUTO_INCREMENT,
    id_alumno          INT          NOT NULL,
    tipo               ENUM('madre','padre') NOT NULL,
    apellido_nombres   VARCHAR(150) NOT NULL,
    dni                VARCHAR(10)  NOT NULL,
    cuil               VARCHAR(13)  NULL,
    fecha_nacimiento   DATE         NULL,
    lugar_nacimiento   VARCHAR(120) NULL,
    percibe_asignacion ENUM('Sí','No') NULL,
    domicilio          VARCHAR(200) NOT NULL,
    telefono           VARCHAR(30)  NOT NULL,
    estudios_cursados  VARCHAR(60)  NULL,
    email              VARCHAR(150) NULL,
    UNIQUE KEY uq_alumno_tipo (id_alumno, tipo),
    CONSTRAINT fk_tutor_alumno FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS acta_convivencia (
    id_acta         INT PRIMARY KEY AUTO_INCREMENT,
    id_alumno       INT          NOT NULL UNIQUE,
    tutor_nombre    VARCHAR(150) NOT NULL,
    tutor_dni       VARCHAR(10)  NOT NULL,
    correo_contacto VARCHAR(150) NULL,
    autoriza_imagen BOOLEAN      NOT NULL,
    fecha           DATE         NULL,
    lugar           VARCHAR(120) NULL,
    CONSTRAINT fk_acta_alumno FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno) ON DELETE CASCADE
) ENGINE=InnoDB;