<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$uuid = $_GET['uuid'] ?? '';

if (!$uuid) {
    die("Error: habitación no válida");
}

$user_uuid = $_SESSION['user_uuid'];

/* validar que sea del usuario */
$check = mysqli_query($config, "
    SELECT ch.uuid 
    FROM cat_catalogo_habitacion ch
    INNER JOIN catalogo c ON c.id_catalogo = ch.id_catalogo
    WHERE ch.uuid = '$uuid'
    AND c.propietario_uuid = '$user_uuid'
");

if (mysqli_num_rows($check) == 0) {
    die("No tienes permiso");
}

/* desactivar */
mysqli_query($config, "
    UPDATE cat_catalogo_habitacion
    SET id_status = 2
    WHERE uuid = '$uuid'
");

header("Location: habitaciones.php");
exit();