<?php
// Módulo Servicios: Registro de nuevos servicios en el catálogo
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre_tratamiento']) ? trim($_POST['nombre_tratamiento']) : '';
    $descripcion = isset($_POST['descripcion_estandar']) ? trim($_POST['descripcion_estandar']) : null;
    $costo = isset($_POST['costo_estandar']) ? floatval($_POST['costo_estandar']) : 0.00;

    if (empty($nombre)) {
        header("Location: ../servicios.php?error=" . urlencode("El nombre del servicio es obligatorio."));
        exit;
    }

    try {
        $sql = "INSERT INTO catalogo_tratamientos (nombre_tratamiento, descripcion_estandar, costo_estandar) 
                VALUES (:nombre, :descripcion, :costo)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':costo' => $costo
        ]);

        header("Location: ../servicios.php?success=" . urlencode("Servicio registrado exitosamente en el catálogo."));
        exit;
    } catch (\PDOException $e) {
        header("Location: ../servicios.php?error=" . urlencode("Error al registrar el servicio: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: ../servicios.php");
    exit;
}
?>
