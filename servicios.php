<?php
// Modulo: Servicios (Catálogo de Tratamientos)
require_once 'conexion.php';

// Inicializar variables para edición
$editMode = false;
$servicio_edit = [
    'id_catalogo_tratamiento' => '',
    'nombre_tratamiento' => '',
    'descripcion_estandar' => '',
    'costo_estandar' => '0.00'
];

// Comprobar si se solicita editar un servicio
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $id_edit = intval($_GET['edit']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM catalogo_tratamientos WHERE id_catalogo_tratamiento = :id");
        $stmt->execute([':id' => $id_edit]);
        $servicio = $stmt->fetch();
        if ($servicio) {
            $servicio_edit = $servicio;
            $editMode = true;
        }
    } catch (\PDOException $e) {
        $error_msg = "Error al obtener datos del servicio para edición: " . $e->getMessage();
    }
}


// Búsqueda de servicios en el catálogo
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
try {
    if (!empty($search)) {
        $sql = "SELECT * FROM catalogo_tratamientos 
                WHERE nombre_tratamiento LIKE :search OR descripcion_estandar LIKE :search 
                ORDER BY nombre_tratamiento ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':search' => '%' . $search . '%']);
    } else {
        $sql = "SELECT * FROM catalogo_tratamientos ORDER BY nombre_tratamiento ASC";
        $stmt = $pdo->query($sql);
    }
    $servicios = $stmt->fetchAll();
} catch (\PDOException $e) {
    $error_msg = "Error al listar los servicios: " . $e->getMessage();
    $servicios = [];
}

$projectName = 'Clinica Dental — Muelitas';
$extraStyles = file_exists('style-tratamientos-module.css') ? 'style-tratamientos-module.css' : '../style-tratamientos-module.css';
include 'header.php';
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
                Catálogo de Servicios Odontológicos
            </h2>
            <p class="module-subtitle">
                Gestione los procedimientos clínicos estándar de la clínica y sus costos de referencia.
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
                 FORMULARIO DE SERVICIOS
                 ================================================================ -->
            <div class="patient-form-section">
                <section class="treatment-card">

                    <div class="treatment-card-header">
                        <div class="treatment-icon">
                            <!-- SVG Servicio / Catálogo -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                        <div>
                            <h3><?php echo $editMode ? 'Editar Servicio del Catálogo' : 'Crear Nuevo Servicio'; ?></h3>
                            <p>Complete la información del procedimiento y su costo de referencia.</p>
                        </div>
                    </div>

                    <form action="<?php echo $editMode ? 'Servicios/actualizar.php' : 'Servicios/crear.php'; ?>" method="POST" class="treatment-form">
                        <?php if ($editMode): ?>
                            <input type="hidden" name="id_catalogo_tratamiento" value="<?php echo htmlspecialchars($servicio_edit['id_catalogo_tratamiento']); ?>">
                        <?php endif; ?>

                        <!-- NOMBRE DEL SERVICIO -->
                        <div class="form-group">
                            <label for="nombre_tratamiento">
                                Nombre del Servicio *
                            </label>
                            <input
                                type="text"
                                id="nombre_tratamiento"
                                name="nombre_tratamiento"
                                class="form-control"
                                placeholder="Ej. Blanqueamiento dental láser"
                                maxlength="150"
                                required
                                value="<?php echo htmlspecialchars($servicio_edit['nombre_tratamiento']); ?>"
                            >
                        </div>

                        <!-- DESCRIPCIÓN ESTÁNDAR -->
                        <div class="form-group">
                            <label for="descripcion_estandar">
                                Descripción Estándar del Servicio
                            </label>
                            <textarea
                                id="descripcion_estandar"
                                name="descripcion_estandar"
                                class="form-control"
                                rows="3"
                                placeholder="Escriba aquí los detalles estándar del procedimiento..."
                            ><?php echo htmlspecialchars($servicio_edit['descripcion_estandar'] ?? ''); ?></textarea>
                        </div>

                        <!-- COSTO ESTÁNDAR -->
                        <div class="form-group">
                            <label for="costo_estandar">
                                Costo Estándar (Q) *
                            </label>
                            <div class="input-money">
                                <span class="currency">Q</span>
                                <input
                                    type="number"
                                    id="costo_estandar"
                                    name="costo_estandar"
                                    class="form-control"
                                    placeholder="0.00"
                                    min="0"
                                    step="0.01"
                                    required
                                    value="<?php echo htmlspecialchars($servicio_edit['costo_estandar']); ?>"
                                >
                            </div>
                        </div>

                        <!-- BOTONES -->
                        <div class="form-actions">
                            <?php if ($editMode): ?>
                                <a href="servicios.php" class="btn btn-outline">
                                    Cancelar
                                </a>
                            <?php else: ?>
                                <button type="reset" class="btn btn-secondary">
                                    Limpiar
                                </button>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <?php echo $editMode ? 'Actualizar Servicio' : 'Crear Servicio'; ?>
                            </button>
                        </div>

                    </form>
                </section>
            </div>

            <!-- ================================================================
                 LISTADO DE SERVICIOS
                 ================================================================ -->
            <div class="patients-list-section">
                <div class="table-actions">
                    <form method="GET" action="servicios.php" class="search-box">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o descripción..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                    <?php if (!empty($search)): ?>
                        <a href="servicios.php" class="btn btn-outline btn-xs">Limpiar búsqueda</a>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Servicio</th>
                                <th>Descripción Estándar</th>
                                <th>Costo Estándar</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($servicios) > 0): ?>
                                <?php foreach ($servicios as $serv): ?>
                                    <tr>
                                        <td>
                                            <code>#<?php echo $serv['id_catalogo_tratamiento']; ?></code>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($serv['nombre_tratamiento']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($serv['descripcion_estandar'] ?? 'Sin descripción'); ?>
                                        </td>
                                        <td>
                                            <strong style="color: var(--accent);">Q <?php echo number_format($serv['costo_estandar'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <div class="actions-cell">
                                                <a href="servicios.php?edit=<?php echo $serv['id_catalogo_tratamiento']; ?>" class="btn btn-secondary btn-xs" title="Editar Servicio">
                                                    Editar
                                                </a>
                                                <a href="Servicios/eliminar.php?id=<?php echo $serv['id_catalogo_tratamiento']; ?>" class="btn btn-outline btn-xs" style="color: #b91c1c; border-color: rgba(239, 68, 68, 0.2);" onclick="return confirm('¿Está seguro de que desea eliminar este servicio del catálogo?');" title="Eliminar Servicio">
                                                    Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                        No se encontraron servicios registrados en el catálogo.
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
