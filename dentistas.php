<?php
// Módulo: Dentistas
$projectName = 'Clinica Dental — Muelitas';
$extraStyles = 'style-dentistas-module.css'; //Carga de estilos adicionales
require_once 'conexion.php';
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
