<?php
session_start();
include("../config/conexion.php");

$email = $_POST['email'];
$password = hash('md2', $_POST['password']); 

$query = "SELECT e.user_uuid, l.password, l.role, u.first_name 
          FROM usr_emails e
          INNER JOIN usr_users_login l ON e.user_uuid = l.user_uuid
          INNER JOIN usr_users u ON e.user_uuid = u.uuid
          WHERE e.email = '$email' AND l.password = '$password'";

$resultado = mysqli_query($conexion, $query);

if(mysqli_num_rows($resultado) > 0){
    $datos = mysqli_fetch_assoc($resultado);
    
    $_SESSION['user_uuid'] = $datos['user_uuid'];
    $_SESSION['role'] = $datos['role']; 
    $_SESSION['first_name'] = $datos['first_name'];

    if($datos['role'] == 'admin'){
        header("Location: ../admin/admin_dashboard.php");
    } elseif($datos['role'] == 'propietario'){
        header("Location: ../owner_dashboard.php");
    } else {
        header("Location: ../home.php");
    }
    exit(); 
} else {
    echo "Correo o contraseña incorrectos.";
}
?>