<?php
// Modulo: Citas
$projectName = 'Clinica Dental — Muelitas';
include 'header.php';
require_once 'conexion.php';

// Asegurar que todas las citas existentes tengan un registro de tratamiento asociado (para el INNER JOIN)
try {
    $pdo->exec("INSERT INTO tratamientos (nombre_tratamiento, descripcion, costo, id_cita)
                SELECT c.motivo, 'Tratamiento inicial', 0.00, c.id_cita
                FROM citas c
                LEFT JOIN tratamientos t ON c.id_cita = t.id_cita
                WHERE t.id_tratamiento IS NULL");
} catch (\PDOException $e) {
    // Silencioso
}

// Inicializar variables para edición
$editMode = false;
$cita_edit = [
    'id_cita' => '',
    'fecha_hora' => '',
    'motivo' => '',
    'estado' => 'Pendiente',
    'id_paciente' => '',
    'id_dentista' => '',
    'nombre_tratamiento' => '',
    'costo' => '0.00',
    'descripcion_tratamiento' => ''
];

// Comprobar si se solicita editar una cita
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $id_edit = intval($_GET['edit']);
    try {
        // Obtener cita
        $stmt = $pdo->prepare("SELECT * FROM citas WHERE id_cita = :id");
        $stmt->execute([':id' => $id_edit]);
        $cita = $stmt->fetch();
        if ($cita) {
            $cita_edit = $cita;
            // Para precargar en datetime-local, el formato debe tener una 'T' separando la fecha y la hora (Y-m-d\TH:i)
            if (!empty($cita_edit['fecha_hora'])) {
                $cita_edit['fecha_hora'] = str_replace(' ', 'T', substr($cita_edit['fecha_hora'], 0, 16));
            }
            
            // Obtener tratamiento asociado
            $stmtTrat = $pdo->prepare("SELECT * FROM tratamientos WHERE id_cita = :id_cita");
            $stmtTrat->execute([':id_cita' => $id_edit]);
            $tratamiento = $stmtTrat->fetch();
            if ($tratamiento) {
                $cita_edit['nombre_tratamiento'] = $tratamiento['nombre_tratamiento'];
                $cita_edit['costo'] = $tratamiento['costo'];
                $cita_edit['descripcion_tratamiento'] = $tratamiento['descripcion'];
            }
            
            $editMode = true;
        }
    } catch (\PDOException $e) {
        $error_msg = "Error al obtener datos de la cita para edición: " . $e->getMessage();
    }
}

// Obtener lista de pacientes para el selector desplegable
try {
    $stmtPacientes = $pdo->query("SELECT id_paciente, nombre, apellido, activo FROM pacientes ORDER BY apellido, nombre");
    $listPacientes = $stmtPacientes->fetchAll();
} catch (\PDOException $e) {
    $listPacientes = [];
    $error_msg = "Error al cargar la lista de pacientes: " . $e->getMessage();
}

// Obtener lista de dentistas para el selector desplegable
try {
    $stmtDentistas = $pdo->query("SELECT id_dentista, nombre, apellido, activo FROM dentistas ORDER BY apellido, nombre");
    $listDentistas = $stmtDentistas->fetchAll();
} catch (\PDOException $e) {
    $listDentistas = [];
    $error_msg = "Error al cargar la lista de dentistas: " . $e->getMessage();
}

// Obtener lista de tratamientos únicos ya registrados (catálogo implícito)
try {
    $stmtTrats = $pdo->query("SELECT nombre_tratamiento, costo, descripcion FROM tratamientos GROUP BY nombre_tratamiento ORDER BY nombre_tratamiento");
    $listTratamientos = $stmtTrats->fetchAll();
} catch (\PDOException $e) {
    $listTratamientos = [];
}

// Si no hay tratamientos registrados (Base de datos limpia), proveemos un catálogo inicial de muestra
if (count($listTratamientos) === 0) {
    $listTratamientos = [
        ['nombre_tratamiento' => 'Limpieza dental', 'costo' => '250.00', 'descripcion' => 'Limpieza profiláctica profunda y aplicación de flúor.'],
        ['nombre_tratamiento' => 'Extracción simple', 'costo' => '350.00', 'descripcion' => 'Extracción quirúrgica simple de pieza dental.'],
        ['nombre_tratamiento' => 'Resina dental', 'costo' => '300.00', 'descripcion' => 'Restauración dental estética con resina de alta calidad.'],
        ['nombre_tratamiento' => 'Endodoncia', 'costo' => '1200.00', 'descripcion' => 'Tratamiento de conductos radiculares para salvar la pieza dental.'],
        ['nombre_tratamiento' => 'Ajuste de brackets', 'costo' => '500.00', 'descripcion' => 'Control mensual de ortodoncia, cambio de ligas y ajuste de arco.']
    ];
}

// Búsqueda de citas con reporte INNER JOIN
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
try {
    $sql = "SELECT c.*, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, 
                   d.nombre AS dentista_nombre, d.apellido AS dentista_apellido,
                   t.nombre_tratamiento, t.costo AS tratamiento_costo, t.descripcion AS tratamiento_descripcion
            FROM citas c 
            INNER JOIN pacientes p ON c.id_paciente = p.id_paciente 
            INNER JOIN dentistas d ON c.id_dentista = d.id_dentista
            INNER JOIN tratamientos t ON c.id_cita = t.id_cita";
    
    if (!empty($search)) {
        $sql .= " WHERE p.nombre LIKE :search OR p.apellido LIKE :search 
                     OR d.nombre LIKE :search OR d.apellido LIKE :search 
                     OR c.motivo LIKE :search OR t.nombre_tratamiento LIKE :search
                  ORDER BY c.fecha_hora DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => '%' . $search . '%']);
    } else {
        $sql .= " ORDER BY c.fecha_hora DESC";
        $stmt = $pdo->query($sql);
    }
    $citas = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_msg = "Error al listar las citas: " . $e->getMessage();
    $citas = [];
}
?>

<main class="site-main">
    <div class="module-container">
        <header class="module-header">
            <div class="module-badge">Módulo Activo</div>
            <h2 class="module-title">Agenda y Control de Citas Médicas</h2>
            <p class="module-subtitle">Programe citas, asigne especialistas y registre los tratamientos dentales junto a sus costos (Reporte Combinado INNER JOIN).</p>
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
            
            <!-- Panel Superior: Formulario de Programación / Edición -->
            <div class="patient-form-section">
                <div class="seccion-institucional" style="padding: 24px; gap: 16px;">
                    <div class="seccion-header" style="padding-bottom: 12px; margin-bottom: 8px;">
                        <span class="seccion-subtitle"><?php echo $editMode ? 'Editar Cita' : 'Nueva Cita'; ?></span>
                        <h4 style="margin: 0; color: var(--primary-dark); font-family: var(--font-title); font-size: 1.25rem;">
                            <?php echo $editMode ? 'Modificar Cita Programada' : 'Programar Nueva Cita con Tratamiento'; ?>
                        </h4>
                    </div>

                    <form method="POST" action="<?php echo $editMode ? 'Citas/actualizar.php' : 'Citas/crear.php'; ?>">
                        <?php if ($editMode): ?>
                            <input type="hidden" name="id_cita" value="<?php echo htmlspecialchars($cita_edit['id_cita']); ?>">
                        <?php endif; ?>

                        <div class="patient-form-grid">
                            <!-- Datos de la Cita -->
                            <div class="form-group">
                                <label for="id_paciente">Paciente *</label>
                                <select id="id_paciente" name="id_paciente" class="form-control" required>
                                    <option value="">-- Seleccionar Paciente --</option>
                                    <?php foreach ($listPacientes as $pac): ?>
                                        <?php if ($pac['activo'] == 1 || $pac['id_paciente'] == $cita_edit['id_paciente']): ?>
                                            <option value="<?php echo $pac['id_paciente']; ?>" <?php echo ($pac['id_paciente'] == $cita_edit['id_paciente']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($pac['apellido'] . ', ' . $pac['nombre']) . ($pac['activo'] == 0 ? ' (Inactivo)' : ''); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="id_dentista">Dentista Asignado *</label>
                                <select id="id_dentista" name="id_dentista" class="form-control" required>
                                    <option value="">-- Seleccionar Dentista --</option>
                                    <?php foreach ($listDentistas as $dent): ?>
                                        <?php if ($dent['activo'] == 1 || $dent['id_dentista'] == $cita_edit['id_dentista']): ?>
                                            <option value="<?php echo $dent['id_dentista']; ?>" <?php echo ($dent['id_dentista'] == $cita_edit['id_dentista']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dent['apellido'] . ', ' . $dent['nombre']) . ($dent['activo'] == 0 ? ' (Inactivo)' : ''); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="fecha_hora">Fecha y Hora *</label>
                                <input type="datetime-local" id="fecha_hora" name="fecha_hora" class="form-control" required value="<?php echo htmlspecialchars($cita_edit['fecha_hora']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="estado">Estado de la Cita</label>
                                <select id="estado" name="estado" class="form-control">
                                    <option value="Pendiente" <?php echo ($cita_edit['estado'] === 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="Confirmada" <?php echo ($cita_edit['estado'] === 'Confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                                    <option value="Cancelada" <?php echo ($cita_edit['estado'] === 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                    <option value="Atendida" <?php echo ($cita_edit['estado'] === 'Atendida') ? 'selected' : ''; ?>>Atendida</option>
                                </select>
                            </div>

                            <!-- Datos del Tratamiento -->
                            <div class="form-group">
                                <label for="nombre_tratamiento_select">Seleccionar Tratamiento *</label>
                                <select id="nombre_tratamiento_select" class="form-control" onchange="handleTreatmentSelect(this.value)" required>
                                    <option value="">-- Seleccionar Tratamiento --</option>
                                    <?php 
                                    $nombreEnLista = false;
                                    foreach ($listTratamientos as $trat): 
                                        $selected = ($cita_edit['nombre_tratamiento'] === $trat['nombre_tratamiento']);
                                        if ($selected) { $nombreEnLista = true; }
                                    ?>
                                        <option value="<?php echo htmlspecialchars($trat['nombre_tratamiento']); ?>" <?php echo $selected ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($trat['nombre_tratamiento']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="otro" <?php echo ($editMode && !empty($cita_edit['nombre_tratamiento']) && !$nombreEnLista) ? 'selected' : ''; ?>>
                                        -- Otro (Ingresar manualmente) --
                                    </option>
                                </select>
                            </div>

                            <div class="form-group" id="custom-treatment-group" style="display: none;">
                                <label for="nombre_tratamiento">Nombre del Tratamiento Manual *</label>
                                <input type="text" id="nombre_tratamiento" name="nombre_tratamiento" class="form-control" placeholder="Escriba el nombre del nuevo tratamiento" value="<?php echo htmlspecialchars($cita_edit['nombre_tratamiento']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="costo">Costo del Tratamiento (Q) *</label>
                                <input type="number" id="costo" name="costo" class="form-control" placeholder="Ej. 350.00" min="0" step="0.01" required value="<?php echo htmlspecialchars($cita_edit['costo']); ?>">
                            </div>

                            <div class="form-group form-group-full">
                                <label for="motivo">Motivo de la Consulta *</label>
                                <input type="text" id="motivo" name="motivo" class="form-control" placeholder="Ej. Dolor agudo, revisión de brackets..." required value="<?php echo htmlspecialchars($cita_edit['motivo']); ?>">
                            </div>

                            <div class="form-group form-group-full">
                                <label for="descripcion_tratamiento">Descripción Detallada del Tratamiento</label>
                                <textarea id="descripcion_tratamiento" name="descripcion_tratamiento" class="form-control" placeholder="Detalles u observaciones del tratamiento dental..."><?php echo htmlspecialchars($cita_edit['descripcion_tratamiento'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="patient-form-actions">
                            <?php if ($editMode): ?>
                                <a href="citas.php" class="btn btn-outline">
                                    Cancelar
                                </a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $editMode ? 'Actualizar Cita' : 'Programar Cita'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panel Inferior: Listado de Citas con INNER JOIN -->
            <div class="patients-list-section">
                <div class="table-actions">
                    <form method="GET" action="citas.php" class="search-box">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="search" class="form-control" placeholder="Buscar por paciente, dentista, tratamiento..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                    <?php if (!empty($search)): ?>
                        <a href="citas.php" class="btn btn-outline btn-xs">Limpiar búsqueda</a>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Paciente</th>
                                <th>Dentista</th>
                                <th>Fecha y Hora</th>
                                <th>Motivo / Tratamiento</th>
                                <th>Costo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($citas) > 0): ?>
                                <?php foreach ($citas as $cita): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cita['paciente_apellido'] . ', ' . $cita['paciente_nombre']); ?></strong>
                                            <br>
                                            <small style="color: var(--text-muted);">Cita ID: <?php echo $cita['id_cita']; ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($cita['dentista_apellido'] . ', ' . $cita['dentista_nombre']); ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $date = new DateTime($cita['fecha_hora']);
                                            echo $date->format('d/m/Y h:i A'); 
                                            ?>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.88rem; color: var(--text-muted);">Motivo: <?php echo htmlspecialchars($cita['motivo']); ?></span>
                                            <br>
                                            <strong>Tratamiento: <?php echo htmlspecialchars($cita['nombre_tratamiento']); ?></strong>
                                            <?php if (!empty($cita['tratamiento_descripcion'])): ?>
                                                <br>
                                                <small style="color: var(--text-muted); font-style: italic;" title="<?php echo htmlspecialchars($cita['tratamiento_descripcion']); ?>">
                                                    <?php 
                                                    echo htmlspecialchars(strlen($cita['tratamiento_descripcion']) > 50 ? substr($cita['tratamiento_descripcion'], 0, 47) . '...' : $cita['tratamiento_descripcion']); 
                                                    ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong style="color: var(--accent);">Q <?php echo number_format($cita['tratamiento_costo'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <?php 
                                            $badgeClass = '';
                                            switch($cita['estado']) {
                                                case 'Pendiente': $badgeClass = 'badge-pendiente'; break;
                                                case 'Confirmada': $badgeClass = 'badge-confirmada'; break;
                                                case 'Cancelada': $badgeClass = 'badge-cancelada'; break;
                                                case 'Atendida': $badgeClass = 'badge-atendida'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($cita['estado']); ?></span>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                <a href="citas.php?edit=<?php echo $cita['id_cita']; ?>" class="btn btn-secondary btn-xs" title="Editar Cita">
                                                    Editar
                                                </a>
                                                <a href="Citas/eliminar.php?id=<?php echo $cita['id_cita']; ?>" class="btn btn-outline btn-xs" style="color: #b91c1c; border-color: rgba(239, 68, 68, 0.2);" onclick="return confirm('¿Está seguro de que desea eliminar esta cita y su tratamiento asociado?');" title="Eliminar Cita">
                                                    Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                        No se encontraron citas médicas programadas.
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

<script>
// Catálogo de tratamientos para autocompletar costo y descripción en el formulario
const catalogoTratamientos = <?php echo json_encode($listTratamientos); ?>;

function handleTreatmentSelect(val) {
    const customInputGroup = document.getElementById('custom-treatment-group');
    const customInput = document.getElementById('nombre_tratamiento');
    const costoInput = document.getElementById('costo');
    const descInput = document.getElementById('descripcion_tratamiento');

    if (val === 'otro') {
        customInputGroup.style.display = 'block';
        customInput.value = '';
        customInput.required = true;
        customInput.focus();
        
        costoInput.value = '0.00';
        descInput.value = '';
    } else if (val === '') {
        customInputGroup.style.display = 'none';
        customInput.value = '';
        customInput.required = false;
        
        costoInput.value = '0.00';
        descInput.value = '';
    } else {
        customInputGroup.style.display = 'none';
        customInput.value = val;
        customInput.required = false;

        // Buscar el costo y descripción en el catálogo para autocompletar
        const selected = catalogoTratamientos.find(t => t.nombre_tratamiento === val);
        if (selected) {
            costoInput.value = selected.costo;
            descInput.value = selected.descripcion;
        }
    }
}

// Configurar el estado inicial al cargar la página (para el modo edición)
document.addEventListener('DOMContentLoaded', () => {
    const selectEl = document.getElementById('nombre_tratamiento_select');
    if (selectEl) {
        if (selectEl.value !== '') {
            const val = selectEl.value;
            if (val === 'otro') {
                document.getElementById('custom-treatment-group').style.display = 'block';
                document.getElementById('nombre_tratamiento').required = true;
            } else {
                handleTreatmentSelect(val);
            }
        }
    }
});
</script>

<?php include 'footer.php'; ?>
