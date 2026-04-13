<?php
session_start();
include("../config/config.php");

$user_id = $_SESSION['user_uuid'];

$tipo = $_POST['tipo'];
$numero = $_POST['numero'];

mysqli_query($config, "
INSERT INTO metodos_pago (user_id, tipo, numero)
VALUES ('$user_id', '$tipo', '$numero')
");

header("Location: ../perfil.php");
?>