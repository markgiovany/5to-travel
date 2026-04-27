<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$uuid = $_GET['uuid'] ?? '';
$user_uuid = $_SESSION['user_uuid'];

if (!$id) {
    die("Imagen no válida");
}

/* validar que la imagen pertenece al usuario */
$check = mysqli_query($config, "
    SELECT ci.id_imagen
    FROM cat_imagen ci
    INNER JOIN cat_catalogo_habitacion ch ON ch.id_habitacion = ci.id_habitacion
    INNER JOIN catalogo c ON c.id_catalogo = ch.id_catalogo
    WHERE ci.id_imagen = '$id'
    AND c.propietario_uuid = '$user_uuid'
");

if (mysqli_num_rows($check) == 0) {
    die("No tienes permiso");
}

/* eliminar */
mysqli_query($config, "DELETE FROM cat_imagen WHERE id_imagen = '$id'");

header("Location: editar_habitacion.php?uuid=$uuid");
exit();