<?php
session_start();
include("../config/config.php");

// 1. Limpiar el email para evitar inyecciones
$email = mysqli_real_escape_string($config, $_POST['email']);
$password_ingresada = $_POST['password']; // La contraseña plana que puso el usuario

// 2. Buscamos al usuario solo por su email para obtener su hash guardado
$query = "SELECT e.user_uuid, l.password, l.role, u.first_name 
          FROM usr_emails e
          INNER JOIN usr_users_login l ON e.user_uuid = l.user_uuid
          INNER JOIN usr_users u ON e.user_uuid = u.uuid
          WHERE e.email = '$email' AND l.id_status = 1";

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
    
    // 3. ¡LA CLAVE! Validar la contraseña usando password_verify
    // Esto compara la clave plana con el hash de la base de datos
    if (password_verify($password_ingresada, $datos['password'])) {
        
        $_SESSION['user_uuid'] = $datos['user_uuid'];
        $_SESSION['role'] = $datos['role']; 
        $_SESSION['first_name'] = $datos['first_name'];

        // 4. Redirección por roles
        if($datos['role'] == 'admin'){
            header("Location: ../admin/admin_dashboard.php");
        } elseif($datos['role'] == 'propietario'){
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