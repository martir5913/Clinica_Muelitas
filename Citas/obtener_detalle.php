<?php
// Módulo Citas: Obtener detalle y tratamientos de una cita en formato JSON
header('Content-Type: application/json; charset=utf-8');
require_once '../conexion.php';

$response = [
    'success' => false,
    'message' => ''
];

if (isset($_GET['id_cita'])) {
    $id_cita = intval($_GET['id_cita']);

    if ($id_cita <= 0) {
        $response['message'] = 'ID de cita no válido.';
        echo json_encode($response);
        exit;
    }

    try {
        // 1. Obtener datos generales de la cita, paciente y dentista
        $sqlCita = "SELECT c.id_cita, c.fecha_hora, c.motivo, c.estado,
                           p.nombre AS pac_nombre, p.apellido AS pac_apellido, p.telefono AS pac_telefono, p.correo AS pac_correo,
                           d.nombre AS den_nombre, d.apellido AS den_apellido, d.especialidad AS den_especialidad
                    FROM citas c
                    INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
                    INNER JOIN dentistas d ON c.id_dentista = d.id_dentista
                    WHERE c.id_cita = :id_cita";
        $stmtCita = $pdo->prepare($sqlCita);
        $stmtCita->execute([':id_cita' => $id_cita]);
        $cita = $stmtCita->fetch(PDO::FETCH_ASSOC);

        if (!$cita) {
            $response['message'] = 'No se encontró la cita solicitada.';
            echo json_encode($response);
            exit;
        }

        // Formatear la fecha
        $date = new DateTime($cita['fecha_hora']);
        $cita['fecha_hora_formateada'] = $date->format('d/m/Y h:i A');

        // 2. Obtener lista de tratamientos asociados a esta cita (unido al catálogo)
        $sqlTratamientos = "SELECT t.id_tratamiento, t.costo, t.observaciones,
                                   ct.nombre_tratamiento, ct.descripcion_estandar
                            FROM tratamientos t
                            INNER JOIN catalogo_tratamientos ct ON t.id_catalogo_trabajo = ct.id_catalogo_tratamiento
                            WHERE t.id_cita = :id_cita
                            ORDER BY t.id_tratamiento ASC";
        $stmtTrats = $pdo->prepare($sqlTratamientos);
        $stmtTrats->execute([':id_cita' => $id_cita]);
        $tratamientos = $stmtTrats->fetchAll(PDO::FETCH_ASSOC);

        // Calcular costo total
        $costo_total = 0.00;
        foreach ($tratamientos as $t) {
            $costo_total += floatval($t['costo']);
        }

        $response['success'] = true;
        $response['cita'] = $cita;
        $response['tratamientos'] = $tratamientos;
        $response['costo_total'] = $costo_total;
        $response['total_tratamientos'] = count($tratamientos);

    } catch (\PDOException $e) {
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Parámetro id_cita faltante.';
}

echo json_encode($response);
exit;
?>
