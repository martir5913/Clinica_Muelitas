<?php
// Módulo: Tratamientos
$projectName = 'Clinica Dental — Muelitas';
include 'header.php';
?>

<main class="site-main">
    <div class="module-container">
        <header class="module-header">
            <div class="module-badge">Módulo Activo</div>
            <h2 class="module-title">Catálogo de Servicios y Tratamientos</h2>
            <p class="module-subtitle">Administre la lista de tratamientos dentales ofrecidos (Limpieza, Brackets, Extracciones, etc.), descripciones y costos clínicos.</p>
        </header>

        <!-- =========================================================================
             INICIO DEL CONTENIDO ESPECÍFICO (BODY DEL MÓDULO)
             =========================================================================
             INSTRUCCIONES DE INTEGRACIÓN:
             Aquí se debera cargar el código para borrar comentarios:
             1. El formulario de Creación y Edición de Tratamientos (nombre_tratamiento, descripción, costo, id_cita)
             2. La lista de precios y catálogo de tratamientos con buscador.
             3. La lógica de conexión a la base de datos (SELECT, INSERT, UPDATE, DELETE).
             ========================================================================= -->
        
        <div class="placeholder-card">
            <div class="placeholder-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>
            </div>
            <h3>Espacio de Trabajo para Tratamientos</h3>
            <p>El código correspondiente al CRUD de Tratamientos (catálogo de procedimientos, definición de costos y consultas) debe cargarse dentro de esta sección del archivo.</p>
            <div class="placeholder-tech-details">
                <!-- quitar la siguiente linea para que no choque con el codigo que se va a agregar-->
                <strong>Tabla Relacionada en BD:</strong> <code>tratamientos</code> (Campos: id_tratamiento, nombre_tratamiento, descripcion, costo, id_cita)
            </div>
        </div>

        <!-- =========================================================================
             FIN DEL CONTENIDO ESPECÍFICO (BODY DEL MÓDULO)
             ========================================================================= -->
    </div>
</main>

<?php include 'footer.php'; ?>
