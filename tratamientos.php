<?php
// Modulo: Tratamientos
$projectName = 'Clinica Dental — Muelitas';
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
                Registro y Control de Tratamientos
            </h2>

            <p class="module-subtitle">
                Registre los tratamientos realizados a los pacientes,
                agregando su descripción, costo y la cita relacionada.
            </p>

        </header>


        <!-- ================================================================
             FORMULARIO DE TRATAMIENTOS
             ================================================================ -->

        <section class="treatment-card">

            <div class="treatment-card-header">

                <div class="treatment-icon">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">

                        <path d="M12 3c-1.5-1.5-4-1.5-5.5 0S5 7 5.5 9.5L8 20c.3 1.3 2 1.3 2.3 0L12 15l1.7 5c.3 1.3 2 1.3 2.3 0l2.5-10.5C19 7 18 4.5 17 3c-1.5-1.5-4-1.5-5.5 0L12 4z"/>

                    </svg>

                </div>

                <div>

                    <h3>
                        Nuevo Tratamiento
                    </h3>

                    <p>
                        Complete la información del tratamiento.
                    </p>

                </div>

            </div>


            <!-- ============================================================
                 FORMULARIO
                 ============================================================ -->

            <form
                action=""
                method="POST"
                class="treatment-form"
            >

                <!-- ========================================================
                     NOMBRE DEL TRATAMIENTO
                     ======================================================== -->

                <div class="form-group">

                    <label for="nombre_tratamiento">
                        Nombre del tratamiento
                        <span class="required">*</span>
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


                <!-- ========================================================
                     DESCRIPCIÓN
                     ======================================================== -->

                <div class="form-group">

                    <label for="descripcion">
                        Descripción
                    </label>

                    <textarea
                        id="descripcion"
                        name="descripcion"
                        rows="5"
                        placeholder="Describa el tratamiento realizado..."
                    ></textarea>

                </div>


                <!-- ========================================================
                     COSTO E ID CITA
                     ======================================================== -->

                <div class="form-row">

                    <!-- COSTO -->

                    <div class="form-group">

                        <label for="costo">
                            Costo
                            <span class="required">*</span>
                        </label>

                        <div class="input-money">

                            <span class="currency">
                                Q
                            </span>

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

                        <small>
                            Ingrese el costo del tratamiento en quetzales.
                        </small>

                    </div>


                    <!-- ID CITA -->

                    <div class="form-group">

                        <label for="id_cita">
                            ID de la cita
                            <span class="required">*</span>
                        </label>

                        <input
                            type="number"
                            id="id_cita"
                            name="id_cita"
                            placeholder="Ej. 1"
                            min="1"
                            required
                        >

                        <small>
                            Cita a la que pertenece este tratamiento.
                        </small>

                    </div>

                </div>


                <!-- ========================================================
                     INFORMACIÓN
                     ======================================================== -->

                <div class="treatment-info">

                    <div class="info-icon">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">

                            <circle cx="12" cy="12" r="10"></circle>

                            <line x1="12" y1="16"
                                  x2="12" y2="12"></line>

                            <line x1="12" y1="8"
                                  x2="12.01" y2="8"></line>

                        </svg>

                    </div>

                    <p>
                        El ID del tratamiento se genera automáticamente
                        al guardar el registro. El tratamiento quedará
                        relacionado con la cita indicada mediante
                        <strong>id_cita</strong>.
                    </p>

                </div>


                <!-- ========================================================
                     BOTONES
                     ======================================================== -->

                <div class="form-actions">

                    <button
                        type="reset"
                        class="btn btn-secondary"
                    >
                        Limpiar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Guardar tratamiento
                    </button>

                </div>

            </form>

        </section>


        <!-- ================================================================
             INFORMACIÓN DE LA TABLA
             ================================================================ -->

        <section class="database-info">

            <h3>
                Información de la tabla
            </h3>

            <p>
                Este formulario está diseñado para trabajar con la tabla
                <code>tratamiento</code> de la base de datos.
            </p>

            <div class="fields-list">

                <div class="field-item">
                    <strong>id_tratamiento</strong>
                    <span>INT · PK · AUTO_INCREMENT</span>
                </div>

                <div class="field-item">
                    <strong>nombre_tratamiento</strong>
                    <span>VARCHAR</span>
                </div>

                <div class="field-item">
                    <strong>descripcion</strong>
                    <span>TEXT</span>
                </div>

                <div class="field-item">
                    <strong>costo</strong>
                    <span>DECIMAL</span>
                </div>

                <div class="field-item">
                    <strong>id_cita</strong>
                    <span>INT · FK</span>
                </div>

            </div>

        </section>

    </div>

</main>

<?php include 'footer.php'; ?>