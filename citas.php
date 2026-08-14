<?php
// Modulo: Citas
$projectName = 'Clinica Dental — Muelitas';
include 'header.php';
?>

<main class="site-main">
    <div class="module-container">
        <header class="module-header">
            <div class="module-badge">Módulo Activo</div>
            <h2 class="module-title">Agenda y Control de Citas Médicas</h2>
            <p class="module-subtitle">Programe y realice el seguimiento de las citas de los pacientes con sus respectivos dentistas asignados y controle su estado (Pendiente, Confirmada, Cancelada, Atendida).</p>
        </header>

        <!-- =========================================================================
             INICIO DEL CONTENIDO ESPECÍFICO (BODY DEL MÓDULO)
             =========================================================================
             INSTRUCCIONES DE INTEGRACIÓN:
             Aquí se debera cargar el código para:
             1. El formulario de Programación y Edición de Citas (fecha_hora, motivo, estado, id_paciente, id_dentista)
             2. La agenda, calendario o tabla de citas ordenadas cronológicamente con estados visuales diferenciados.
             3. La lógica de conexión a la base de datos (SELECT, INSERT, UPDATE, DELETE).
             ========================================================================= -->
        
        <div class="placeholder-card">
            <div class="placeholder-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
            </div>
            <h3>Espacio de Trabajo para Citas</h3>
            <p>El código correspondiente al CRUD de Citas (agenda de consultas, selección de paciente/dentista, y estados) debe cargarse dentro de esta sección del archivo.</p>
            <div class="placeholder-tech-details">
                <!-- quitar la siguiente linea para que no choque con el codigo que se va a agregar-->
                <strong>Tabla Relacionada en BD:</strong> <code>citas</code> (Campos: id_cita, fecha_hora, motivo, estado, id_paciente, id_dentista)
            </div>
        </div>

        <!-- =========================================================================
             FIN DEL CONTENIDO ESPECÍFICO (BODY DEL MÓDULO)
             ========================================================================= -->
    </div>
</main>

<?php include 'footer.php'; ?>
