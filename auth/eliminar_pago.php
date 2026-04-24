<?php
session_start();
include("../config/config.php");

if (isset($_GET['p_id']) && isset($_GET['u_id'])) {
    $p_id = mysqli_real_escape_string($config, $_GET['p_id']);
    $u_id = mysqli_real_escape_string($config, $_GET['u_id']);

    if ($u_id === $_SESSION['user_uuid']) {
        mysqli_query($config, "UPDATE usr_billetera SET id_status = 2 WHERE id_metodo_guardado = '$p_id' AND user_uuid = '$u_id'");
    }
}
header("Location: ../perfil.php?pago=eliminado");
?>