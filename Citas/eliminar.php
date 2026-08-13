<?php
// Módulo Citas: Lógica de Eliminación (con Tratamientos)
require_once '../conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        header("Location: ../citas.php?error=" . urlencode("ID de cita no válido."));
        exit;
    }

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // 1. Eliminar tratamientos asociados a la cita
        $sqlTratamientos = "DELETE FROM tratamientos WHERE id_cita = :id_cita";
        $stmtTratamientos = $pdo->prepare($sqlTratamientos);
        $stmtTratamientos->execute([':id_cita' => $id]);

        // 2. Eliminar la cita
        $sqlCita = "DELETE FROM citas WHERE id_cita = :id_cita";
        $stmtCita = $pdo->prepare($sqlCita);
        $stmtCita->execute([':id_cita' => $id]);

        // Confirmar transacción
        $pdo->commit();

        header("Location: ../citas.php?success=" . urlencode("Cita y su tratamiento asociado eliminados de la base de datos."));
        exit;
    } catch (\PDOException $e) {
        // Revertir cambios en caso de error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: ../citas.php?error=" . urlencode("Error al eliminar la cita: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: ../citas.php");
    exit;
}
