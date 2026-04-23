<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid'])) { exit; }

$user_id = $_SESSION['user_uuid'];

$titular = mysqli_real_escape_string($config, $_POST['titular']);
$tipo    = (int)$_POST['id_metodo_pago'];
$numero  = mysqli_real_escape_string($config, preg_replace('/[^0-9]/', '', $_POST['numero']));

mysqli_query($config, "
    INSERT INTO usr_billetera (user_uuid, id_metodo_pago, nombre_titular, datos_encriptados, id_status)
    VALUES ('$user_id', '$tipo', '$titular', '$numero', 1)
");

header("Location: ../perfil.php?pago=success");
?>