<?php
// Módulo Tratamientos: Lógica de Eliminación
require_once '../conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        header("Location: ../tratamientos.php?error=" . urlencode("ID de tratamiento no válido."));
        exit;
    }

    try {
        $sqlDelete = "DELETE FROM tratamientos WHERE id_tratamiento = :id";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->execute([':id' => $id]);
        
        header("Location: ../tratamientos.php?success=" . urlencode("Tratamiento eliminado exitosamente."));
        exit;
    } catch (\PDOException $e) {
        header("Location: ../tratamientos.php?error=" . urlencode("Error al intentar eliminar el tratamiento: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: ../tratamientos.php");
    exit;
}
?>