<?php
// Módulo Pacientes: Lógica de Actualización
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperar y desinfectar datos del POST
    $id_paciente = isset($_POST['id_paciente']) ? intval($_POST['id_paciente']) : 0;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : null;
    $fecha_nacimiento = (isset($_POST['fecha_nacimiento']) && $_POST['fecha_nacimiento'] !== '') ? $_POST['fecha_nacimiento'] : null;
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : null;
    $activo = isset($_POST['activo']) ? intval($_POST['activo']) : 0;

    // Validación de campos obligatorios
    if ($id_paciente <= 0 || empty($nombre) || empty($apellido)) {
        header("Location: ../pacientes.php?error=" . urlencode("Datos insuficientes para actualizar el paciente. Nombre y apellido son obligatorios."));
        exit;
    }

    try {
        // Preparar sentencia SQL de actualización
        $sql = "UPDATE pacientes 
                SET nombre = :nombre, 
                    apellido = :apellido, 
                    telefono = :telefono, 
                    correo = :correo, 
                    fecha_nacimiento = :fecha_nacimiento, 
                    direccion = :direccion, 
                    activo = :activo 
                WHERE id_paciente = :id_paciente";
        $stmt = $pdo->prepare($sql);
        
        // Ejecutar con los parámetros enlazados
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':telefono' => $telefono,
            ':correo' => $correo,
            ':fecha_nacimiento' => $fecha_nacimiento,
            ':direccion' => $direccion,
            ':activo' => $activo,
            ':id_paciente' => $id_paciente
        ]);

        header("Location: ../pacientes.php?success=" . urlencode("Paciente actualizado exitosamente."));
        exit;
    } catch (\PDOException $e) {
        header("Location: ../pacientes.php?error=" . urlencode("Error al actualizar el paciente: " . $e->getMessage()));
        exit;
    }
} else {
    // Si no es POST, redirigir al listado principal
    header("Location: ../pacientes.php");
    exit;
}
