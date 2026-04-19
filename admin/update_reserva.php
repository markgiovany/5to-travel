<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    
    $id_reserva = mysqli_real_escape_string($config, $_GET['id']);
    $action = $_GET['action'];
    
    $status_name = "";
    $msg = "";

    if ($action === 'approve') {
        $status_name = "Confirmado";
        $msg = "Reserva aprobada con éxito.";
    } elseif ($action === 'reject') {
        $status_name = "Cancelado";
        $msg = "Reserva rechazada.";
    }

    if ($status_name !== "") {
        $query_id = "SELECT id_status FROM status WHERE nombre = '$status_name' LIMIT 1";
        $res_id = mysqli_query($config, $query_id);
        
        if ($res_id && mysqli_num_rows($res_id) > 0) {
            $row = mysqli_fetch_assoc($res_id);
            $id_encontrado = $row['id_status'];

            $update_query = "UPDATE res_reserva SET id_status = '$id_encontrado' WHERE id_reserva = '$id_reserva'";
            
            if (mysqli_query($config, $update_query)) {
                header("Location: reservaciones.php?status=success&msg=" . urlencode($msg));
                exit();
            }
        } else {
            die("Error: El estatus '$status_name' no existe en la base de datos.");
        }
    }
}

header("Location: reservaciones.php");
exit();