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
        $_SESSION['flash'] = [
            'type'  => 'danger',
            'title' => 'Error de seguridad',
            'msg'   => 'No puedes desactivar o suspender tu propia cuenta.'
        ];
        header("Location: users.php");
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
        
        $tipo_alerta = ($new_status == $id_activo) ? 'success' : 'warning';
        $res_nombre_status = mysqli_query($config, "SELECT nombre FROM status WHERE id_status = $new_status");
        $nombre_status = mysqli_fetch_assoc($res_nombre_status)['nombre'];

        $_SESSION['flash'] = [
            'type'  => $tipo_alerta,
            'title' => 'Datos Actualizados ',
            'msg'   => " Se ha cambiado el estatus a: $nombre_status."
        ];

        $loc = isset($_POST['from_profile']) ? "view_user.php?u=$uuid" : "users.php";
        header("Location: $loc");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($config);
        $_SESSION['flash'] = [
            'type'  => 'danger',
            'title' => 'Error Crítico',
            'msg'   => 'No se pudo cambiar el estatus: ' . mysqli_error($config)
        ];
        header("Location: users.php");
        exit();
    }

} else {
    header("Location: users.php");
    exit();
}
?>