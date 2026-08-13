<?php
// Módulo Citas: Lógica de Actualización (con Tratamiento)
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Datos de la Cita
    $id_cita = isset($_POST['id_cita']) ? intval($_POST['id_cita']) : 0;
    $id_paciente = isset($_POST['id_paciente']) ? intval($_POST['id_paciente']) : 0;
    $id_dentista = isset($_POST['id_dentista']) ? intval($_POST['id_dentista']) : 0;
    $fecha_hora_input = isset($_POST['fecha_hora']) ? trim($_POST['fecha_hora']) : '';
    $fecha_hora = !empty($fecha_hora_input) ? str_replace('T', ' ', $fecha_hora_input) : null;
    $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
    $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'Pendiente';

    // 2. Datos del Tratamiento
    $nombre_tratamiento = isset($_POST['nombre_tratamiento']) ? trim($_POST['nombre_tratamiento']) : '';
    $costo = isset($_POST['costo']) ? floatval($_POST['costo']) : 0.00;
    $descripcion_tratamiento = isset($_POST['descripcion_tratamiento']) ? trim($_POST['descripcion_tratamiento']) : null;

    // Validación de campos obligatorios
    if ($id_cita <= 0 || $id_paciente <= 0 || $id_dentista <= 0 || empty($fecha_hora) || empty($motivo) || empty($nombre_tratamiento)) {
        header("Location: ../citas.php?error=" . urlencode("Todos los campos obligatorios (*) deben estar completos."));
        exit;
    }

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // 1. Actualizar Cita
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

        // 2. Comprobar si ya existe un tratamiento para esta cita
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM tratamientos WHERE id_cita = :id_cita");
        $stmtCheck->execute([':id_cita' => $id_cita]);
        $hasTratamiento = $stmtCheck->fetchColumn() > 0;

        if ($hasTratamiento) {
            // Actualizar Tratamiento
            $sqlTratamiento = "UPDATE tratamientos 
                               SET nombre_tratamiento = :nombre, 
                                   descripcion = :descripcion, 
                                   costo = :costo 
                               WHERE id_cita = :id_cita";
            $stmtTratamiento = $pdo->prepare($sqlTratamiento);
            $stmtTratamiento->execute([
                ':nombre' => $nombre_tratamiento,
                ':descripcion' => $descripcion_tratamiento,
                ':costo' => $costo,
                ':id_cita' => $id_cita
            ]);
        } else {
            // Insertar Tratamiento si no existía (para registros viejos de la BD)
            $sqlTratamiento = "INSERT INTO tratamientos (nombre_tratamiento, descripcion, costo, id_cita) 
                               VALUES (:nombre, :descripcion, :costo, :id_cita)";
            $stmtTratamiento = $pdo->prepare($sqlTratamiento);
            $stmtTratamiento->execute([
                ':nombre' => $nombre_tratamiento,
                ':descripcion' => $descripcion_tratamiento,
                ':costo' => $costo,
                ':id_cita' => $id_cita
            ]);
        }

        // Confirmar transacción
        $pdo->commit();

        header("Location: ../citas.php?success=" . urlencode("Cita y tratamiento actualizados exitosamente."));
        exit;
    } catch (\PDOException $e) {
        // Revertir cambios en caso de error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: ../citas.php?error=" . urlencode("Error al actualizar en la base de datos: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: ../citas.php");
    exit;
}
