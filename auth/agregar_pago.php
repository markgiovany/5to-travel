<?php
session_start();
include("../config/config.php");

$user_id = $_SESSION['user_uuid'];
$titular = mysqli_real_escape_string($config, $_POST['titular']);
$numero  = preg_replace('/[^0-9]/', '', $_POST['numero']);

if (strlen($numero) !== 16) {
    header("Location: ../perfil.php?error=tarjeta");
    exit();
}

mysqli_query($config, "INSERT INTO usr_billetera (user_uuid, id_metodo_pago, nombre_titular, datos_encriptados, id_status) VALUES ('$user_id', 1, '$titular', '$numero', 1)");
header("Location: ../perfil.php?pago=success");
?>