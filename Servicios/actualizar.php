<?php
// Módulo Servicios: Actualización de servicios existentes en el catálogo
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_catalogo_tratamiento = isset($_POST['id_catalogo_tratamiento']) ? intval($_POST['id_catalogo_tratamiento']) : 0;
    $nombre = isset($_POST['nombre_tratamiento']) ? trim($_POST['nombre_tratamiento']) : '';
    $descripcion = isset($_POST['descripcion_estandar']) ? trim($_POST['descripcion_estandar']) : null;
    $costo = isset($_POST['costo_estandar']) ? floatval($_POST['costo_estandar']) : 0.00;

    if ($id_catalogo_tratamiento <= 0 || empty($nombre)) {
        header("Location: ../servicios.php?error=" . urlencode("El ID de servicio y el nombre son obligatorios."));
        exit;
    }

    try {
        $sql = "UPDATE catalogo_tratamientos 
                SET nombre_tratamiento = :nombre, descripcion_estandar = :descripcion, costo_estandar = :costo 
                WHERE id_catalogo_tratamiento = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':costo' => $costo,
            ':id' => $id_catalogo_tratamiento
        ]);

        header("Location: ../servicios.php?success=" . urlencode("Servicio actualizado exitosamente."));
        exit;
    } catch (\PDOException $e) {
        header("Location: ../servicios.php?error=" . urlencode("Error al actualizar el servicio: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: ../servicios.php");
    exit;
}
?>
