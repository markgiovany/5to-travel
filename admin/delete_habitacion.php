<?php
session_start();
include("../config/config.php");

// 1. Validación de sesión
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// 2. Validar que vengan los parámetros necesarios (nombre de la habitación y uuid del hotel)
if (isset($_GET['name']) && isset($_GET['u'])) {
    $nombre_hab = mysqli_real_escape_string($config, $_GET['name']);
    $uuid_hotel = mysqli_real_escape_string($config, $_GET['u']);

    // Iniciamos transacción para asegurar consistencia
    mysqli_begin_transaction($config);

    try {
        // A. Obtener IDs necesarios
        // Buscamos el hotel para confirmar que existe y obtener su ID interno
        $res_h = mysqli_query($config, "SELECT id_catalogo FROM catalogo WHERE uuid = '$uuid_hotel'");
        $hotel = mysqli_fetch_assoc($res_h);
        
        if (!$hotel) throw new Exception("Hotel no encontrado.");
        $id_hotel = $hotel['id_catalogo'];

        // Buscamos la habitación específica dentro de ese hotel
        $res_hab = mysqli_query($config, "SELECT id_habitacion FROM cat_catalogo_habitacion WHERE nombre = '$nombre_hab' AND id_catalogo = '$id_hotel'");
        $hab = mysqli_fetch_assoc($res_hab);
        
        if (!$hab) throw new Exception("Habitación no encontrada.");
        $id_hab = $hab['id_habitacion'];

        // Obtenemos el ID de estatus "Inactivo"
        $res_status = mysqli_query($config, "SELECT id_status FROM status WHERE nombre = 'Inactivo' LIMIT 1");
        $status_row = mysqli_fetch_assoc($res_status);
        $id_inactivo = $status_row['id_status'] ?? 2;

        // B. Desactivar la Habitación
        $query_del_hab = "UPDATE cat_catalogo_habitacion SET id_status = $id_inactivo WHERE id_habitacion = $id_hab";
        if (!mysqli_query($config, $query_del_hab)) throw new Exception("Error al desactivar la habitación.");

        // C. Desactivar fotos y DESVINCULARLAS de la habitación
        // Ponemos id_habitacion = NULL y cambiamos el status a inactivo.
        // El WHERE asegura que solo toquemos las fotos que actualmente pertenecen a esta habitación.
        $query_upd_imgs = "UPDATE cat_imagen 
                           SET id_habitacion = NULL, 
                               id_status = $id_inactivo 
                           WHERE id_habitacion = $id_hab";
        
        if (!mysqli_query($config, $query_upd_imgs)) throw new Exception("Error al actualizar las imágenes.");

        // D. Confirmar cambios
        mysqli_commit($config);
        
        header("Location: habitaciones.php?u=$uuid_hotel&msg=deleted");
        exit();

    } catch (Exception $e) {
        // Si algo falla, revertimos todos los cambios
        mysqli_rollback($config);
        die("Error en la desactivación: " . $e->getMessage());
    }
} else {
    // Si no vienen parámetros, redirigir a la lista de hoteles
    header("Location: hoteles.php");
    exit();
}
?>