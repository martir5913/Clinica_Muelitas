-- Database: muelitas

CREATE DATABASE IF NOT EXISTS muelitas
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_spanish_ci;

USE muelitas;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS tratamientos;
DROP TABLE IF EXISTS citas;
DROP TABLE IF EXISTS dentistas;
DROP TABLE IF EXISTS pacientes;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE pacientes (
	id_paciente INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(100) NOT NULL,
	apellido VARCHAR(100) NOT NULL,
	telefono VARCHAR(20),
	correo VARCHAR(150),
	fecha_nacimiento DATE,
	direccion TEXT,
	activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE dentistas (
	id_dentista INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(100) NOT NULL,
	apellido VARCHAR(100) NOT NULL,
	especialidad ENUM('General', 'Ortodoncia', 'Endodoncia', 'Cirugia', 'Pediatria', 'Periodoncia', 'Protesis') NOT NULL,
	telefono VARCHAR(20),
	correo VARCHAR(150),
	activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE citas (
	id_cita INT AUTO_INCREMENT PRIMARY KEY,
	fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	motivo VARCHAR(255) NOT NULL,
	estado ENUM('Pendiente', 'Confirmada', 'Cancelada', 'Atendida') NOT NULL DEFAULT 'Pendiente',
	id_paciente INT NOT NULL,
	id_dentista INT NOT NULL,
	CONSTRAINT fk_citas_pacientes
		FOREIGN KEY (id_paciente) REFERENCES pacientes (id_paciente)
		ON UPDATE CASCADE
		ON DELETE RESTRICT,
	CONSTRAINT fk_citas_dentistas
		FOREIGN KEY (id_dentista) REFERENCES dentistas (id_dentista)
		ON UPDATE CASCADE
		ON DELETE RESTRICT,
	INDEX idx_citas_id_paciente (id_paciente),
	INDEX idx_citas_id_dentista (id_dentista)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE tratamientos (
	id_tratamiento INT AUTO_INCREMENT PRIMARY KEY,
	nombre_tratamiento VARCHAR(150) NOT NULL,
	descripcion TEXT,
	costo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
	id_cita INT NOT NULL,
	CONSTRAINT fk_tratamientos_citas
		FOREIGN KEY (id_cita) REFERENCES citas (id_cita)
		ON UPDATE CASCADE
		ON DELETE RESTRICT,
	INDEX idx_tratamientos_id_cita (id_cita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;