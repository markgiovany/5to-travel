<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_hotel = mysqli_real_escape_string($conexion, $_GET['id']);

    mysqli_begin_transaction($conexion);

    try {
        mysqli_query($conexion, "DELETE FROM cat_imagen WHERE id_catalogo = '$id_hotel'");

        mysqli_query($conexion, "DELETE FROM catalogo WHERE id_catalogo = '$id_hotel'");

        mysqli_commit($conexion);
        header("Location: hoteles.php?msg=deleted");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    header("Location: hoteles.php");
    exit();
}
?>