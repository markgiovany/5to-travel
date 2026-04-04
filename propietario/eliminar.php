<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['user_uuid'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_hotel = $_GET['id'];
    $propietario_uuid = $_SESSION['user_uuid'];

    $sql = "DELETE FROM catalogo WHERE id_catalogo = '$id_hotel' AND propietario_uuid = '$propietario_uuid'";

    if (mysqli_query($conexion, $sql)) {
        header("Location: propietario_dashboard.php?mensaje=eliminado");
    } else {
        echo "Error al eliminar: " . mysqli_error($conexion);
    }
} else {
    header("Location: propietario_dashboard.php");
}
?>