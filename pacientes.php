<?php
// Módulo: Pacientes
$projectName = 'Clinica Dental — Muelitas';
include 'header.php';
require_once 'conexion.php';

// Inicializar variables para edición
$editMode = false;
$paciente_edit = [
    'id_paciente' => '',
    'nombre' => '',
    'apellido' => '',
    'telefono' => '',
    'correo' => '',
    'fecha_nacimiento' => '',
    'direccion' => '',
    'activo' => 1
];

// Comprobar si se solicita editar un paciente
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $id_edit = intval($_GET['edit']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id_paciente = :id");
        $stmt->execute([':id' => $id_edit]);
        $paciente = $stmt->fetch();
        if ($paciente) {
            $paciente_edit = $paciente;
            $editMode = true;
        }
    } catch (\PDOException $e) {
        $error_msg = "Error al obtener datos del paciente para edición: " . $e->getMessage();
    }
}

// Búsqueda de pacientes
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE nombre LIKE :search OR apellido LIKE :search OR telefono LIKE :search OR correo LIKE :search ORDER BY id_paciente DESC");
        $stmt->execute([':search' => '%' . $search . '%']);
    } else {
        $stmt = $pdo->query("SELECT * FROM pacientes ORDER BY id_paciente DESC");
    }
    $pacientes = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_msg = "Error al listar pacientes: " . $e->getMessage();
    $pacientes = [];
}
?>

<main class="site-main">
    <div class="module-container">
        <header class="module-header">
            <div class="module-badge">Módulo Activo</div>
            <h2 class="module-title">Registro y Control de Pacientes</h2>
            <p class="module-subtitle">Administre los expedientes médicos, información de contacto y estado activo de los pacientes de la clínica.</p>
        </header>

        <!-- Contenedor de Alertas de Éxito o Error -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert-container">
                <div class="alert alert-success">
                    <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><?php echo htmlspecialchars($_GET['success']); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) || isset($error_msg)): ?>
            <div class="alert-container">
                <div class="alert alert-error">
                    <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span><?php echo htmlspecialchars($_GET['error'] ?? $error_msg); ?></span>
                </div>
            </div>
        <?php endif; ?>
      
        <div class="pacientes-layout-vertical">            
            <!-- Panel Superior: Formulario de Creación / Edición -->
            <div class="patient-form-section">
                <div class="seccion-institucional" style="padding: 24px; gap: 16px;">
                    <div class="seccion-header" style="padding-bottom: 12px; margin-bottom: 8px;">
                        <span class="seccion-subtitle"><?php echo $editMode ? 'Editar Expediente' : 'Nuevo Expediente'; ?></span>
                        <h4 style="margin: 0; color: var(--primary-dark); font-family: var(--font-title); font-size: 1.25rem;">
                            <?php echo $editMode ? 'Modificar Paciente' : 'Registrar Paciente'; ?>
                        </h4>
                    </div>

                    <form method="POST" action="<?php echo $editMode ? 'Pacientes/actualizar.php' : 'Pacientes/crear.php'; ?>">
                        <?php if ($editMode): ?>
                            <input type="hidden" name="id_paciente" value="<?php echo htmlspecialchars($paciente_edit['id_paciente']); ?>">
                        <?php endif; ?>

                        <div class="patient-form-grid">
                            <div class="form-group">
                                <label for="nombre">Nombre *</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ej. Juan Carlos" required value="<?php echo htmlspecialchars($paciente_edit['nombre']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="apellido">Apellido *</label>
                                <input type="text" id="apellido" name="apellido" class="form-control" placeholder="Ej. Pérez Gómez" required value="<?php echo htmlspecialchars($paciente_edit['apellido']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" class="form-control" placeholder="Ej. +502 5555-5555" value="<?php echo htmlspecialchars($paciente_edit['telefono'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="correo">Correo Electrónico</label>
                                <input type="email" id="correo" name="correo" class="form-control" placeholder="Ej. juan.perez@correo.com" value="<?php echo htmlspecialchars($paciente_edit['correo'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" value="<?php echo htmlspecialchars($paciente_edit['fecha_nacimiento'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="activo">Estado del Paciente</label>
                                <select id="activo" name="activo" class="form-control">
                                    <option value="1" <?php echo ($paciente_edit['activo'] == 1) ? 'selected' : ''; ?>>Activo (Habilitado)</option>
                                    <option value="0" <?php echo ($paciente_edit['activo'] == 0) ? 'selected' : ''; ?>>Inactivo (Deshabilitado)</option>
                                </select>
                            </div>

                            <div class="form-group form-group-full">
                                <label for="direccion">Dirección</label>
                                <textarea id="direccion" name="direccion" class="form-control" placeholder="Dirección domiciliar..."><?php echo htmlspecialchars($paciente_edit['direccion'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="patient-form-actions">
                            <?php if ($editMode): ?>
                                <a href="pacientes.php" class="btn btn-outline">
                                    Cancelar
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $editMode ? 'Actualizar Paciente' : 'Guardar Paciente'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panel Inferior: Listado de Pacientes -->
            <div class="patients-list-section">
                <div class="table-actions">
                    <form method="GET" action="pacientes.php" class="search-box">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, correo..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                    <?php if (!empty($search)): ?>
                        <a href="pacientes.php" class="btn btn-outline btn-xs">Limpiar búsqueda</a>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Fecha Nac.</th>
                                <th>Dirección</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pacientes) > 0): ?>
                                <?php foreach ($pacientes as $paciente): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?></strong>
                                            <br>
                                            <small style="color: var(--text-muted);">ID: <?php echo $paciente['id_paciente']; ?></small>
                                        </td>
                                        <td>
                                            Tel: <?php echo htmlspecialchars($paciente['telefono'] ?? 'N/A'); ?>
                                            <br>
                                            Email: <?php echo htmlspecialchars($paciente['correo'] ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($paciente['fecha_nacimiento'] ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; display: inline-block; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($paciente['direccion'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($paciente['direccion'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($paciente['activo'] == 1): ?>
                                                <span class="badge badge-active">Activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-inactive">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                <a href="pacientes.php?edit=<?php echo $paciente['id_paciente']; ?>" class="btn btn-secondary btn-xs" title="Editar Paciente">
                                                    Editar
                                                </a>
                                                <a href="Pacientes/eliminar.php?id=<?php echo $paciente['id_paciente']; ?>" class="btn btn-outline btn-xs" style="color: #b91c1c; border-color: rgba(239, 68, 68, 0.2);" onclick="return confirm('¿Está seguro de que desea eliminar o desactivar a este paciente?');" title="Eliminar/Desactivar Paciente">
                                                    Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                        No se encontraron pacientes registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>       
    </div>
</main>

<?php include 'footer.php'; ?>