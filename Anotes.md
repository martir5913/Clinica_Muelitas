### Sitio web "Clinica Dental - Muelitas"

## Tablas

*Paciente
*Dentista
*Citas.
*Tratamientos (Lista de sericios que ofrece la clinica)

### CRUD

## Inicio de proyecto Index.php

## Modulos:

Pacientes, -> Martir -
Dentistas, -> Milton
Tramientos, -> Gabriela
Citas. -> Martir
Ejemplo de linea

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

## Paleta de Colores Preliminar (Estética Dental)

A continuación se detallan los colores principales configurados en la interfaz para garantizar uniformidad visual:

- **Primario (Azul Clínico):** `#0284c7` (Usado en enlaces activos, títulos del Hero, botones primarios y acentos principales).
- **Primario Hover:** `#0369a1` (Estado hover de los elementos interactivos primarios).
- **Primario Light:** `#e0f2fe` (Fondo suave para insignias, botones secundarios y estados hover del menú).
- **Primario Dark:** `#0f172a` (Tono pizarra oscuro usado para encabezados de sección y textos importantes).
- **Acento (Teal/Menta):** `#0d9488` (Usado en insignias de estado activo e indicadores animados).
- **Fondo de Página (Azul Pastel):** `#f0f7ff` (Fondo general ultra suave que inspira higiene y tranquilidad).
- **Fondo de Tarjetas:** `#ffffff` (Blanco puro para los bloques y tarjetas institucionales).
- **Bordes:** `#e2e8f0` (Gris sutil para separar secciones y cajas de formularios).

## Guía de Uso del Archivo de Conexión (conexion.php)

El archivo `conexion.php` utiliza la API **PDO** de PHP. Para interactuar con la base de datos en los diferentes módulos, siga estos patrones recomendados:

### 1. Incluir la conexión en un módulo

Para utilizar la base de datos en `pacientes.php` u otros módulos, incluya el archivo al inicio del script PHP:

```php
<?php
// Incluir el archivo de conexión (la variable $pdo quedará disponible)
require_once 'conexion.php';
?>
```

### 2. Consultar registros (SELECT)

Utilice consultas preparadas para garantizar la seguridad contra Inyección SQL:

```php
<?php
// Preparar la consulta
$stmt = $pdo->prepare("SELECT * FROM pacientes WHERE activo = :activo");

// Ejecutar pasando los parámetros asociados
$stmt->execute(['activo' => 1]);

// Obtener todos los resultados como arreglo asociativo
$pacientes = $stmt->fetchAll();

foreach ($pacientes as $paciente) {
    echo htmlspecialchars($paciente['nombre']) . "<br>";
}
?>
```

### 3. Insertar registros (INSERT)

Use marcadores de posición con nombre en la consulta SQL para mayor claridad:

```php
<?php
try {
    $sql = "INSERT INTO pacientes (nombre, apellido, telefono, correo)
            VALUES (:nombre, :apellido, :telefono, :correo)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'nombre'   => 'Juan',
        'apellido' => 'Pérez',
        'telefono' => '5555-1234',
        'correo'   => 'juan@example.com'
    ]);

    echo "Registro insertado con ID: " . $pdo->lastInsertId();
} catch (PDOException $e) {
    echo "Error al insertar: " . $e->getMessage();
}
?>
```
