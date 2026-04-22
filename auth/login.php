<?php
session_start();
include("../config/config.php");

$email = mysqli_real_escape_string($config, $_POST['email']);
$password_ingresada = $_POST['password'];

$query = "SELECT e.user_uuid, l.password, u.rol_name, u.first_name, u.id_status
          FROM usr_emails e
          LEFT JOIN usr_users_login l ON e.user_uuid = l.user_uuid
          LEFT JOIN usr_users u ON e.user_uuid = u.uuid
          WHERE e.email = '$email'"; 

$resultado = mysqli_query($config, $query);

if(mysqli_num_rows($resultado) > 0){
    $datos = mysqli_fetch_assoc($resultado);

    if ($datos['id_status'] == 2) {
        $_SESSION['error_login'] = "Tu cuenta está marcada como inactiva. <br> Para más información entra al centro de ayuda.";
        header("Location: ../login.php");
        exit(); 
    } elseif ($datos['id_status'] == 3) {
        $_SESSION['error_login'] = "Tu cuenta está marcada como pendiente. <br> Para más información entra al centro de ayuda.";
        header("Location: ../login.php");
        exit(); 
    } elseif ($datos['id_status'] == 4) {
        $_SESSION['error_login'] = "Tu cuenta está marcada como suspendida por soporte. <br> Para más información entra al centro de ayuda.";
        header("Location: ../login.php");
        exit(); 
    } elseif ($datos['id_status'] != 1) {
        $_SESSION['error_login'] = "Tu cuenta tiene problemas de confirmación. <br> Para más información entra al centro de ayuda.";
        header("Location: ../login.php");
        exit(); 
    } 
    
    if (password_verify($password_ingresada, $datos['password'])) {
        
        $_SESSION['user_uuid'] = $datos['user_uuid'];
        $_SESSION['role'] = $datos['rol_name']; 
        $_SESSION['first_name'] = $datos['first_name'];

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
        $_SESSION['error_login'] = "Contraseña incorrecta.";
        header("Location: ../login.php");
        exit();
    }
} else {
    $_SESSION['error_login'] = "El correo electrónico no está registrado o la cuenta está inactiva.";
    header("Location: ../login.php");
    exit();
}
?>