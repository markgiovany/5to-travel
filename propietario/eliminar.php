<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['id'])) {

    $id_hotel = $_GET['id'];
    $propietario_uuid = $_SESSION['user_uuid'];

    /* 🔥 1. BORRAR IMÁGENES RELACIONADAS */
    $sql_imgs = "DELETE FROM cat_imagen WHERE id_catalogo = '$id_hotel'";
    mysqli_query($config, $sql_imgs);

    /* 🔥 2. BORRAR RELACIÓN DE HABITACIONES (SI EXISTE) */
    $sql_rel = "DELETE FROM cat_catalogo_habitacion WHERE id_catalogo = '$id_hotel'";
    mysqli_query($config, $sql_rel);

    /* 🔥 3. BORRAR HOTEL (YA SIN DEPENDENCIAS) */
    $sql = "
    DELETE FROM catalogo 
    WHERE id_catalogo = '$id_hotel' 
    AND propietario_uuid = '$propietario_uuid'
    ";

    if (mysqli_query($config, $sql)) {
        header("Location: propietario_dashboard.php?mensaje=eliminado");
        exit();
    } else {
        echo "Error al eliminar: " . mysqli_error($config);
    }

} else {
    header("Location: propietario_dashboard.php");
    exit();
}
?>