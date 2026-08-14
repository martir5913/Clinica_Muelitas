<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];
    $costo = $_POST["costo"];
    $id_cita = $_POST["id_cita"];

    $sql = "INSERT INTO tratamientos
            (nombre_tratamiento, descripcion, costo, id_cita)
            VALUES
            ('$nombre', '$descripcion', '$costo', '$id_cita')";

    if ($conexion->query($sql)) {
        header("Location: index.php");
        exit;
    }
}
?>

<form method="POST">

    <label>Nombre del tratamiento</label><br>
    <input type="text" name="nombre" required>

    <br><br>

    <label>Descripción</label><br>
    <textarea name="descripcion" required></textarea>

    <br><br>

    <label>Costo</label><br>
    <input type="number" step="0.01" name="costo" required>

    <br><br>

    <label>ID Cita</label><br>
    <input type="number" name="id_cita" required>

    <br><br>

    <button type="submit">Guardar</button>

</form>