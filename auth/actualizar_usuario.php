<?php
session_start();
include("../config/conexion.php");

$user_id = $_SESSION['user_uuid'];

$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email'];
$telefono = $_POST['telefono'];

/* ACTUALIZAR */
mysqli_query($conexion, "UPDATE usr_users 
SET first_name='$nombre', last_name='$apellido' 
WHERE uuid='$user_id'");

mysqli_query($conexion, "UPDATE usr_emails 
SET email='$email' 
WHERE user_uuid='$user_id'");

mysqli_query($conexion, "UPDATE usr_telefonos 
SET telefono='$telefono' 
WHERE user_uuid='$user_id'");

header("Location: ../perfil.php");
?>