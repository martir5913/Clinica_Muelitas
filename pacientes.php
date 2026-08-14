<?php
// Módulo: Pacientes
$projectName = 'Clinica Dental — Muelitas';
include 'header.php';
?>

<main class="site-main">
    <div class="module-container">
        <header class="module-header">
            <div class="module-badge">Módulo Activo</div>
            <h2 class="module-title">Registro y Control de Pacientes</h2>
            <p class="module-subtitle">Administre los expedientes médicos, información de contacto y estado activo de los pacientes de la clínica.</p>
        </header>

        <!-- =========================================================================
             INICIO DEL CONTENIDO ESPECÍFICO (BODY DEL MÓDULO)
             =========================================================================
             INSTRUCCIONES DE INTEGRACIÓN:
             Aquí se debera cargar el código para borrar comentarios:
             1. El formulario de Creación y Edición de Pacientes (nombre, apellido, teléfono, correo, etc.)
             2. La tabla de listado con acciones para Editar, Desactivar y Ver detalles.
             3. La lógica de conexión a la base de datos (SELECT, INSERT, UPDATE, DELETE).
             ========================================================================= -->
        
        <div class="placeholder-card">
            <div class="placeholder-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </div>
            <h3>Espacio de Trabajo para Pacientes</h3>
            <p>El código correspondiente al CRUD de Pacientes (tabla, formulario y consultas a la base de datos) debe cargarse dentro de esta sección del archivo.</p>
            <div class="placeholder-tech-details">
                <!-- quitar la siguiente linea para que no choque con el codigo que se va a agregar-->
                <strong>Tabla Relacionada en BD:</strong> <code>pacientes</code> (Campos: id_paciente, nombre, apellido, telefono, correo, fecha_nacimiento, direccion, activo)
            </div>
        </div>

        <!-- =========================================================================
             FIN DEL CONTENIDO ESPECÍFICO (BODY DEL MÓDULO)
             ========================================================================= -->
    </div>
</main>

<?php include 'footer.php'; ?>
