<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("Acceso denegado");
}

if (isset($_GET['u']) && isset($_GET['to'])) {
    $uuid = mysqli_real_escape_string($config, $_GET['u']);
    $new_status = (int)$_GET['to'];

    $reason = isset($_POST['reason']) ? mysqli_real_escape_string($config, $_POST['reason']) : null;

    if ($uuid === $_SESSION['user_uuid'] && ($new_status == 2 || $new_status == 4)) {
        header("Location: users.php?msg=self_error");
        exit();
    }

    mysqli_begin_transaction($config);

    try {
        $extra_field = "";
        
        $res_susp = mysqli_query($config, "SELECT id_status FROM status WHERE nombre = 'Suspendido' LIMIT 1");
        $id_suspendido = mysqli_fetch_assoc($res_susp)['id_status'];

        $res_act = mysqli_query($config, "SELECT id_status FROM status WHERE nombre = 'Activo' LIMIT 1");
        $id_activo = mysqli_fetch_assoc($res_act)['id_status'];

        if ($new_status == $id_suspendido && $reason !== null) {
            $extra_field = ", suspension_reason = '$reason'";
        } elseif ($new_status == $id_activo) {
            $extra_field = ", suspension_reason = NULL";
        }

        $query_login = "UPDATE usr_users_login SET id_status = $new_status $extra_field WHERE user_uuid = '$uuid'";
        mysqli_query($config, $query_login);

        $query_users = "UPDATE usr_users SET id_status = $new_status WHERE uuid = '$uuid'";
        mysqli_query($config, $query_users);

        mysqli_commit($config);
        
        $loc = isset($_POST['from_profile']) ? "view_user.php?u=$uuid&msg=status_updated" : "users.php?msg=status_updated";
        header("Location: $loc");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($config);
        die("Error al cambiar el estatus: " . mysqli_error($config));
    }

} else {
    header("Location: users.php");
    exit();
}
?>