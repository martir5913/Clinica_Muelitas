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

//Si se está en modo edición, se cargan los datos del dentista a editar
if ($modoEdicion) {
    $stmt = $pdo->prepare("SELECT * FROM dentistas WHERE id_dentista = :id");
    $stmt->execute(['id' => $idEditar]);
    $dentistaEditar = $stmt->fetch();

    if ($dentistaEditar) {
        $nombre       = $dentistaEditar['nombre'];
        $apellido     = $dentistaEditar['apellido'];
        $especialidad = $dentistaEditar['especialidad'];
        $telefono     = $dentistaEditar['telefono'];
        $correo       = $dentistaEditar['correo'];
        $activo       = $dentistaEditar['activo'];
    } else {
        $modoEdicion = false;
    }
}

//Gestión de errores mediante isset para evitar null si no se envian datos en el formulario
//también se guardan mediante SESSION para que persistan al recargar la página
$errores = [];
if (isset($_SESSION['errores'])) {
    $errores = $_SESSION['errores'];
    unset($_SESSION['errores']);
}

if (isset($_SESSION['form_data'])) {
    $datosGuardados = $_SESSION['form_data'];
    unset($_SESSION['form_data']);

    $nombre       = $datosGuardados['nombre'];
    $apellido     = $datosGuardados['apellido'];
    $especialidad = $datosGuardados['especialidad'];
    $telefono     = $datosGuardados['telefono'];
    $correo       = $datosGuardados['correo'];
    $activo       = $datosGuardados['activo'];

    // Si el error vino de una edición, forzamos el modo edición otra vez
    if ($datosGuardados['id']) {
        $modoEdicion = true;
        $idEditar    = $datosGuardados['id'];
    }
}

// Leer el mensaje de confirmación tras la redirección
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

        <!-- Mensajes de confirmación -->
        <?php if ($mensaje === 'creado'): ?>
            <div class="alert alert-success">Dentista registrado correctamente.</div>
        <?php elseif ($mensaje === 'editado'): ?>
            <div class="alert alert-success">Datos del dentista actualizados correctamente.</div>
        <?php elseif ($mensaje === 'desactivado'): ?>
            <div class="alert alert-success">Dentista desactivado correctamente.</div>
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

        <!-- Tarjeta de confirmación para desactivar -->
        <?php if ($modoEliminar): ?>
        <div class="confirm-card">
            <p>¿Está seguro que desea desactivar a:</p>
            <p class="confirm-nombre">
                <?php echo htmlspecialchars($dentistaEliminar['nombre'] . ' ' . $dentistaEliminar['apellido'], ENT_QUOTES, 'UTF-8'); ?>
                — <?php echo htmlspecialchars($dentistaEliminar['especialidad'], ENT_QUOTES, 'UTF-8'); ?>
            </p>
            <form method="POST" action="dentistas/procesar.php" class="form-actions">
                <input type="hidden" name="confirmar_eliminar" value="<?php echo (int) $dentistaEliminar['id_dentista']; ?>">
                <a href="dentistas.php" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-danger">Sí, Desactivar</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Formulario para crear/editar dentistas -->
        <div class="form-section">
            <div class="form-section-badge"><?php echo $modoEdicion ? 'Edición de Registro' : 'Nuevo Registro'; ?></div>
            <h3 class="form-section-title"><?php echo $modoEdicion ? 'Editar Dentista' : 'Registrar Dentista'; ?></h3>
            <form method="POST" action="dentistas/procesar.php" class="form-grid">
                <input type="hidden" name="id" value="<?php echo $modoEdicion ? (int) $idEditar : ''; ?>">

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

                <?php if ($modoEdicion): ?>
                <div class="form-group form-group-checkbox">
                    <label class="checkbox-label">
                        <input type="checkbox" name="activo" value="1" <?php echo $activo ? 'checked' : ''; ?>>
                        Dentista activo
                    </label>
                </div>
                <?php endif; ?>

                <div class="form-actions">
                    <?php if ($modoEdicion): ?>
                        <a href="dentistas.php" class="btn btn-outline">Cancelar</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $modoEdicion ? 'Guardar Cambios' : 'Guardar Dentista'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista de dentistas -->
        <?php if (count($dentistas) === 0): ?>
            <div class="placeholder-card">
                <p>Aún no hay dentistas registrados.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Especialidad</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
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
                            <td class="table-actions">
                                <a href="dentistas.php?action=editar&id=<?php echo (int) $dentista['id_dentista']; ?>" class="btn btn-outline btn-sm">Editar</a>
                                <?php if ($dentista['activo']): ?>
                                    <a href="dentistas.php?action=eliminar&id=<?php echo (int) $dentista['id_dentista']; ?>" class="btn btn-outline btn-sm btn-danger">Desactivar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
