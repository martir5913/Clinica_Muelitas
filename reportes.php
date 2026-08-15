<?php
// Modulo: Reporte de Tratamientos Consolidados (INNER JOIN)
require_once 'conexion.php';

// Inicializar variables de filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$id_dentista = isset($_GET['id_dentista']) ? intval($_GET['id_dentista']) : 0;
$especialidad = isset($_GET['especialidad']) ? trim($_GET['especialidad']) : '';
$fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';

// Obtener la lista de dentistas para el dropdown
$listDentistas = [];
try {
    $stmtD = $pdo->query("SELECT id_dentista, nombre, apellido FROM dentistas WHERE activo = 1 ORDER BY apellido, nombre");
    $listDentistas = $stmtD->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $error_msg = "Error al cargar dentistas: " . $e->getMessage();
}

// Especialidades estándar de la clínica
$especialidades = ['General', 'Ortodoncia', 'Endodoncia', 'Cirugia', 'Pediatria', 'Periodoncia', 'Protesis'];

// Construcción de la consulta dinámica utilizando INNER JOIN
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(p.nombre LIKE :search OR p.apellido LIKE :search 
                 OR d.nombre LIKE :search OR d.apellido LIKE :search 
                 OR ct.nombre_tratamiento LIKE :search OR c.motivo LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($id_dentista > 0) {
    $where[] = "d.id_dentista = :id_dentista";
    $params[':id_dentista'] = $id_dentista;
}

if (!empty($especialidad)) {
    $where[] = "d.especialidad = :especialidad";
    $params[':especialidad'] = $especialidad;
}

if (!empty($fecha_desde)) {
    $where[] = "c.fecha_hora >= :fecha_desde";
    $params[':fecha_desde'] = $fecha_desde . ' 00:00:00';
}

if (!empty($fecha_hasta)) {
    $where[] = "c.fecha_hora <= :fecha_hasta";
    $params[':fecha_hasta'] = $fecha_hasta . ' 23:59:59';
}

// Consulta de Reporte INNER JOIN combinando 5 tablas relacionales
$sql = "SELECT 
            t.id_tratamiento,
            p.nombre AS pac_nombre,
            p.apellido AS pac_apellido,
            p.telefono AS pac_telefono,
            p.correo AS pac_correo,
            d.nombre AS den_nombre,
            d.apellido AS den_apellido,
            d.especialidad AS den_especialidad,
            c.id_cita,
            c.fecha_hora AS cita_fecha_hora,
            c.motivo AS cita_motivo,
            c.estado AS cita_estado,
            ct.nombre_tratamiento AS tratamiento_nombre,
            t.costo AS costo_aplicado,
            t.observaciones AS tratamiento_observaciones
        FROM tratamientos t
        INNER JOIN citas c ON t.id_cita = c.id_cita
        INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
        INNER JOIN dentistas d ON c.id_dentista = d.id_dentista
        INNER JOIN catalogo_tratamientos ct ON t.id_catalogo_trabajo = ct.id_catalogo_tratamiento";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY c.fecha_hora DESC, t.id_tratamiento DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    $error_msg = "Error al generar el reporte: " . $e->getMessage();
    $reportData = [];
}

// Calcular métricas/KPIs sobre los datos filtrados
$totalTratamientos = count($reportData);
$totalFacturado = 0;
$costoPromedio = 0;

if ($totalTratamientos > 0) {
    foreach ($reportData as $row) {
        $totalFacturado += floatval($row['costo_aplicado']);
    }
    $costoPromedio = $totalFacturado / $totalTratamientos;
}

// Configuración de encabezados y estilos
$projectName = 'Clinica Dental — Muelitas';
$extraStyles = 'style-reportes-module.css';
include 'header.php';
?>

<main class="site-main">
    <div class="module-container">

        <!-- ================================================================
             ENCABEZADO DE IMPRESIÓN (OCULTO EN PANTALLA)
             ================================================================ -->
        <div class="print-only-header">
            <div class="print-logo-row">
                <div class="print-logo-title">
                    <!-- SVG Dental Duplicado para Impresión -->
                    <svg style="width: 28px; height: 28px; color: #0284c7;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 10.5c0-3.5 1.5-6 5-6s5 2.5 5 6c0 4-2 7-2.5 9-.2.8-.8 1.3-1.6 1.4-.3 0-.6 0-.8 0-.8-.1-1.4-.6-1.6-1.4-.5-2-2.5-5-2.5-9z" />
                    </svg>
                    Clinica Dental Muelitas
                </div>
                <div class="print-meta-info">
                    <strong>Reporte Oficial de Tratamientos</strong><br>
                    Fecha de Generación: <?php echo date('d/m/Y h:i A'); ?><br>
                    Usuario: Administrador del Sistema
                </div>
            </div>
            <div class="print-filters-summary">
                <strong>Filtros aplicados en la consulta:</strong>
                <?php 
                $activeFilters = [];
                if (!empty($search)) {
                    $activeFilters[] = "Búsqueda: \"" . htmlspecialchars($search) . "\"";
                }
                if ($id_dentista > 0) {
                    // Buscar el nombre del dentista seleccionado
                    $dentName = "Dentista ID " . $id_dentista;
                    foreach ($listDentistas as $dent) {
                        if ($dent['id_dentista'] == $id_dentista) {
                            $dentName = $dent['apellido'] . ", " . $dent['nombre'];
                            break;
                        }
                    }
                    $activeFilters[] = "Dentista: " . htmlspecialchars($dentName);
                }
                if (!empty($especialidad)) {
                    $activeFilters[] = "Especialidad: " . htmlspecialchars($especialidad);
                }
                if (!empty($fecha_desde) || !empty($fecha_hasta)) {
                    $fDesde = !empty($fecha_desde) ? date('d/m/Y', strtotime($fecha_desde)) : 'Inicio';
                    $fHasta = !empty($fecha_hasta) ? date('d/m/Y', strtotime($fecha_hasta)) : 'Fin';
                    $activeFilters[] = "Rango de fechas: Desde $fDesde hasta $fHasta";
                }
                
                if (empty($activeFilters)) {
                    echo "Ninguno (Mostrando todos los registros).";
                } else {
                    echo implode(" | ", $activeFilters);
                }
                ?>
            </div>
        </div>

        <!-- ================================================================
             ENCABEZADO DE PANTALLA
             ================================================================ -->
        <header class="module-header">
            <div class="module-badge">
                Módulo Administrativo
            </div>
            <h2 class="module-title">
                Reporte Consolidado (INNER JOIN)
            </h2>
            <p class="module-subtitle">
                Visualice y exporte información clínica consolidada combinando pacientes, dentistas, citas y tratamientos realizados.
            </p>
        </header>

        <!-- Mensajes de Error de Base de Datos -->
        <?php if (isset($error_msg)): ?>
            <div class="alert-container">
                <div class="alert alert-error">
                    <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span><?php echo htmlspecialchars($error_msg); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- ================================================================
             TARJETAS DE MÉTRICAS (KPIs)
             ================================================================ -->
        <div class="kpi-grid">
            <!-- KPI: Total Tratamientos -->
            <div class="kpi-card">
                <div class="kpi-icon primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="kpi-content">
                    <span class="kpi-value"><?php echo number_format($totalTratamientos); ?></span>
                    <span class="kpi-label">Tratamientos Realizados</span>
                </div>
            </div>

            <!-- KPI: Facturación Total -->
            <div class="kpi-card">
                <div class="kpi-icon accent">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <div class="kpi-content">
                    <span class="kpi-value">Q <?php echo number_format($totalFacturado, 2); ?></span>
                    <span class="kpi-label">Ingresos Consolidados</span>
                </div>
            </div>

            <!-- KPI: Costo Promedio -->
            <div class="kpi-card">
                <div class="kpi-icon warning">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                </div>
                <div class="kpi-content">
                    <span class="kpi-value">Q <?php echo number_format($costoPromedio, 2); ?></span>
                    <span class="kpi-label">Costo Promedio</span>
                </div>
            </div>
        </div>

        <!-- ================================================================
             FILTROS DE BÚSQUEDA
             ================================================================ -->
        <div class="filters-card">
            <div class="filters-header">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <h3>Filtros de Búsqueda y Parámetros</h3>
            </div>
            
            <form method="GET" action="reportes.php">
                <div class="filters-form-grid">
                    <!-- Búsqueda General -->
                    <div class="form-group">
                        <label for="search">Paciente, Dentista o Tratamiento</label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search" 
                            class="form-control" 
                            placeholder="Buscar..." 
                            value="<?php echo htmlspecialchars($search); ?>"
                        >
                    </div>

                    <!-- Dentista -->
                    <div class="form-group">
                        <label for="id_dentista">Dentista Tratante</label>
                        <select id="id_dentista" name="id_dentista" class="form-control">
                            <option value="">-- Todos los Dentistas --</option>
                            <?php foreach ($listDentistas as $dent): ?>
                                <option 
                                    value="<?php echo $dent['id_dentista']; ?>" 
                                    <?php echo ($dent['id_dentista'] == $id_dentista) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($dent['apellido'] . ', ' . $dent['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Especialidad -->
                    <div class="form-group">
                        <label for="especialidad">Especialidad</label>
                        <select id="especialidad" name="especialidad" class="form-control">
                            <option value="">-- Todas las Especialidades --</option>
                            <?php foreach ($especidades = $especialidades as $esp): ?>
                                <option 
                                    value="<?php echo $esp; ?>" 
                                    <?php echo ($esp === $especialidad) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($esp); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Fecha Desde -->
                    <div class="form-group">
                        <label for="fecha_desde">Fecha Desde</label>
                        <input 
                            type="date" 
                            id="fecha_desde" 
                            name="fecha_desde" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($fecha_desde); ?>"
                        >
                    </div>

                    <!-- Fecha Hasta -->
                    <div class="form-group">
                        <label for="fecha_hasta">Fecha Hasta</label>
                        <input 
                            type="date" 
                            id="fecha_hasta" 
                            name="fecha_hasta" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($fecha_hasta); ?>"
                        >
                    </div>
                </div>

                <div class="filters-actions">
                    <?php if (!empty($search) || $id_dentista > 0 || !empty($especialidad) || !empty($fecha_desde) || !empty($fecha_hasta)): ?>
                        <a href="reportes.php" class="btn btn-outline">Limpiar Filtros</a>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-secondary">
                        Aplicar Filtros
                    </button>
                    
                    <button type="button" class="btn btn-primary" onclick="window.print();" style="background-color: var(--accent); border-color: var(--accent);">
                        <svg style="width: 16px; height: 16px; margin-right: 6px; vertical-align: middle; fill: none; stroke: currentColor; stroke-width: 2;" viewBox="0 0 24 24">
                            <polyline points="6 9 6 2 18 2 18 9"/>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                            <rect x="6" y="14" width="12" height="8"/>
                        </svg>
                        Imprimir / Exportar PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- ================================================================
             TABLA DE RESULTADOS (REPORTE INNER JOIN)
             ================================================================ -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Cita ID</th>
                        <th>Paciente / Cliente</th>
                        <th>Dentista / Especialidad</th>
                        <th>Fecha y Hora</th>
                        <th>Tratamiento Realizado</th>
                        <th>Costo Cobrado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reportData) > 0): ?>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo htmlspecialchars($row['id_cita']); ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['pac_apellido'] . ', ' . $row['pac_nombre']); ?></strong>
                                    <br>
                                    <small style="color: var(--text-muted); font-size: 0.8rem;">
                                        Tel: <?php echo htmlspecialchars($row['pac_telefono'] ?: 'N/D'); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($row['den_apellido'] . ', ' . $row['den_nombre']); ?>
                                    <br>
                                    <span class="badge" style="background-color: var(--primary-light); color: var(--primary); font-size: 0.7rem; margin-top: 4px;">
                                        <?php echo htmlspecialchars($row['den_especialidad']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo date('d/m/Y', strtotime($row['cita_fecha_hora'])); ?></strong>
                                    <br>
                                    <small style="color: var(--text-muted);">
                                        <?php echo date('h:i A', strtotime($row['cita_fecha_hora'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['tratamiento_nombre']); ?></strong>
                                    <?php if (!empty($row['tratamiento_observaciones'])): ?>
                                        <br>
                                        <small style="color: var(--text-muted); font-style: italic;" title="<?php echo htmlspecialchars($row['tratamiento_observaciones']); ?>">
                                            Obs: <?php 
                                            echo htmlspecialchars(strlen($row['tratamiento_observaciones']) > 65 
                                                ? substr($row['tratamiento_observaciones'], 0, 62) . '...' 
                                                : $row['tratamiento_observaciones']); 
                                            ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: var(--accent); font-size: 0.95rem;">
                                        Q <?php echo number_format($row['costo_applied'] = $row['costo_aplicado'], 2); ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 36px;">
                                No se encontraron tratamientos consolidados con los filtros aplicados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ================================================================
             INFORMACIÓN DE LA TABLA
             ================================================================ -->
        <section class="database-info">
            <h3>
                Información Técnica de la Consulta (INNER JOIN)
            </h3>
            <p>
                Este reporte utiliza un <code>INNER JOIN</code> para combinar la información de 5 tablas del modelo relacional:
            </p>
            <div class="fields-list">
                <div class="field-item">
                    <strong>1. tratamientos (t)</strong>
                    <span>Tabla principal de hechos clínicos de donde se obtiene el costo cobrado y observaciones.</span>
                </div>
                <div class="field-item">
                    <strong>2. citas (c)</strong>
                    <span>Se une por <code>t.id_cita = c.id_cita</code> para obtener la fecha y hora de atención.</span>
                </div>
                <div class="field-item">
                    <strong>3. pacientes (p)</strong>
                    <span>Se une por <code>c.id_paciente = p.id_paciente</code> para desplegar el nombre y teléfono del paciente.</span>
                </div>
                <div class="field-item">
                    <strong>4. dentistas (d)</strong>
                    <span>Se une por <code>c.id_dentista = d.id_dentista</code> para desplegar el nombre y especialidad del odontólogo.</span>
                </div>
                <div class="field-item">
                    <strong>5. catalogo_tratamientos (ct)</strong>
                    <span>Se une por <code>t.id_catalogo_trabajo = ct.id_catalogo_tratamiento</code> para obtener el nombre estandarizado del procedimiento.</span>
                </div>
            </div>
        </section>

    </div>
</main>

<?php include 'footer.php'; ?>
