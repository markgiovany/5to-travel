<?php
session_start();
include("../config/config.php");

// si no eres admin, pa' fuera papá
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['u'])) {
    $uuid = mysqli_real_escape_string($config, $_GET['u']);

    mysqli_begin_transaction($config);

    try {
        $res_h = mysqli_query($config, "SELECT id_catalogo, id_ubicacion FROM catalogo WHERE uuid = '$uuid' LIMIT 1");
        $hotel = mysqli_fetch_assoc($res_h);

        if ($hotel) {
            $id_hotel = $hotel['id_catalogo'];
            $id_ubicacion = $hotel['id_ubicacion'];

            $status_activo = "(SELECT id_status FROM status WHERE nombre = 'Activo' LIMIT 1)";

            mysqli_query($config, "UPDATE catalogo SET id_status = $status_activo WHERE id_catalogo = $id_hotel");
            mysqli_query($config, "UPDATE cat_ubicacion SET id_status = $status_activo WHERE id_ubicacion = $id_ubicacion");
            mysqli_query($config, "UPDATE cat_imagen SET id_status = $status_activo WHERE id_catalogo = $id_hotel");
            mysqli_query($config, "UPDATE cat_catalogo_habitacion SET id_status = $status_activo WHERE id_catalogo = $id_hotel");

            mysqli_commit($config);
            header("Location: hoteles.php?msg=updated");
        } else {
            header("Location: hoteles.php");
        }
    } catch (Exception $e) {
        mysqli_rollback($config);
        die("Error al reactivar: " . $e->getMessage());
    }
} else {
    header("Location: hoteles.php");
}
exit();