<?php
session_start(); // Usado para manejar la lógica de los posibles errores y mensajes de validación
require_once '../conexion.php';

// Lista de especialidades válidas (coincide con el ENUM de la BD)
$especialidades = ['General', 'Ortodoncia', 'Endodoncia', 'Cirugia', 'Pediatria', 'Periodoncia', 'Protesis'];

// Si alguien entra directo a este archivo sin enviar un formulario, se envía de vuelta
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dentistas.php');
    exit;
}

//GESTION DE ERRORES
//** FORMULARIO ELIMINAR **//
//Confirmación de DESACTIVAR (viene del campo oculto "confirmar_eliminar")
//cuando se elimina un usuario
if (isset($_POST['confirmar_eliminar'])) {
    $idAEliminar = filter_input(INPUT_POST, 'confirmar_eliminar', FILTER_VALIDATE_INT);

    if ($idAEliminar) {
        try {
            $stmt = $pdo->prepare("UPDATE dentistas SET activo = 0 WHERE id_dentista = :id");
            $stmt->execute(['id' => $idAEliminar]);

            header('Location: ../dentistas.php?mensaje=desactivado');
            exit;
        } catch (PDOException $e) {
            $_SESSION['errores'] = ['No fue posible desactivar al dentista: ' . $e->getMessage()];
            header('Location: ../dentistas.php');
            exit;
        }
    }

    header('Location: ../dentistas.php');
    exit;
}

//GESTION DE ERRORES
//** FORMULARIO CREAR O EDITAR **//
//se distinguen por el campo oculto "id": vacío = crear, con número = editar
$nombreCrudo = '';
if (isset($_POST['nombre'])) {
    $nombreCrudo = $_POST['nombre'];
}
$nombre = trim($nombreCrudo);

$apellidoCrudo = '';
if (isset($_POST['apellido'])) {
    $apellidoCrudo = $_POST['apellido'];
}
$apellido = trim($apellidoCrudo);

$especialidadCrudo = '';
if (isset($_POST['especialidad'])) {
    $especialidadCrudo = $_POST['especialidad'];
}
$especialidad = trim($especialidadCrudo);

$telefonoCrudo = '';
if (isset($_POST['telefono'])) {
    $telefonoCrudo = $_POST['telefono'];
}
$telefono = trim($telefonoCrudo);

$correoCrudo = '';
if (isset($_POST['correo'])) {
    $correoCrudo = $_POST['correo'];
}
$correo = trim($correoCrudo);

$idPost = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$activo = isset($_POST['activo']) ? 1 : 0;

$errores = [];

if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
if ($apellido === '') $errores[] = 'El apellido es obligatorio.';
if (!in_array($especialidad, $especialidades, true)) $errores[] = 'Seleccione una especialidad válida.';
if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo electrónico no tiene un formato válido.';

if (empty($errores)) {
    try {
        if ($idPost) {
            $sql = "UPDATE dentistas
                    SET nombre = :nombre, apellido = :apellido, especialidad = :especialidad,
                        telefono = :telefono, correo = :correo, activo = :activo
                    WHERE id_dentista = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre'       => $nombre,
                'apellido'     => $apellido,
                'especialidad' => $especialidad,
                'telefono'     => $telefono !== '' ? $telefono : null,
                'correo'       => $correo !== '' ? $correo : null,
                'activo'       => $activo,
                'id'           => $idPost,
            ]);

            header('Location: ../dentistas.php?mensaje=editado');
            exit;
        } else {
            $sql = "INSERT INTO dentistas (nombre, apellido, especialidad, telefono, correo, activo)
                    VALUES (:nombre, :apellido, :especialidad, :telefono, :correo, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre'       => $nombre,
                'apellido'     => $apellido,
                'especialidad' => $especialidad,
                'telefono'     => $telefono !== '' ? $telefono : null,
                'correo'       => $correo !== '' ? $correo : null,
            ]);

            header('Location: ../dentistas.php?mensaje=creado');
            exit;
        }
    } catch (PDOException $e) {
        $errores[] = 'Error al guardar el dentista: ' . $e->getMessage();
    }
}

//Si hay errores, se guardan en la sesión y se redirige de vuelta al formulario
$_SESSION['form_data'] = [
    'id'           => $idPost,
    'nombre'       => $nombre,
    'apellido'     => $apellido,
    'especialidad' => $especialidad,
    'telefono'     => $telefono,
    'correo'       => $correo,
    'activo'       => $activo,
];

//En caso que fuera el modo edición se redirige a la misma url
if ($idPost) {
    header('Location: ../dentistas.php?action=editar&id=' . $idPost);
} else {
    header('Location: ../dentistas.php');
}
exit;

?>


