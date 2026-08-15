<?php
// Módulo Citas: Lógica de Actualización (Sin Tratamiento Inline)
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos de la Cita
    $id_cita = isset($_POST['id_cita']) ? intval($_POST['id_cita']) : 0;
    $id_paciente = isset($_POST['id_paciente']) ? intval($_POST['id_paciente']) : 0;
    $id_dentista = isset($_POST['id_dentista']) ? intval($_POST['id_dentista']) : 0;
    $fecha_hora_input = isset($_POST['fecha_hora']) ? trim($_POST['fecha_hora']) : '';
    $fecha_hora = !empty($fecha_hora_input) ? str_replace('T', ' ', $fecha_hora_input) : null;
    $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'Pendiente';

    // Validación de campos obligatorios
    if ($id_cita <= 0 || $id_paciente <= 0 || $id_dentista <= 0 || empty($fecha_hora) || empty($motivo)) {
        header("Location: ../citas.php?error=" . urlencode("Todos los campos obligatorios (*) deben estar completos."));
        exit;
    }

    try {
        // Actualizar Cita
        $sqlCita = "UPDATE citas 
                    SET fecha_hora = :fecha_hora, 
                        motivo = :motivo, 
                        estado = :estado, 
                        id_paciente = :id_paciente, 
                        id_dentista = :id_dentista 
                    WHERE id_cita = :id_cita";
        $stmtCita = $pdo->prepare($sqlCita);
        $stmtCita->execute([
            ':fecha_hora' => $fecha_hora,
            ':motivo' => $motivo,
            ':estado' => $estado,
            ':id_paciente' => $id_paciente,
            ':id_dentista' => $id_dentista,
            ':id_cita' => $id_cita
        ]);

        header("Location: ../citas.php?success=" . urlencode("Cita actualizada exitosamente."));
        exit;
    } catch (\PDOException $e) {
        header("Location: ../citas.php?error=" . urlencode("Error al actualizar en la base de datos: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: ../citas.php");
    exit;
}
?>
