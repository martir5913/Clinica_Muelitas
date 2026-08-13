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

<body>

    <main class="page-container">

        <section class="form-card">

            <!-- Encabezado -->
            <div class="form-header">

                <div class="icon-container">
                    🦷
                </div>

                <div>
                    <span class="form-subtitle">
                        Clínica Dental Muelitas
                    </span>

                    <h1>
                        Registrar tratamiento
                    </h1>

                    <p>
                        Ingresa la información del tratamiento realizado
                        durante la cita del paciente.
                    </p>
                </div>

            </div>

            <!-- Formulario -->
            <form action="guardar_tratamiento.php" method="POST">

                <div class="form-grid">

                    <!-- Nombre del tratamiento -->
                    <div class="form-group full-width">

                        <label for="nombre_tratamiento">
                            Nombre del tratamiento
                            <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="nombre_tratamiento"
                            name="nombre_tratamiento"
                            placeholder="Ej. Limpieza dental"
                            maxlength="150"
                            required
                        >

                        <small>
                            Ejemplos: Limpieza dental, Extracción, Brackets.
                        </small>

                    </div>


                    <!-- Descripción -->
                    <div class="form-group full-width">

                        <label for="descripcion">
                            Descripción
                        </label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="5"
                            placeholder="Describe el tratamiento realizado..."
                        ></textarea>

                    </div>


                    <!-- Costo -->
                    <div class="form-group">

                        <label for="costo">
                            Costo del tratamiento
                            <span>*</span>
                        </label>

                        <div class="input-money">

                            <span>Q</span>

                            <input
                                type="number"
                                id="costo"
                                name="costo"
                                placeholder="0.00"
                                min="0"
                                step="0.01"
                                required
                            >

                        </div>

                    </div>


                    <!-- ID Cita -->
                    <div class="form-group">

                        <label for="id_cita">
                            Cita
                            <span>*</span>
                        </label>

                        <input
                            type="number"
                            id="id_cita"
                            name="id_cita"
                            placeholder="Ej. 15"
                            min="1"
                            required
                        >

                        <small>
                            Ingresa el ID de la cita relacionada.
                        </small>

                    </div>

                </div>


                <!-- Información -->
                <div class="info-box">

                    <div class="info-icon">
                        ℹ
                    </div>

                    <p>
                        El tratamiento quedará asociado a la cita
                        seleccionada mediante el campo <strong>id_cita</strong>.
                    </p>

                </div>


                <!-- Botones -->
                <div class="form-actions">

                    <button
                        type="reset"
                        class="btn btn-secondary">
                        Limpiar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">
                        Registrar tratamiento
                    </button>

                </div>

            </form>

        </section>

    </main>
        
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
