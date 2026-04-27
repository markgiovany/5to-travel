<?php
session_start();
include("../config/config.php");

// Verificamos que existan los dos parámetros en la URL y la sesión esté activa
if (isset($_GET['p_id']) && isset($_GET['u_id']) && isset($_SESSION['user_uuid'])) {
    
    $p_id = mysqli_real_escape_string($config, $_GET['p_id']);
    $u_id = mysqli_real_escape_string($config, $_GET['u_id']);

    // Verificación de seguridad: El u_id de la URL debe ser igual al de la sesión
    if ($u_id === $_SESSION['user_uuid']) {
        // Cambiamos el status a 2 (Eliminado/Inactivo)
        mysqli_query($config, "UPDATE usr_billetera SET id_status = 2 WHERE id_metodo_guardado = '$p_id' AND user_uuid = '$u_id'");
        
        header("Location: ../perfil.php?pago=eliminado");
        exit();
    }
}

// Si algo falla o no coinciden los IDs, simplemente regresa al perfil
header("Location: ../perfil.php");
exit();
?>