### Sitio web "Clinica Dental - Muelitas"

## Tablas

*Paciente
*Dentista
*Citas
*Tratamientos (Lista de sericios que ofrece la clinica)

### CRUD

## Modulos:

Pacientes, -> Martir -
Dentistas, -> Milton
Tramientos, -> Gabriela
Citas. -> Martir

## Tabla Pacientes

id_paciente (INT, PK)
nombre (VARCHAR)
apellido (VARCHAR)
telefono (VARCHAR)
correo (VARCHAR)
fecha_nacimiento (DATE)
direccion (TEXT)
Activo (Bool)

## Tabla Dentista

id_dentista (INT, PK)
nombre (VARCHAR)
apellido (VARCHAR)
especialidad (ENUM)
telefono (VARCHAR)
correo (VARCHAR)
Activo (Bool)

## Tabla Citas

id_cita (INT, PK)
fecha_hora (DATETIME) -- GetDate
motivo (VARCHAR MAX)
estado (VARCHAR - Ej: Pendiente, Confirmada, Cancelada, Atendida)
id_paciente (FK)
id_dentista (FK)

## Tabla Tratamientos

id_tratamiento (INT, PK)
nombre_tratamiento (VARCHAR - Ej: Limpieza dental, Extracción, Brackets)
descripcion (TEXT)
costo (DECIMAL)
id_cita (FK)
