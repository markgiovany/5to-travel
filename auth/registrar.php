<?php
include("../config/config.php");

$first_name = $_POST['nombre'];
$last_name  = $_POST['apellido'];
$email      = $_POST['email'];
$telefono   = $_POST['telefono'];
$password   = hash('md2', $_POST['password']); 

// Generar UUID
$query_uuid = mysqli_query($config, "SELECT UUID() as uuid");
$uuid = mysqli_fetch_assoc($query_uuid)['uuid'];

// Inserts
$sql1 = "INSERT INTO usr_users (uuid, first_name, last_name, created_at) 
         VALUES ('$uuid', '$first_name', '$last_name', NOW())";

$sql2 = "INSERT INTO usr_emails (email, user_uuid) 
         VALUES ('$email', '$uuid')";

$sql3 = "INSERT INTO usr_users_login (user_uuid, password, role, id_status, created_at) 
         VALUES ('$uuid', '$password', 'user', 1, NOW())";

$sql4 = "INSERT INTO usr_telefonos (telefono, user_uuid) 
         VALUES ('$telefono', '$uuid')";

// Ejecutar todo
if(
    mysqli_query($config, $sql1) && 
    mysqli_query($config, $sql2) && 
    mysqli_query($config, $sql3) && 
    mysqli_query($config, $sql4)
){
    // 🔁 REDIRECCIÓN AL LOGIN
    header("Location: ../index.php");
    exit();
} else {
    echo "Error: " . mysqli_error($config);
}
?>