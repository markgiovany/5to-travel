<?php
session_start();
include("../config/conexion.php"); 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Acceso denegado");
}

if (isset($_GET['id'])) {
    $uuid = mysqli_real_escape_string($conexion, $_GET['id']);

    mysqli_begin_transaction($conexion);

    try {
        mysqli_query($conexion, "DELETE FROM usr_emails WHERE user_uuid = '$uuid'");
        
        mysqli_query($conexion, "DELETE FROM usr_users_login WHERE user_uuid = '$uuid'");
        
        mysqli_query($conexion, "DELETE FROM usr_users WHERE uuid = '$uuid'");

        mysqli_commit($conexion);
        

        header("Location: users.php?msg=deleted");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        die("Error en la base de datos: " . $e->getMessage());
    }
} else {
    header("Location: users.php");
    exit();
}
?>