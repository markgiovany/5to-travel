<?php
session_start();
include("../config/config.php");

// 1. Verificación de sesión
if (!isset($_SESSION['user_uuid'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_uuid'];

// 2. Validación de datos recibidos
if (!isset($_POST['titular']) || !isset($_POST['numero'])) {
    header("Location: ../perfil.php?error=datos");
    exit();
}

$titular = mysqli_real_escape_string($config, $_POST['titular']);
// Limpieza extra por si el JS falla
$numero  = preg_replace('/[^0-9]/', '', $_POST['numero']);

// 3. Verificación de longitud estricta
if (strlen($numero) !== 16) {
    header("Location: ../perfil.php?error=tarjeta");
    exit();
}

// 4. Inserción segura
$query = "INSERT INTO usr_billetera (user_uuid, id_metodo_pago, nombre_titular, datos_encriptados, id_status) 
          VALUES ('$user_id', 1, '$titular', '$numero', 1)";

if (mysqli_query($config, $query)) {
    // Redirección con éxito
    header("Location: ../perfil.php?pago=success");
} else {
    // Error de Base de Datos
    header("Location: ../perfil.php?error=db");
}
exit();
?>