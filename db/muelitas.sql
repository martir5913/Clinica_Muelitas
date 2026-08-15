-- Database: muelitas

CREATE DATABASE IF NOT EXISTS muelitas
	CHARACTER SET utf8mb4
	COLLATE utf8mb4_spanish_ci;

USE muelitas;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS tratamientos;
DROP TABLE IF EXISTS catalogo_tratamientos;
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

CREATE TABLE catalogo_tratamientos (
	id_catalogo_tratamiento INT AUTO_INCREMENT PRIMARY KEY,
	nombre_tratamiento VARCHAR(150) NOT NULL,
	descripcion_estandar TEXT,
	costo_estandar DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE tratamientos (
	id_tratamiento INT AUTO_INCREMENT PRIMARY KEY,
	id_cita INT NOT NULL,
	id_catalogo_trabajo INT NOT NULL, /* Para mantener consistencia con id_catalogo_tratamiento en el mapeo relacional */
	costo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
	observaciones TEXT,
	CONSTRAINT fk_tratamientos_citas
		FOREIGN KEY (id_cita) REFERENCES citas (id_cita)
		ON UPDATE CASCADE
		ON DELETE CASCADE, /* Si se elimina una cita, se eliminan sus tratamientos realizados en cascada */
	CONSTRAINT fk_tratamientos_catalogo
		FOREIGN KEY (id_catalogo_trabajo) REFERENCES catalogo_tratamientos (id_catalogo_tratamiento)
		ON UPDATE CASCADE
		ON DELETE RESTRICT,
	INDEX idx_tratamientos_id_cita (id_cita),
	INDEX idx_tratamientos_id_catalogo (id_catalogo_trabajo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Datos de ejemplo
INSERT INTO pacientes (nombre, apellido, telefono, correo, fecha_nacimiento, direccion, activo) VALUES
	('Ana', 'Pérez', '55511101', 'ana.perez@email.com', '1990-05-12', 'Calle Primera 1, Guatemala', 1),
	('Luis', 'García', '55511102', 'luis.garcia@email.com', '1988-09-21', 'Avenida Central 10, Guatemala', 1),
	('María', 'López', '55511103', 'maria.lopez@email.com', '1995-02-14', 'Zona 4, Guatemala', 1),
	('Carlos', 'Martínez', '55511104', 'carlos.martinez@email.com', '1983-11-09', 'Colonia San José 8, Guatemala', 1),
	('Sofía', 'Ramírez', '55511105', 'sofia.ramirez@email.com', '2001-07-03', 'Boulevard Norte 22, Guatemala', 1);

INSERT INTO dentistas (nombre, apellido, especialidad, telefono, correo, activo) VALUES
	('José', 'Méndez', 'General', '55522201', 'jose.mendez@email.com', 1),
	('Patricia', 'Castro', 'Ortodoncia', '55522202', 'patricia.castro@email.com', 1),
	('Roberto', 'Véliz', 'Endodoncia', '55522203', 'roberto.veliz@email.com', 1),
	('Andrea', 'Núñez', 'Pediatria', '55522204', 'andrea.nunez@email.com', 1),
	('Miguel', 'Torres', 'Cirugia', '55522205', 'miguel.torres@email.com', 1);

INSERT INTO citas (fecha_hora, motivo, estado, id_paciente, id_dentista) VALUES
	('2026-08-11 09:00:00', 'Consulta general', 'Confirmada', 1, 1),
	('2026-08-11 10:30:00', 'Ajuste de ortodoncia', 'Pendiente', 2, 2),
	('2026-08-11 12:00:00', 'Dolor de muela', 'Confirmada', 3, 3),
	('2026-08-12 08:15:00', 'Revisión infantil', 'Pendiente', 4, 4),
	('2026-08-12 15:45:00', 'Extracción', 'Cancelada', 5, 5);

INSERT INTO catalogo_tratamientos (nombre_tratamiento, descripcion_estandar, costo_estandar) VALUES
	('Limpieza dental', 'Limpieza profiláctica profunda y aplicación de flúor.', 250.00),
	('Ajuste de brackets', 'Control mensual de ortodoncia, cambio de ligas y ajuste de arco.', 800.00),
	('Endodoncia', 'Tratamiento de conductos radiculares para salvar la pieza dental.', 1200.00),
	('Revisión pediátrica', 'Control y seguimiento del paciente infantil.', 180.00),
	('Extracción simple', 'Extracción quirúrgica simple de pieza dental molar.', 350.00);

INSERT INTO tratamientos (id_cita, id_catalogo_trabajo, costo, observaciones) VALUES
	(1, 1, 250.00, 'Paciente con excelente higiene dental. Se recomienda control en 6 meses.'),
	(2, 2, 800.00, 'Se cambiaron ligas a color azul y se ajustó el arco superior.'),
	(3, 3, 1200.00, 'Se realizó la obturación del conducto radicular de la pieza 14.'),
	(4, 4, 180.00, 'Paciente cooperador. Se le enseña técnica de cepillado.'),
	(5, 5, 350.00, 'Extracción exitosa del molar inferior derecho. Se recetaron analgésicos.');