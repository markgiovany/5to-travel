<?php
include("../conexion.php");


$first_name = $_POST['nombre'];
$last_name  = $_POST['apellido'];
$email      = $_POST['email'];
$password   = hash('md2', $_POST['password']); 

$query_uuid = mysqli_query($conexion, "SELECT UUID() as uuid");
$uuid = mysqli_fetch_assoc($query_uuid)['uuid'];

$sql1 = "INSERT INTO usr_users (uuid, first_name, last_name, created_at) 
         VALUES ('$uuid', '$first_name', '$last_name', NOW())";

$sql2 = "INSERT INTO usr_emails (email, user_uuid) 
         VALUES ('$email', '$uuid')";

$sql3 = "INSERT INTO usr_users_login (user_uuid, password, status, created_at) 
         VALUES ('$uuid', '$password', 'active', NOW())";

if(mysqli_query($conexion, $sql1) && mysqli_query($conexion, $sql2) && mysqli_query($conexion, $sql3)){
    echo "Registro exitoso con UUID: " . $uuid;
} else {
    echo "Error: " . mysqli_error($conexion);
}
?>

