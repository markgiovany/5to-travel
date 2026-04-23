<?php
session_start();
include("../config/config.php");

if (isset($_GET['id']) && isset($_SESSION['user_uuid'])) {
    
    $id = mysqli_real_escape_string($config, $_GET['id']);
    $user_uuid = $_SESSION['user_uuid'];

    /* SOFT DELETE: No borramos físicamente, cambiamos el estatus a 2 */
    /* La tabla correcta es usr_billetera e identificamos por id_metodo_guardado */
    mysqli_query($config, "UPDATE usr_billetera 
                           SET id_status = 2 
                           WHERE id_metodo_guardado = '$id' 
                           AND user_uuid = '$user_uuid'");
}

header("Location: ../perfil.php?pago=suspendido");
exit();
?>