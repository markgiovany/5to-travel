<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['u'])) {
    $uuid_hotel = mysqli_real_escape_string($config, $_GET['u']);
    $res = mysqli_query($config, "SELECT id_catalogo FROM catalogo WHERE uuid = '$uuid_hotel'");
    $hotel = mysqli_fetch_assoc($res);

    if (!$hotel) {
        header("Location: hoteles.php?msg=not_found");
        exit();
    }

    $id_hotel = $hotel['id_catalogo'];

    mysqli_begin_transaction($config);

    try {
        mysqli_query($config, "DELETE FROM cat_imagen WHERE id_catalogo = '$id_hotel'");

        mysqli_query($config, "DELETE FROM cat_catalogo_habitacion WHERE id_catalogo = '$id_hotel'");

        mysqli_query($config, "DELETE FROM catalogo WHERE id_catalogo = '$id_hotel'");

        mysqli_commit($config);
        header("Location: hoteles.php?msg=deleted");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($config);
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    header("Location: hoteles.php");
    exit();
}
?>