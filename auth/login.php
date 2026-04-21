<?php
session_start();
include("../config/config.php");

$email = mysqli_real_escape_string($config, $_POST['email']);
$password_ingresada = $_POST['password'];

$query = "SELECT e.user_uuid, l.password, u.rol_name, u.first_name 
          FROM usr_emails e
          LEFT JOIN usr_users_login l ON e.user_uuid = l.user_uuid
          LEFT JOIN usr_users u ON e.user_uuid = u.uuid
          WHERE e.email = '$email' AND l.id_status = (SELECT id_status FROM status WHERE nombre = 'Activo')"; 

$resultado = mysqli_query($config, $query);

if(mysqli_num_rows($resultado) > 0){
    $datos = mysqli_fetch_assoc($resultado);
    
    if (password_verify($password_ingresada, $datos['password'])) {
        
        $_SESSION['user_uuid'] = $datos['user_uuid'];
        $_SESSION['role'] = $datos['rol_name']; 
        $_SESSION['first_name'] = $datos['first_name'];

        // Limpiamos la variable para comparar
        $user_role = trim(strtolower($datos['rol_name']));

        if($user_role == 'admin'){
            header("Location: ../admin/admin_dashboard.php");
        } elseif($user_role == 'propietario'){
            header("Location: ../propietario/propietario_dashboard.php");
        } else {
            header("Location: ../home.php");
        }
        exit(); 
        
    } else {
        echo "Contraseña incorrecta.";
    }
} else {
    echo "El correo electrónico no está registrado o la cuenta está inactiva.";
}
?>