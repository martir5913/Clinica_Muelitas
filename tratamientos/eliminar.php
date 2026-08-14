<?php
include("conexion.php");

$id = $_GET["id"];

$conexion->query(
    "DELETE FROM tratamientos
     WHERE id_tratamiento = $id"
);

header("Location: index.php");
exit;
?>