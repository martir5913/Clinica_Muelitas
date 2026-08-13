<?php
// Módulo: Dentistas
$projectName = 'Clinica Dental — Muelitas';
$extraStyles = 'style-dentistas-module.css'; //Carga de estilos adicionales
require_once 'conexion.php';

//Especialidades para registrar al momento de un nuevo dentista
$especialidades = ['General', 'Ortodoncia', 'Endodoncia', 'Cirugia', 'Pediatria', 'Periodoncia', 'Protesis'];

//Variables para el formulario
$errores      = [];
$nombre       = '';
$apellido     = '';
$especialidad = '';
$telefono     = '';
$correo       = '';

//Procesamiento de formuario cuando lo envia el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = trim($_POST['nombre'] ?? '');
    $apellido     = trim($_POST['apellido'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $telefono     = trim($_POST['telefono'] ?? '');
    $correo       = trim($_POST['correo'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if ($apellido === '') {
        $errores[] = 'El apellido es obligatorio.';
    }
    if (!in_array($especialidad, $especialidades, true)) {
        $errores[] = 'Seleccione una especialidad válida.';
    }
    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no tiene un formato válido.';
    }

    if (empty($errores)) {
        try {
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

            // Redirigir para evitar reenvío del formulario al recargar
            header('Location: dentistas.php?mensaje=creado');
            exit;
        } catch (PDOException $e) {
            $errores[] = 'Error al guardar el dentista: ' . $e->getMessage();
        }
    }
}
//Mensaje de validaciones
$mensaje = $_GET['mensaje'] ?? '';

include 'header.php';
//Listado simple de dentistas
$stmt = $pdo->query("SELECT * FROM dentistas ORDER BY apellido, nombre");
$dentistas = $stmt->fetchAll();
?>

<main class="site-main">
    <div class="module-container">
        <header class="module-header">
            <div class="module-badge">Módulo Activo</div>
            <h2 class="module-title">Personal Odontológico y Especialistas</h2>
            <p class="module-subtitle">Administre el registro de dentistas, especialidades (General, Ortodoncia, Endodoncia, Cirugía, etc.), contacto y disponibilidad.</p>
        </header>
        
        <!-- Confirmación tras crear un nuevo dentista-->
        <?php if ($mensaje === 'creado'): ?>
            <div class="alert alert-success">Dentista registrado correctamente.</div>
        <?php endif; ?>
        
        <!-- Bloque de validación -->
        <?php if (!empty($errores)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Formulario para crear dentistas -->


        <!-- Lista de dentistas -->
        <?php if (count($dentistas) === 0): ?>
            <div class="placeholder-card">
                <p>Aún no hay dentistas registrados.</p>
            </div>
        <?php else:  ?>
            <div class="tabble-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Especialidad</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dentistas as $dentista): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dentista['nombre'] . ' ' . $dentista['apellido'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($dentista['especialidad'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($dentista['telefono'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($dentista['correo'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo $dentista['activo'] ? 'Activo' : 'Inactivo'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
