<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid'])) { exit; }

$user_id = $_SESSION['user_uuid'];

$nombre   = mysqli_real_escape_string($config, $_POST['nombre']);
$apellido = mysqli_real_escape_string($config, $_POST['apellido']);
$email    = mysqli_real_escape_string($config, $_POST['email']);
$telefono = preg_replace('/[^0-9]/', '', $_POST['telefono']);

if (strlen($telefono) == 10) {
    mysqli_query($config, "UPDATE usr_users SET first_name='$nombre', last_name='$apellido', updated_at=NOW() WHERE uuid='$user_id'");
    mysqli_query($config, "UPDATE usr_emails SET email='$email' WHERE user_uuid='$user_id'");
    mysqli_query($config, "UPDATE usr_telefonos SET telefono='$telefono' WHERE user_uuid='$user_id'");
    header("Location: ../perfil.php?status=success");
} else {
    header("Location: ../perfil.php?error=tel_corto");
}
?>