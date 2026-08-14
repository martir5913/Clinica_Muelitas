<?php
// ================================================================
// MÓDULO: TRATAMIENTOS
// ================================================================

$projectName = 'Clinica Dental — Muelitas';

include 'conexion.php';

// Mensajes
$mensaje = '';
$tipoMensaje = '';


// ================================================================
// GUARDAR NUEVO TRATAMIENTO
// ================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {

    $nombre_tratamiento = trim($_POST['nombre_tratamiento'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $costo = $_POST['costo'] ?? '';
    $id_cita = $_POST['id_cita'] ?? '';

    if (
        $nombre_tratamiento === '' ||
        $costo === '' ||
        $id_cita === ''
    ) {
        $mensaje = 'Por favor complete los campos obligatorios.';
        $tipoMensaje = 'error';
    } else {

        // Verificar primero que la cita exista
        $sqlCita = "SELECT id_cita FROM citas WHERE id_cita = ?";
        $stmtCita = $conexion->prepare($sqlCita);
        $stmtCita->bind_param("i", $id_cita);
        $stmtCita->execute();
        $resultadoCita = $stmtCita->get_result();

        if ($resultadoCita->num_rows === 0) {

            $mensaje = 'El ID de cita indicado no existe.';
            $tipoMensaje = 'error';

        } else {

            $sql = "INSERT INTO tratamientos
                    (nombre_tratamiento, descripcion, costo, id_cita)
                    VALUES (?, ?, ?, ?)";

            $stmt = $conexion->prepare($sql);

            if ($stmt) {

                $stmt->bind_param(
                    "ssdi",
                    $nombre_tratamiento,
                    $descripcion,
                    $costo,
                    $id_cita
                );

                if ($stmt->execute()) {

                    $mensaje = 'Tratamiento registrado correctamente.';
                    $tipoMensaje = 'success';

                } else {

                    $mensaje = 'Error al guardar el tratamiento: ' . $stmt->error;
                    $tipoMensaje = 'error';
                }

                $stmt->close();

            } else {

                $mensaje = 'Error al preparar la consulta.';
                $tipoMensaje = 'error';
            }
        }

        $stmtCita->close();
    }
}


// ================================================================
// ELIMINAR TRATAMIENTO
// ================================================================

if (isset($_GET['eliminar'])) {

    $id_tratamiento = (int) $_GET['eliminar'];

    if ($id_tratamiento > 0) {

        $sql = "DELETE FROM tratamientos
                WHERE id_tratamiento = ?";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param("i", $id_tratamiento);

        if ($stmt->execute()) {

            $stmt->close();

            // Evita que al actualizar el navegador vuelva a eliminar
            header("Location: tratamientos.php?eliminado=1");
            exit;

        } else {

            $mensaje = 'No se pudo eliminar el tratamiento.';
            $tipoMensaje = 'error';
        }

        $stmt->close();
    }
}


// ================================================================
// MENSAJE DESPUÉS DE ELIMINAR
// ================================================================

if (isset($_GET['eliminado'])) {
    $mensaje = 'Tratamiento eliminado correctamente.';
    $tipoMensaje = 'success';
}


// ================================================================
// CONSULTAR TRATAMIENTOS
// ================================================================

$sqlTratamientos = "
    SELECT
        id_tratamiento,
        nombre_tratamiento,
        descripcion,
        costo,
        id_cita
    FROM tratamientos
    ORDER BY id_tratamiento DESC
";

$resultadoTratamientos = $conexion->query($sqlTratamientos);


include 'header.php';
?>


<main class="site-main">

    <div class="module-container">


        <!-- ================================================================
             ENCABEZADO
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
             MENSAJES
             ================================================================ -->

        <?php if ($mensaje !== ''): ?>

            <div class="alert <?= $tipoMensaje === 'success'
                ? 'alert-success'
                : 'alert-error' ?>">

                <?= htmlspecialchars($mensaje) ?>

            </div>

        <?php endif; ?>


        <!-- ================================================================
             FORMULARIO NUEVO TRATAMIENTO
             ================================================================ -->

        <section class="treatment-card">

            <div class="treatment-card-header">

                <div class="treatment-icon">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M12 3c-1.5-1.5-4-1.5-5.5 0S5 7 5.5 9.5L8 20c.3 1.3 2 1.3 2.3 0L12 15l1.7 5c.3 1.3 2 1.3 2.3 0l2.5-10.5C19 7 18 4.5 17 3c-1.5-1.5-4-1.5-5.5 0L12 4z"
                        />
                    </svg>

                </div>

                <div>

                    <h3>Nuevo Tratamiento</h3>

                    <p>
                        Complete la información del tratamiento.
                    </p>

                </div>

            </div>


            <form
                action="tratamientos.php"
                method="POST"
                class="treatment-form"
            >


                <!-- NOMBRE -->

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
                        maxlength="50"
                        required
                    >

                </div>


                <!-- DESCRIPCIÓN -->

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
                            Debe corresponder a una cita existente.
                        </small>

                    </div>

                </div>


                <div class="treatment-info">

                    <p>
                        El ID del tratamiento se genera automáticamente
                        y quedará relacionado con la cita mediante
                        <strong>id_cita</strong>.
                    </p>

                </div>


                <!-- BOTONES -->

                <div class="form-actions">

                    <button
                        type="reset"
                        class="btn btn-secondary"
                    >
                        Limpiar
                    </button>

                    <button
                        type="submit"
                        name="guardar"
                        class="btn btn-primary"
                    >
                        Guardar tratamiento
                    </button>

                </div>

            </form>

        </section>



        <!-- ================================================================
             TRATAMIENTOS REGISTRADOS
             ================================================================ -->

        <section class="treatment-card">

            <div class="treatment-card-header">

                <div>

                    <h3>
                        Tratamientos registrados
                    </h3>

                    <p>
                        Consulte o elimine los tratamientos existentes.
                    </p>

                </div>

            </div>


            <div class="table-responsive">

                <table class="treatment-table">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Tratamiento</th>
                            <th>Descripción</th>
                            <th>Costo</th>
                            <th>Cita</th>
                            <th>Acciones</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if (
                        $resultadoTratamientos &&
                        $resultadoTratamientos->num_rows > 0
                    ): ?>

                        <?php while (
                            $fila = $resultadoTratamientos->fetch_assoc()
                        ): ?>

                            <tr>

                                <td>
                                    <?= $fila['id_tratamiento'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $fila['nombre_tratamiento']
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $fila['descripcion']
                                    ) ?>
                                </td>

                                <td>
                                    Q <?= number_format(
                                        $fila['costo'],
                                        2
                                    ) ?>
                                </td>

                                <td>
                                    <?= $fila['id_cita'] ?>
                                </td>

                                <td>

                                    <a
                                        href="tratamientos.php?eliminar=<?= $fila['id_tratamiento'] ?>"
                                        class="btn btn-danger"
                                        onclick="
                                            return confirm(
                                                '¿Está seguro de eliminar este tratamiento?'
                                            );
                                        "
                                    >
                                        Eliminar
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>


                    <?php else: ?>

                        <tr>

                            <td colspan="6">

                                No hay tratamientos registrados.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>


    </div>

</main>


<?php include 'footer.php'; ?>