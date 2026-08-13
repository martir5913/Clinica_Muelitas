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
            <div class="placeholder-card">
                <p>Mensaje de prueba si cargo correctamente doctores.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
