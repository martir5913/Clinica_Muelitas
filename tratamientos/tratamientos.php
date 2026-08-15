<?php
// Modulo: Tratamientos
require_once '../conexion.php';

// Inicializar variables para edición
$editMode = false;
$tratamiento_edit = [
    'id_tratamiento' => '',
    'id_cita' => '',
    'id_catalogo_trabajo' => '',
    'costo' => '0.00',
    'observaciones' => ''
];

// Comprobar si se solicita editar un tratamiento
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $id_edit = intval($_GET['edit']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM tratamientos WHERE id_tratamiento = :id");
        $stmt->execute([':id' => $id_edit]);
        $tratamiento = $stmt->fetch();
        if ($tratamiento) {
            $tratamiento_edit = $tratamiento;
            $editMode = true;
        }
    } catch (\PDOException $e) {
        $error_msg = "Error al obtener datos del tratamiento para edición: " . $e->getMessage();
    }
}

// Comprobar si se solicita pre-seleccionar una cita específica
if (isset($_GET['id_cita']) && intval($_GET['id_cita']) > 0) {
    $tratamiento_edit['id_cita'] = intval($_GET['id_cita']);
}

// Procesar formulario POST para creación o actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tratamiento = isset($_POST['id_tratamiento']) ? intval($_POST['id_tratamiento']) : 0;
    $id_catalogo_trabajo = isset($_POST['id_catalogo_trabajo']) ? intval($_POST['id_catalogo_trabajo']) : 0;
    $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : null;
    $costo = isset($_POST['costo']) ? floatval($_POST['costo']) : 0.00;
    $id_cita = isset($_POST['id_cita']) ? intval($_POST['id_cita']) : 0;

    if ($id_catalogo_trabajo <= 0 || $id_cita <= 0) {
        $error_msg = "Todos los campos marcados con (*) son obligatorios.";
    } else {
        try {
            if ($id_tratamiento > 0) {
                // Actualizar
                $sql = "UPDATE tratamientos 
                        SET id_cita = :id_cita, id_catalogo_trabajo = :id_catalogo, costo = :costo, observaciones = :observaciones 
                        WHERE id_tratamiento = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id_cita' => $id_cita,
                    ':id_catalogo' => $id_catalogo_trabajo,
                    ':costo' => $costo,
                    ':observaciones' => $observaciones,
                    ':id' => $id_tratamiento
                ]);
                header("Location: tratamientos.php?success=" . urlencode("Tratamiento actualizado exitosamente."));
                exit;
            } else {
                // Crear
                $sql = "INSERT INTO tratamientos (id_cita, id_catalogo_trabajo, costo, observaciones) 
                        VALUES (:id_cita, :id_catalogo, :costo, :observaciones)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id_cita' => $id_cita,
                    ':id_catalogo' => $id_catalogo_trabajo,
                    ':costo' => $costo,
                    ':observaciones' => $observaciones
                ]);
                header("Location: tratamientos.php?success=" . urlencode("Tratamiento registrado exitosamente."));
                exit;
            }
        } catch (\PDOException $e) {
            $error_msg = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

// Obtener catálogo de tratamientos estándar
try {
    $stmtCatalog = $pdo->query("SELECT * FROM catalogo_tratamientos ORDER BY nombre_tratamiento");
    $listCatalog = $stmtCatalog->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $listCatalog = [];
}

// Obtener lista de citas para el selector desplegable
try {
    $stmtCitasList = $pdo->query("SELECT c.id_cita, c.fecha_hora, c.motivo, p.nombre AS pac_nombre, p.apellido AS pac_apellido 
                                  FROM citas c 
                                  INNER JOIN pacientes p ON c.id_paciente = p.id_paciente 
                                  ORDER BY c.fecha_hora DESC");
    $listCitas = $stmtCitasList->fetchAll();
} catch (\PDOException $e) {
    $listCitas = [];
}

// Búsqueda de tratamientos realizados
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
try {
    $sql = "SELECT t.*, ct.nombre_tratamiento, c.fecha_hora, p.nombre AS pac_nombre, p.apellido AS pac_apellido, d.nombre AS den_nombre, d.apellido AS den_apellido 
            FROM tratamientos t 
            INNER JOIN catalogo_tratamientos ct ON t.id_catalogo_trabajo = ct.id_catalogo_tratamiento
            INNER JOIN citas c ON t.id_cita = c.id_cita 
            INNER JOIN pacientes p ON c.id_paciente = p.id_paciente 
            INNER JOIN dentistas d ON c.id_dentista = d.id_dentista";
    
    if (!empty($search)) {
        $sql .= " WHERE ct.nombre_tratamiento LIKE :search 
                     OR p.nombre LIKE :search OR p.apellido LIKE :search 
                     OR d.nombre LIKE :search OR d.apellido LIKE :search 
                  ORDER BY t.id_tratamiento DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => '%' . $search . '%']);
    } else {
        $sql .= " ORDER BY t.id_tratamiento DESC";
        $stmt = $pdo->query($sql);
    }
    $tratamientos = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_msg = "Error al listar tratamientos: " . $e->getMessage();
    $tratamientos = [];
}

$projectName = 'Clinica Dental — Muelitas';
$extraStyles = file_exists('style-tratamientos-module.css') ? 'style-tratamientos-module.css' : '../style-tratamientos-module.css';
include '../header.php';
?>

<main class="site-main">

    <div class="module-container">

        <!-- ================================================================
             ENCABEZADO DEL MÓDULO
             ================================================================ -->
        <header class="module-header">
            <div class="module-badge">
                Módulo Activo
            </div>
            <h2 class="module-title">
                Registro y Control de Tratamientos
            </h2>
            <p class="module-subtitle">
                Registre y administre los tratamientos dentales realizados, vinculándolos a su respectiva cita médica.
            </p>
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

        <!-- Layout Vertical Unificado -->
        <div class="pacientes-layout-vertical">

            <!-- ================================================================
                 FORMULARIO DE TRATAMIENTOS (Creación / Edición)
                 ================================================================ -->
            <div class="patient-form-section">
                <section class="treatment-card">

                    <div class="treatment-card-header">
                        <div class="treatment-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3c-1.5-1.5-4-1.5-5.5 0S5 7 5.5 9.5L8 20c.3 1.3 2 1.3 2.3 0L12 15l1.7 5c.3 1.3 2 1.3 2.3 0l2.5-10.5C19 7 18 4.5 17 3c-1.5-1.5-4-1.5-5.5 0L12 4z"/>
                            </svg>
                        </div>
                        <div>
                            <h3><?php echo $editMode ? 'Editar Tratamiento Realizado' : 'Registrar Tratamiento'; ?></h3>
                            <p>Complete la información del tratamiento dental realizado.</p>
                        </div>
                    </div>

                    <form action="tratamientos.php" method="POST" class="treatment-form">
                        <?php if ($editMode): ?>
                            <input type="hidden" name="id_tratamiento" value="<?php echo htmlspecialchars($tratamiento_edit['id_tratamiento']); ?>">
                        <?php endif; ?>

                        <!-- TRATAMIENTO DEL CATÁLOGO -->
                        <div class="form-group">
                            <label for="id_catalogo_trabajo">
                                Tratamiento (Catálogo) *
                            </label>
                            <select id="id_catalogo_trabajo" name="id_catalogo_trabajo" class="form-control" onchange="actualizarCostoEstandar(this.value)" required>
                                <option value="">-- Seleccionar Tratamiento del Catálogo --</option>
                                <?php foreach ($listCatalog as $item): ?>
                                    <option value="<?php echo $item['id_catalogo_tratamiento']; ?>" <?php echo ($item['id_catalogo_tratamiento'] == $tratamiento_edit['id_catalogo_trabajo']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($item['nombre_tratamiento']); ?> (Q <?php echo number_format($item['costo_estandar'], 2); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small>
                                Seleccione el procedimiento estándar del catálogo de servicios.
                            </small>
                        </div>

                        <!-- OBSERVACIONES PARTICULARES -->
                        <div class="form-group">
                            <label for="observaciones">
                                Observaciones Clínicas / Detalles del Paciente
                            </label>
                            <textarea
                                id="observaciones"
                                name="observaciones"
                                class="form-control"
                                rows="3"
                                placeholder="Escriba aquí los detalles del procedimiento realizado (ej. pieza dental, anestesia, recomendaciones)..."
                            ><?php echo htmlspecialchars($tratamiento_edit['observaciones'] ?? ''); ?></textarea>
                        </div>

                        <!-- COSTO E ID CITA (SELECTOR DESPLEGABLE) -->
                        <div class="form-row">
                            <!-- COSTO -->
                            <div class="form-group">
                                <label for="costo">
                                    Costo Aplicado *
                                </label>
                                <div class="input-money">
                                    <span class="currency">Q</span>
                                    <input
                                        type="number"
                                        id="costo"
                                        name="costo"
                                        class="form-control"
                                        placeholder="0.00"
                                        min="0"
                                        step="0.01"
                                        required
                                        value="<?php echo htmlspecialchars($tratamiento_edit['costo']); ?>"
                                    >
                                </div>
                                <small>
                                    El costo se autocompleta con el valor estándar del catálogo, pero puede ser editado para aplicar descuentos.
                                </small>
                            </div>

                            <!-- ID CITA SELECTOR -->
                            <div class="form-group">
                                <label for="id_cita">
                                    Cita Médica Relacionada *
                                </label>
                                <select id="id_cita" name="id_cita" class="form-control" required>
                                    <option value="">-- Seleccionar Cita del Paciente --</option>
                                    <?php foreach ($listCitas as $cita): ?>
                                        <option value="<?php echo $cita['id_cita']; ?>" <?php echo ($cita['id_cita'] == $tratamiento_edit['id_cita']) ? 'selected' : ''; ?>>
                                            ID: <?php echo $cita['id_cita']; ?> · <?php echo htmlspecialchars($cita['pac_apellido'] . ', ' . $cita['pac_nombre']); ?> — <?php echo htmlspecialchars($cita['motivo']); ?> (<?php echo date('d/m/Y h:i A', strtotime($cita['fecha_hora'])); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small>
                                    Cita en la que se llevó a cabo el tratamiento.
                                </small>
                            </div>
                        </div>

                        <!-- INFORMACIÓN -->
                        <div class="treatment-info">
                            <div class="info-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                            </div>
                            <p>
                                El ID del tratamiento se genera automáticamente. Debe asociarlo a una cita para saber a qué paciente y odontólogo corresponde.
                            </p>
                        </div>

                        <!-- BOTONES -->
                        <div class="form-actions">
                            <?php if ($editMode): ?>
                                <a href="tratamientos.php" class="btn btn-outline">
                                    Cancelar
                                </a>
                            <?php else: ?>
                                <button type="reset" class="btn btn-secondary">
                                    Limpiar
                                </button>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $editMode ? 'Actualizar registro' : 'Guardar tratamiento'; ?>
                            </button>
                        </div>

                    </form>
                </section>
            </div>

            <!-- ================================================================
                 LISTADO DE TRATAMIENTOS REGISTRADOS
                 ================================================================ -->
            <div class="patients-list-section">
                <div class="table-actions">
                    <form method="GET" action="tratamientos.php" class="search-box">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="search" class="form-control" placeholder="Buscar por tratamiento, paciente, dentista..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                    <?php if (!empty($search)): ?>
                        <a href="tratamientos.php" class="btn btn-outline btn-xs">Limpiar búsqueda</a>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Paciente / Cita</th>
                                <th>Dentista</th>
                                <th>Tratamiento Realizado</th>
                                <th>Costo Aplicado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($tratamientos) > 0): ?>
                                <?php foreach ($tratamientos as $trat): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($trat['pac_apellido'] . ', ' . $trat['pac_nombre']); ?></strong>
                                            <br>
                                            <small style="color: var(--text-muted);">
                                                Cita ID: <?php echo $trat['id_cita']; ?> — <?php echo date('d/m/Y h:i A', strtotime($trat['fecha_hora'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($trat['den_apellido'] . ', ' . $trat['den_nombre']); ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($trat['nombre_tratamiento']); ?></strong>
                                            <?php if (!empty($trat['observaciones'])): ?>
                                                <br>
                                                <small style="color: var(--text-muted); font-style: italic;" title="<?php echo htmlspecialchars($trat['observaciones']); ?>">
                                                    <?php 
                                                    echo htmlspecialchars(strlen($trat['observaciones']) > 60 ? substr($trat['observaciones'], 0, 57) . '...' : $trat['observaciones']); 
                                                    ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong style="color: var(--accent);">Q <?php echo number_format($trat['costo'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                <a href="tratamientos.php?edit=<?php echo $trat['id_tratamiento']; ?>" class="btn btn-secondary btn-xs" title="Editar Tratamiento">
                                                    Editar
                                                </a>
                                                <a href="eliminar.php?id=<?php echo $trat['id_tratamiento']; ?>" class="btn btn-outline btn-xs" style="color: #b91c1c; border-color: rgba(239, 68, 68, 0.2);" onclick="return confirm('¿Está seguro de que desea eliminar este tratamiento?');" title="Eliminar Tratamiento">
                                                    Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                        No se encontraron tratamientos registrados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ================================================================
             INFORMACIÓN DE LA TABLA
             ================================================================ -->
        <section class="database-info">
            <h3>
                Información de las tablas
            </h3>
            <p>
                Este módulo utiliza una estructura normalizada de 5 tablas, separando el <code>catalogo_tratamientos</code> (catálogo maestro) de la tabla <code>tratamientos</code> (historial de procedimientos aplicados).
            </p>
            <div class="fields-list">
                <div class="field-item">
                    <strong>id_tratamiento</strong>
                    <span>INT · PK · AUTO_INCREMENT (Tabla tratamientos)</span>
                </div>
                <div class="field-item">
                    <strong>id_cita</strong>
                    <span>INT · FK (Relaciona a la tabla citas)</span>
                </div>
                <div class="field-item">
                    <strong>id_catalogo_trabajo</strong>
                    <span>INT · FK (Relaciona a la tabla catalogo_tratamientos)</span>
                </div>
                <div class="field-item">
                    <strong>costo</strong>
                    <span>DECIMAL (Costo final aplicado al paciente)</span>
                </div>
                <div class="field-item">
                    <strong>observaciones</strong>
                    <span>TEXT (Notas particulares de la intervención)</span>
                </div>
            </div>
        </section>

    </div>

</main>

<script>
// Guardar el catálogo en un objeto JS para búsquedas rápidas en cliente
const catalogoTratamientos = <?php echo json_encode($listCatalog); ?>;

function actualizarCostoEstandar(idCatalogo) {
    const costoInput = document.getElementById('costo');
    if (!idCatalogo) {
        costoInput.value = '0.00';
        return;
    }
    
    // Buscar el costo del tratamiento seleccionado
    const item = catalogoTratamientos.find(c => c.id_catalogo_tratamiento == idCatalogo);
    if (item) {
        costoInput.value = parseFloat(item.costo_estandar).toFixed(2);
    } else {
        costoInput.value = '0.00';
    }
}
</script>

<?php include '../footer.php'; ?>