<?php
// Módulo Servicios: Eliminación controlada de servicios del catálogo
require_once '../conexion.php';

if (isset($_GET['id']) && intval($_GET['id']) > 0) {
    $id = intval($_GET['id']);

    try {
        // Intentar eliminar el servicio del catálogo
        $stmt = $pdo->prepare("DELETE FROM catalogo_tratamientos WHERE id_catalogo_tratamiento = :id");
        $stmt->execute([':id' => $id]);

        header("Location: ../servicios.php?success=" . urlencode("Servicio eliminado del catálogo exitosamente."));
        exit;
    } catch (\PDOException $e) {
        // El código SQLSTATE 23000 corresponde a violaciones de integridad relacional (llave foránea restringida)
        if ($e->getCode() == '23000' || strpos($e->getMessage(), '1451') !== false) {
            header("Location: ../servicios.php?error=" . urlencode("No se puede eliminar este servicio porque ya ha sido aplicado a consultas o tratamientos activos de pacientes. Se recomienda mantenerlo para no alterar los registros históricos."));
            exit;
        } else {
            header("Location: ../servicios.php?error=" . urlencode("Error al intentar eliminar el servicio: " . $e->getMessage()));
            exit;
        }
    }
} else {
    header("Location: ../servicios.php?error=" . urlencode("ID de servicio no válido o ausente."));
    exit;
}
?>
