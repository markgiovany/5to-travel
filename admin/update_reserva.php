<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['uuid']) && isset($_GET['action'])) {
    
    $uuid_reserva = mysqli_real_escape_string($config, $_GET['uuid']);
    $action = $_GET['action'];
    
    $status_name = ($action === 'approve') ? "Confirmado" : "Cancelado";

    $query_id = "SELECT id_status FROM status WHERE nombre = '$status_name' LIMIT 1";
    $res_id = mysqli_query($config, $query_id);
    
    if ($res_id && mysqli_num_rows($res_id) > 0) {
        $row = mysqli_fetch_assoc($res_id);
        $id_encontrado = $row['id_status'];

        $update_query = "UPDATE res_reserva SET id_status = $id_encontrado WHERE uuid_reserva = '$uuid_reserva'";
        
        if (mysqli_query($config, $update_query)) {
            $_SESSION['flash'] = [
                'type'  => ($action === 'approve') ? 'success' : 'warning',
                'title' => ($action === 'approve') ? '¡Aprobada!' : 'Cancelada',
                'msg'   => "La reservación ha sido actualizada a: $status_name."
            ];
            header("Location: reservaciones.php");
            exit();
        } else {
            die("Error en el UPDATE: " . mysqli_error($config));
        }
    } else {
        die("Error: No se encontró el estado '$status_name' en la tabla status.");
    }
} else {
    header("Location: reservaciones.php");
    exit();
}