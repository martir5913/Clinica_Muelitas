<?php
// Módulo: Dentistas
//Inicialización de sesion
session_start();

$projectName = 'Clinica Dental — Muelitas';
$extraStyles = 'style-dentistas-module.css'; //Carga de estilos adicionales
require_once 'conexion.php';

//Lista de especialidades válidas (coincide con el ENUM de la BD)
$especialidades = ['General', 'Ortodoncia', 'Endodoncia', 'Cirugia', 'Pediatria', 'Periodoncia', 'Protesis'];

//Detectar si se entra en modo editar (viene por GET: dentistas.php?action=editar&id=X)
$modoEdicion = false;
$idEditar    = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$accion = '';
if (isset($_GET['action'])) {
    $accion = $_GET['action'];
}

if ($accion === 'editar' && $idEditar) {
    $modoEdicion = true;
}

//Detectar si se entra en modo eliminar (viene por GET: dentistas.php?action=eliminar&id=X)
$modoEliminar     = false;
$dentistaEliminar = null;
if ($accion === 'eliminar' && $idEditar) {
    $stmt = $pdo->prepare("SELECT * FROM dentistas WHERE id_dentista = :id");
    $stmt->execute(['id' => $idEditar]);
    $dentistaEliminar = $stmt->fetch();

    if ($dentistaEliminar) {
        $modoEliminar = true;
    }
}

//Variables para el vacías por defecto.
$nombre       = '';
$apellido     = '';
$especialidad = '';
$telefono     = '';
$correo       = '';
$activo       = 1;


//Procesamiento de formuario cuando lo envia el método POST, 
//en caso de que la solicitud required de html sea burlada
//Se valida con el operador isset() para evirar post null. 
// y trim() para eliminar espacios en blanco al inicio y final de la cadena.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
$mensaje = '';
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
}


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
        <div class="form-section">
            <div class="form-section-badge">Nuevo Registro</div>
            <h3 class="form-section-title">Registrar Dentista</h3>

            <form method="POST" action="dentistas.php" class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-control"
                           value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Ej. José" required>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" class="form-control"
                           value="<?php echo htmlspecialchars($apellido, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Ej. Méndez" required>
                </div>

                <div class="form-group">
                    <label for="especialidad">Especialidad</label>
                    <select id="especialidad" name="especialidad" class="form-control" required>
                        <option value="">Seleccione una especialidad</option>
                        <?php foreach ($especialidades as $op): ?>
                            <option value="<?php echo $op; ?>" <?php echo ($especialidad === $op) ? 'selected' : ''; ?>><?php echo $op; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="form-control"
                           value="<?php echo htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Ej. +502 5555-5555">
                </div>

                <div class="form-group">
                    <label for="correo">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-control"
                           value="<?php echo htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="correo@ejemplo.com">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Dentista</button>
                </div>
            </form>
        </div>

        <!-- Lista de dentistas -->
        <?php if (count($dentistas) === 0): ?>
            <div class="placeholder-card">
                <p>Aún no hay dentistas registrados.</p>
            </div>
        <?php else:  ?>
            <div class="table-wrapper">
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
