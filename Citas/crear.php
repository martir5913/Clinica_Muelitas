<?php
// Módulo Citas: Lógica de Creación (con Tratamiento)
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Datos de la Cita
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

    // Validación mínima de campos obligatorios
    if ($id_paciente <= 0 || $id_dentista <= 0 || empty($fecha_hora) || empty($motivo) || empty($nombre_tratamiento)) {
        header("Location: ../citas.php?error=" . urlencode("Todos los campos marcados con asterisco (*) son obligatorios (incluyendo el tratamiento)."));
        exit;
    }

    try {
        // Iniciar transacción para asegurar que ambos inserts ocurran o ninguno
        $pdo->beginTransaction();

        // 1. Insertar Cita
        $sqlCita = "INSERT INTO citas (fecha_hora, motivo, estado, id_paciente, id_dentista) 
                    VALUES (:fecha_hora, :motivo, :estado, :id_paciente, :id_dentista)";
        $stmtCita = $pdo->prepare($sqlCita);
        $stmtCita->execute([
            ':fecha_hora' => $fecha_hora,
            ':motivo' => $motivo,
            ':estado' => $estado,
            ':id_paciente' => $id_paciente,
            ':id_dentista' => $id_dentista
        ]);

        $id_cita_generado = $pdo->lastInsertId();

        // 2. Insertar Tratamiento
        $sqlTratamiento = "INSERT INTO tratamientos (nombre_tratamiento, descripcion, costo, id_cita) 
                           VALUES (:nombre, :descripcion, :costo, :id_cita)";
        $stmtTratamiento = $pdo->prepare($sqlTratamiento);
        $stmtTratamiento->execute([
            ':nombre' => $nombre_tratamiento,
            ':descripcion' => $descripcion_tratamiento,
            ':costo' => $costo,
            ':id_cita' => $id_cita_generado
        ]);

        // Confirmar transacción
        $pdo->commit();

        header("Location: ../citas.php?success=" . urlencode("Cita y tratamiento programados exitosamente."));
        exit;
    } catch (\PDOException $e) {
        // Revertir cambios en caso de error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: ../citas.php?error=" . urlencode("Error al guardar en la base de datos: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: ../citas.php");
    exit;
}
