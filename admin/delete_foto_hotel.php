<?php
session_start();
include("../config/config.php");

if (isset($_GET['id_img']) && isset($_GET['u'])) {
    
    $id_img = mysqli_real_escape_string($config, $_GET['id_img']);
    $uuid_hotel = mysqli_real_escape_string($config, $_GET['u']);

 
    $query_desactivar = "UPDATE cat_imagen 
                         SET id_status = (SELECT id_status FROM status WHERE nombre = 'Inactivo' LIMIT 1) 
                         WHERE id_imagen = '$id_img'";

    if (mysqli_query($config, $query_desactivar)) {
        header("Location: edit_hotel.php?u=$uuid_hotel&msg=img_disabled");
    } else {
        die("<div style='color:red; font-family:sans-serif;'><strong>Error al desactivar:</strong> " . mysqli_error($config) . "</div>");
    }
    exit();

} else {
    header("Location: hoteles.php");
    exit();
}
?>