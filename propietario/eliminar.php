<?php
session_start();
include("../config/config.php");

if (isset($_GET['id']) && isset($_SESSION['user_uuid'])) {
    $id_catalogo = mysqli_real_escape_string($config, $_GET['id']);
    
    // 1. Primero buscamos el ID del estatus "Inactivo" en tu tabla 'status'
    $status_query = "SELECT id_status FROM status WHERE nombre = 'Inactivo' LIMIT 1";
    $status_res = mysqli_query($config, $status_query);
    $status_row = mysqli_fetch_assoc($status_res);
    $id_inactivo = $status_row['id_status'];

    $sql = "UPDATE catalogo 
            SET id_status = '$id_inactivo' 
            WHERE id_catalogo = '$id_catalogo' 
            AND propietario_uuid = '" . $_SESSION['user_uuid'] . "'";

    if (mysqli_query($config, $sql)) {
        header("Location: mis_hoteles.php?msg=desactivado");
    } else {
        echo "Error al desactivar: " . mysqli_error($config);
    }
}
?>