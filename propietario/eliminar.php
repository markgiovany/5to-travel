<?php
session_start();
include("../config/config.php");

if (isset($_GET['u']) && isset($_SESSION['user_uuid'])) {
    
    $uuid_hotel = mysqli_real_escape_string($config, $_GET['u']);
    $user_uuid = $_SESSION['user_uuid'];
    $status_query = "SELECT id_status FROM status WHERE nombre = 'Inactivo' LIMIT 1";
    $status_res = mysqli_query($config, $status_query);
    
    if ($status_res && mysqli_num_rows($status_res) > 0) {
        $status_row = mysqli_fetch_assoc($status_res);
        $id_inactivo = $status_row['id_status'];
    } else {
        $id_inactivo = 2; 
    }

    $sql = "UPDATE catalogo 
            SET id_status = '$id_inactivo', 
                update_at = NOW() 
            WHERE uuid = '$uuid_hotel' 
            AND propietario_uuid = '$user_uuid'";

    if (mysqli_query($config, $sql)) {
        header("Location: propietario_dashboard.php?msg=eliminado");
        exit();
    } else {
        echo "Error al desactivar: " . mysqli_error($config);
    }
} else {
    header("Location: propietario_dashboard.php");
    exit();
}
?>