<?php
session_start();
include("../conexion.php");

$email = $_POST['email'];
$password = hash('md2', $_POST['password']);

$query = "SELECT e.user_uuid, l.password 
          FROM usr_emails e
          INNER JOIN usr_users_login l ON e.user_uuid = l.user_uuid
          WHERE e.email = '$email' AND l.password = '$password'";

$resultado = mysqli_query($conexion, $query);

if(mysqli_num_rows($resultado) > 0){
    $datos = mysqli_fetch_assoc($resultado);
    $_SESSION['user_uuid'] = $datos['user_uuid'];
    header("Location: ../index.html"); 
} else {
    echo "Correo o contraseña incorrectos.";
}
?>