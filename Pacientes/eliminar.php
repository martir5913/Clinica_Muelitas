<?php
// Módulo Pacientes: Lógica de Eliminación o Desactivación
require_once '../conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($id <= 0) {
        header("Location: ../pacientes.php?error=" . urlencode("ID de paciente no válido."));
        exit;
    }

    try {
        // 1. Intentar borrar físicamente
        $sqlDelete = "DELETE FROM pacientes WHERE id_paciente = :id";
        $stmtDelete = $pdo->prepare($sqlDelete);
        $stmtDelete->execute([':id' => $id]);
        
        header("Location: ../pacientes.php?success=" . urlencode("Paciente eliminado de la base de datos."));
        exit;
    } catch (\PDOException $e) {
        // Código SQLSTATE 23000 indica error de restricción de integridad (clave foránea con citas)
        if ($e->getCode() == '23000') {
            try {
                // 2. Si no se puede borrar físicamente por las citas vinculadas, lo desactivamos (soft delete)
                $sqlUpdate = "UPDATE pacientes SET activo = 0 WHERE id_paciente = :id";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([':id' => $id]);
                
                header("Location: ../pacientes.php?success=" . urlencode("El paciente tiene citas vinculadas, por lo que se desactivó su estado en lugar de eliminarlo para mantener el historial."));
                exit;
            } catch (\PDOException $innerEx) {
                header("Location: ../pacientes.php?error=" . urlencode("No se pudo eliminar ni desactivar al paciente: " . $innerEx->getMessage()));
                exit;
            }
        } else {
            // Otro error de base de datos
            header("Location: ../pacientes.php?error=" . urlencode("Error al intentar eliminar el paciente: " . $e->getMessage()));
            exit;
        }
    }
} else {
    // Si no viene el parámetro id, redirigir al módulo principal
    header("Location: ../pacientes.php");
    exit;
}
