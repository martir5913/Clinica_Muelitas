<?php
// Modulo: Citas
$projectName = 'Clinica Dental — Muelitas';
include 'header.php';
require_once 'conexion.php';

// Inicializar variables para edición
$editMode = false;
$cita_edit = [
    'id_cita' => '',
    'fecha_hora' => '',
    'motivo' => '',
    'estado' => 'Pendiente',
    'id_paciente' => '',
    'id_dentista' => ''
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

// Búsqueda de citas agrupando tratamientos (Opción B - 5 Tablas)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
try {
    if (!empty($search)) {
        $sql = "SELECT c.id_cita, c.fecha_hora, c.motivo, c.estado, c.id_paciente, c.id_dentista,
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, 
                       d.nombre AS dentista_nombre, d.apellido AS dentista_apellido,
                       COALESCE(SUM(t.costo), 0.00) AS costo_total,
                       COUNT(t.id_tratamiento) AS total_tratamientos
                FROM citas c 
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente 
                INNER JOIN dentistas d ON c.id_dentista = d.id_dentista
                LEFT JOIN tratamientos t ON c.id_cita = t.id_cita
                LEFT JOIN catalogo_tratamientos ct ON t.id_catalogo_trabajo = ct.id_catalogo_tratamiento
                WHERE p.nombre LIKE :search OR p.apellido LIKE :search 
                   OR d.nombre LIKE :search OR d.apellido LIKE :search 
                   OR c.motivo LIKE :search OR ct.nombre_tratamiento LIKE :search
                GROUP BY c.id_cita, p.id_paciente, d.id_dentista
                ORDER BY c.fecha_hora DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => '%' . $search . '%']);
    } else {
        $sql = "SELECT c.id_cita, c.fecha_hora, c.motivo, c.estado, c.id_paciente, c.id_dentista,
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, 
                       d.nombre AS dentista_nombre, d.apellido AS dentista_apellido,
                       COALESCE(SUM(t.costo), 0.00) AS costo_total,
                       COUNT(t.id_tratamiento) AS total_tratamientos
                FROM citas c 
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente 
                INNER JOIN dentistas d ON c.id_dentista = d.id_dentista
                LEFT JOIN tratamientos t ON c.id_cita = t.id_cita
                GROUP BY c.id_cita, p.id_paciente, d.id_dentista
                ORDER BY c.fecha_hora DESC";
        $stmt = $pdo->query($sql);
    }
    $citas = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_msg = "Error al listar las citas: " . $e->getMessage();
    $citas = [];
}
?>

<style>
/* Estilos para el Modal de Detalles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeInModal 0.25s ease-out;
}

.modal-card {
    background-color: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    width: 90%;
    max-width: 650px;
    box-shadow: var(--shadow-md);
    animation: slideUpModal 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.modal-card-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-card-header h3 {
    font-family: var(--font-title);
    color: var(--primary-dark);
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
}

.modal-close-btn {
    background: none;
    border: none;
    font-size: 1.75rem;
    color: var(--text-muted);
    cursor: pointer;
    line-height: 1;
    transition: var(--transition);
}

.modal-close-btn:hover {
    color: #ef4444;
}

.modal-card-body {
    padding: 24px;
}

.modal-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 16px;
}

.info-block h5 {
    font-family: var(--font-title);
    color: var(--text-muted);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}

.info-block p {
    color: var(--primary-dark);
    font-weight: 600;
    margin: 0;
    font-size: 0.95rem;
}

.info-block small {
    color: var(--text-muted);
    font-size: 0.85rem;
}

@keyframes fadeInModal {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUpModal {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<main class="site-main">
    <div class="module-container">
        <header class="module-header">
            <div class="module-badge">Módulo Activo</div>
            <h2 class="module-title">Agenda y Control de Citas Médicas</h2>
            <p class="module-subtitle">Programe citas, asigne especialistas y realice el seguimiento a tratamientos y costos asociados.</p>
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
                            <?php echo $editMode ? 'Modificar Cita Programada' : 'Programar Nueva Cita'; ?>
                        </h4>
                    </div>

                    <form method="POST" action="<?php echo $editMode ? 'Citas/actualizar.php' : 'Citas/crear.php'; ?>">
                        <?php if ($editMode): ?>
                            <input type="hidden" name="id_cita" value="<?php echo htmlspecialchars($cita_edit['id_cita']); ?>">
                        <?php endif; ?>

                        <div class="patient-form-grid">
                            <!-- Paciente -->
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

                            <!-- Dentista -->
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

                            <!-- Fecha y Hora -->
                            <div class="form-group">
                                <label for="fecha_hora">Fecha y Hora *</label>
                                <input type="datetime-local" id="fecha_hora" name="fecha_hora" class="form-control" required value="<?php echo htmlspecialchars($cita_edit['fecha_hora']); ?>">
                            </div>

                            <!-- Estado -->
                            <div class="form-group">
                                <label for="estado">Estado de la Cita</label>
                                <select id="estado" name="estado" class="form-control">
                                    <option value="Pendiente" <?php echo ($cita_edit['estado'] === 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="Confirmada" <?php echo ($cita_edit['estado'] === 'Confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                                    <option value="Cancelada" <?php echo ($cita_edit['estado'] === 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                    <option value="Atendida" <?php echo ($cita_edit['estado'] === 'Atendida') ? 'selected' : ''; ?>>Atendida</option>
                                </select>
                            </div>

                            <!-- Motivo de Consulta -->
                            <div class="form-group form-group-full">
                                <label for="motivo">Motivo de la Consulta *</label>
                                <input type="text" id="motivo" name="motivo" class="form-control" placeholder="Ej. Dolor agudo, revisión de brackets..." required value="<?php echo htmlspecialchars($cita_edit['motivo']); ?>">
                            </div>
                        </div>

                        <div class="patient-form-actions">
                            <?php if ($editMode): ?>
                                <a href="citas.php" class="btn btn-outline">Cancelar</a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $editMode ? 'Actualizar Cita' : 'Programar Cita'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Panel Inferior: Listado de Citas -->
            <div class="patients-list-section">
                <div class="table-actions">
                    <form method="GET" action="citas.php" class="search-box">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="search" class="form-control" placeholder="Buscar por paciente, dentista, motivo..." value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Motivo</th>
                                <th>Tratamientos</th>
                                <th>Costo Total</th>
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
                                            <?php echo htmlspecialchars($cita['motivo']); ?>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: var(--primary-light); color: var(--primary-hover); font-weight: 600; cursor: pointer;" onclick="openDetailsModal(<?php echo $cita['id_cita']; ?>)" title="Ver listado de tratamientos">
                                                <?php echo $cita['total_tratamientos']; ?> realiz.
                                            </span>
                                        </td>
                                        <td>
                                            <strong style="color: var(--accent);">Q <?php echo number_format($cita['costo_total'], 2); ?></strong>
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
                                                <button class="btn btn-secondary btn-xs" onclick="openDetailsModal(<?php echo $cita['id_cita']; ?>)" title="Ver Ficha de Detalle" style="color: var(--primary); background-color: var(--primary-light);">
                                                    Detalle
                                                </button>
                                                <a href="tratamientos.php?id_cita=<?php echo $cita['id_cita']; ?>" class="btn btn-secondary btn-xs" style="color: var(--accent); background-color: rgba(13, 148, 136, 0.08);" title="Gestionar Tratamientos">
                                                    + Agregar
                                                </a>
                                                <a href="citas.php?edit=<?php echo $cita['id_cita']; ?>" class="btn btn-outline btn-xs" title="Editar Cita">
                                                    Editar
                                                </a>
                                                <a href="Citas/eliminar.php?id=<?php echo $cita['id_cita']; ?>" class="btn btn-outline btn-xs" style="color: #b91c1c; border-color: rgba(239, 68, 68, 0.2);" onclick="return confirm('¿Está seguro de que desea eliminar esta cita y todos sus tratamientos asociados?');" title="Eliminar Cita">
                                                    Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 24px;">
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

<!-- Modal de Detalle de Cita -->
<div id="citas-detalle-modal" class="modal-overlay" style="display: none;">
    <div class="modal-card">
        <header class="modal-card-header">
            <h3>Detalle de la Cita Médica</h3>
            <button onclick="closeDetailsModal()" class="modal-close-btn">&times;</button>
        </header>
        <div class="modal-card-body">
            <!-- Grid de Información -->
            <div class="modal-info-grid">
                <div class="info-block">
                    <h5>Paciente</h5>
                    <p id="modal-paciente-nombre"></p>
                    <small id="modal-paciente-contacto"></small>
                </div>
                <div class="info-block">
                    <h5>Especialista</h5>
                    <p id="modal-dentista-nombre"></p>
                    <small id="modal-dentista-especialidad"></small>
                </div>
                <div class="info-block">
                    <h5>Fecha y Hora</h5>
                    <p id="modal-cita-fecha"></p>
                </div>
                <div class="info-block">
                    <h5>Estado</h5>
                    <span id="modal-cita-estado" class="badge"></span>
                </div>
            </div>

            <div class="info-block" style="margin-top: 16px;">
                <h5>Motivo de la Cita</h5>
                <p id="modal-cita-motivo" style="font-weight: 500;"></p>
            </div>

            <!-- Tabla de Tratamientos Realizados -->
            <div class="modal-treatments-section" style="margin-top: 24px;">
                <h4 style="font-family: var(--font-title); color: var(--primary-dark); margin-bottom: 12px; font-size: 1.1rem;">Tratamientos Realizados</h4>
                <div class="table-responsive" style="max-height: 250px;">
                    <table class="custom-table" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th>Tratamiento</th>
                                <th>Observaciones</th>
                                <th>Costo</th>
                            </tr>
                        </thead>
                        <tbody id="modal-treatments-table-body">
                            <!-- Inyectado dinámicamente -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-total-sum" style="text-align: right; margin-top: 16px;">
                    <strong>Total Acumulado: </strong>
                    <strong style="color: var(--accent); font-size: 1.1rem;" id="modal-total-cost">Q 0.00</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openDetailsModal(idCita) {
    const modal = document.getElementById('citas-detalle-modal');
    
    // Hacer fetch al endpoint de detalle
    fetch('Citas/obtener_detalle.php?id_cita=' + idCita)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Rellenar información de la cita
                document.getElementById('modal-paciente-nombre').innerText = data.cita.pac_apellido + ', ' + data.cita.pac_nombre;
                document.getElementById('modal-paciente-contacto').innerText = 'Tel: ' + (data.cita.pac_telefono || 'N/A') + ' | Email: ' + (data.cita.pac_correo || 'N/A');
                
                document.getElementById('modal-dentista-nombre').innerText = data.cita.den_apellido + ', ' + data.cita.den_nombre;
                document.getElementById('modal-dentista-especialidad').innerText = 'Especialidad: ' + data.cita.den_especialidad;
                
                document.getElementById('modal-cita-fecha').innerText = data.cita.fecha_hora_formateada;
                document.getElementById('modal-cita-motivo').innerText = data.cita.motivo;
                
                // Badge de estado
                const estadoBadge = document.getElementById('modal-cita-estado');
                estadoBadge.innerText = data.cita.estado;
                estadoBadge.className = 'badge'; // reset class
                
                let badgeClass = '';
                switch(data.cita.estado) {
                    case 'Pendiente': badgeClass = 'badge-pendiente'; break;
                    case 'Confirmada': badgeClass = 'badge-confirmada'; break;
                    case 'Cancelada': badgeClass = 'badge-cancelada'; break;
                    case 'Atendida': badgeClass = 'badge-atendida'; break;
                }
                estadoBadge.classList.add(badgeClass);
                
                // Rellenar tratamientos
                const tbody = document.getElementById('modal-treatments-table-body');
                tbody.innerHTML = ''; // Limpiar
                
                if (data.tratamientos.length > 0) {
                    data.tratamientos.forEach(t => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><strong>${t.nombre_tratamiento}</strong></td>
                            <td>${t.observaciones || '<span style="color:var(--text-muted); font-style:italic;">Sin observaciones</span>'}</td>
                            <td><strong style="color:var(--accent);">Q ${parseFloat(t.costo).toFixed(2)}</strong></td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td colspan="3" style="text-align:center; color:var(--text-muted); padding:16px;">No se registraron tratamientos para esta cita.</td>`;
                    tbody.appendChild(tr);
                }
                
                // Actualizar total
                document.getElementById('modal-total-cost').innerText = 'Q ' + parseFloat(data.costo_total).toFixed(2);
                
                // Mostrar modal
                modal.style.display = 'flex';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al conectar con el servidor.');
        });
}

function closeDetailsModal() {
    document.getElementById('citas-detalle-modal').style.display = 'none';
}

// Cerrar si se hace clic fuera del contenido del modal
window.addEventListener('click', (e) => {
    const modal = document.getElementById('citas-detalle-modal');
    if (e.target === modal) {
        closeDetailsModal();
    }
});
</script>

<?php include 'footer.php'; ?>
