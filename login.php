<?php
session_start();
include("../config/conexion.php");

$email = $_POST['email'];
$password_input = $_POST['password']; // sin hash aquí

$query = "SELECT e.user_uuid, l.password, l.role, u.first_name 
          FROM usr_emails e
          INNER JOIN usr_users_login l ON e.user_uuid = l.user_uuid
          INNER JOIN usr_users u ON e.user_uuid = u.uuid
          WHERE e.email = '$email'";

$resultado = mysqli_query($conexion, $query);

if(mysqli_num_rows($resultado) > 0){

    $datos = mysqli_fetch_assoc($resultado);
    $password_db = $datos['password'];

    $login_correcto = false;

    // 🔐 CASO 1: contraseña moderna (password_hash)
    if (password_verify($password_input, $password_db)) {
        $login_correcto = true;
    }

    // 🔐 CASO 2: contraseña vieja (md2)
    if (hash('md2', $password_input) === $password_db) {
        $login_correcto = true;
    }

    if($login_correcto){

        $_SESSION['user_uuid'] = $datos['user_uuid'];
        $_SESSION['role'] = $datos['role']; 
        $_SESSION['first_name'] = $datos['first_name'];

        // 🔥 REDIRECCIÓN POR ROL
        if($datos['role'] == 'admin'){
            header("Location: ../admin/admin_dashboard.php");

        } elseif($datos['role'] == 'propietario'){
            header("Location: http://localhost/5to-travel-propietario-panel/propietario/");

        } else {
            header("Location: ../home.php");
        }

        exit();

    } else {
        echo "Contraseña incorrecta.";
    }

} else {
    echo "Correo no encontrado.";
}
?>