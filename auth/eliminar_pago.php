<?php
include("../config/config.php");

$id = $_GET['id'];

mysqli_query($config, "DELETE FROM metodos_pago WHERE id='$id'");

header("Location: ../perfil.php");
?>