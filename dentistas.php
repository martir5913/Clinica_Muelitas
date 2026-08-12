<?php
// Módulo: Dentistas
$projectName = 'Clinica Dental — Muelitas';
include 'header.php';
?>

<main class="site-main">
    <div class="module-container">
        <header class="module-header">
            <div class="module-badge">Módulo Activo</div>
            <h2 class="module-title">Personal Odontológico y Especialistas</h2>
            <p class="module-subtitle">Administre el registro de dentistas, especialidades (General, Ortodoncia, Endodoncia, Cirugía, etc.), contacto y disponibilidad.</p>
        </header>

        <!-- =========================================================================
             INICIO DEL CONTENIDO ESPECÍFICO (BODY DEL MÓDULO)
             =========================================================================
             INSTRUCCIONES DE INTEGRACIÓN:
             Aquí se debera cargar el código para borrar comentarios:
             1. El formulario de Creación y Edición de Dentistas (nombre, apellido, especialidad, teléfono, correo, activo)
             2. La tabla de listado con filtros por especialidad y estado.
             3. La lógica de conexión a la base de datos (SELECT, INSERT, UPDATE, DELETE).
             ========================================================================= -->
        
        <div class="placeholder-card">
            <div class="placeholder-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="8.5" cy="7" r="4" />
                    <polyline points="17 11 19 13 23 9" />
                </svg>
            </div>
            <h3>Espacio de Trabajo para Dentistas</h3>
            <p>El código correspondiente al CRUD de Dentistas (tabla de especialistas, formulario de alta y consultas correspondientes) debe cargarse dentro de esta sección del archivo.</p>
            <div class="placeholder-tech-details">
                <!-- quitar la siguiente linea para que no choque con el codigo que se va a agregar-->
                <strong>Tabla Relacionada en BD:</strong> <code>dentistas</code> (Campos: id_dentista, nombre, apellido, especialidad, telefono, correo, activo)
            </div>
        </div>

        <!-- =========================================================================
             FIN DEL CONTENIDO ESPECÍFICO (BODY DEL MÓDULO)
             ========================================================================= -->
    </div>
</main>

<?php include 'footer.php'; ?>
