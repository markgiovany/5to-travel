<?php
session_start();
include("../config/config.php"); 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Acceso denegado");
}

if (isset($_GET['u'])) {
    $uuid = mysqli_real_escape_string($config, $_GET['u']);

    if ($uuid === $_SESSION['user_uuid']) {
        header("Location: users.php?msg=self_delete_error");
        exit();
    }

    mysqli_begin_transaction($config);

    try {
        mysqli_query($config, "DELETE FROM usr_emails WHERE user_uuid = '$uuid'");
        
        mysqli_query($config, "DELETE FROM usr_users_login WHERE user_uuid = '$uuid'");
        
        mysqli_query($config, "DELETE FROM usr_users WHERE uuid = '$uuid'");

        mysqli_commit($config);
        

        header("Location: users.php?msg=deleted");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($config);
        die("Error en la base de datos: " . $e->getMessage());
    }
} else {
    header("Location: users.php");
    exit();
}
?>