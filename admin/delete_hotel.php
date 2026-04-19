<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['u'])) {
    $uuid = mysqli_real_escape_string($config, $_GET['u']);

    // Iniciamos una transacción
    mysqli_begin_transaction($config);

    try {
        $res_h = mysqli_query($config, "SELECT id_catalogo, id_ubicacion FROM catalogo WHERE uuid = '$uuid' LIMIT 1");
        $hotel = mysqli_fetch_assoc($res_h);

        if ($hotel) {
            $id_hotel = $hotel['id_catalogo'];
            $id_ubicacion = $hotel['id_ubicacion'];

            $res_status = mysqli_query($config, "SELECT id_status FROM status WHERE nombre = 'Inactivo' LIMIT 1");
            $status_row = mysqli_fetch_assoc($res_status);
            $id_inactivo = $status_row['id_status'] ?? 2;


            // A. Desactivar el Hotel
            mysqli_query($config, "UPDATE catalogo SET id_status = $id_inactivo WHERE id_catalogo = $id_hotel");

            // B. Desactivar la Ubicación asociada
            if ($id_ubicacion) {
                mysqli_query($config, "UPDATE cat_ubicacion SET id_status = $id_inactivo WHERE id_ubicacion = $id_ubicacion");
            }

            // C. Desactivar todas las Habitaciones del hotel
            mysqli_query($config, "UPDATE cat_catalogo_habitacion SET id_status = $id_inactivo WHERE id_catalogo = $id_hotel");

            // D. Desactivar todas las Fotos (Tanto generales del hotel como de sus habitaciones)
            // Esto funciona porque en tu lógica pro, todas las fotos llevan el id_catalogo
            mysqli_query($config, "UPDATE cat_imagen SET id_status = $id_inactivo WHERE id_catalogo = $id_hotel");

            // 4. Confirmar cambios
            mysqli_commit($config);
            header("Location: hoteles.php?msg=deleted");
            exit();
        } else {
            header("Location: hoteles.php");
            exit();
        }
    } catch (Exception $e) {
        // Si falla CUALQUIERA de los pasos anteriores, se cancela todo
        mysqli_rollback($config);
        die("Error en la desactivación en cascada: " . $e->getMessage());
    }
} else {
    header("Location: hoteles.php");
    exit();
}