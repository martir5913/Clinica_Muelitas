<?php
// Módulo Pacientes: Lógica de Creación
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperar y desinfectar datos del POST
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : null;
    $fecha_nacimiento = (isset($_POST['fecha_nacimiento']) && $_POST['fecha_nacimiento'] !== '') ? $_POST['fecha_nacimiento'] : null;
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : null;
    $activo = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

    // Validación mínima de campos obligatorios
    if (empty($nombre) || empty($apellido)) {
        header("Location: ../pacientes.php?error=" . urlencode("El nombre y el apellido son obligatorios."));
        exit;
    }

    try {
        // Preparar sentencia SQL de inserción
        $sql = "INSERT INTO pacientes (nombre, apellido, telefono, correo, fecha_nacimiento, direccion, activo) 
                VALUES (:nombre, :apellido, :telefono, :correo, :fecha_nacimiento, :direccion, :activo)";
        $stmt = $pdo->prepare($sql);
        
        // Ejecutar con los parámetros enlazados
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':telefono' => $telefono,
            ':correo' => $correo,
            ':fecha_nacimiento' => $fecha_nacimiento,
            ':direccion' => $direccion,
            ':activo' => $activo
        ]);

        header("Location: ../pacientes.php?success=" . urlencode("Paciente registrado exitosamente."));
        exit;
    } catch (\PDOException $e) {
        header("Location: ../pacientes.php?error=" . urlencode("Error al registrar el paciente: " . $e->getMessage()));
        exit;
    }
} else {
    // Si no es POST, redirigir al listado principal
    header("Location: ../pacientes.php");
    exit;
}
