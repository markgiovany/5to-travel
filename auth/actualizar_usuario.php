<?php
session_start();
include("../config/config.php");

$user_id = $_SESSION['user_uuid'];
$nombre  = mysqli_real_escape_string($config, $_POST['nombre']);
$apellido= mysqli_real_escape_string($config, $_POST['apellido']);
$email   = mysqli_real_escape_string($config, $_POST['email']);
$telefono= preg_replace('/[^0-9]/', '', $_POST['telefono']);

if (strlen($telefono) !== 10) {
    header("Location: ../perfil.php?error=telefono");
    exit();
}

mysqli_query($config, "UPDATE usr_users SET first_name='$nombre', last_name='$apellido' WHERE uuid='$user_id'");
mysqli_query($config, "UPDATE usr_emails SET email='$email' WHERE user_uuid='$user_id'");
mysqli_query($config, "UPDATE usr_telefonos SET telefono='$telefono' WHERE user_uuid='$user_id'");

header("Location: ../perfil.php?update=success");
?>