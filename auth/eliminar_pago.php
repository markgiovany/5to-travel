<?php
include("../config/conexion.php");

$id = $_GET['id'];

mysqli_query($conexion, "DELETE FROM metodos_pago WHERE id='$id'");

header("Location: ../perfil.php");
?>