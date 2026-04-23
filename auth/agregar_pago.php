<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid'])) { 
    header("Location: ../index.php");
    exit(); 
}

$user_id = $_SESSION['user_uuid'];

// Aquí ya no usamos "tipo", usamos el nombre del formulario
$id_metodo = isset($_POST['id_metodo_pago']) ? (int)$_POST['id_metodo_pago'] : 1; 
$titular   = mysqli_real_escape_string($config, $_POST['titular']);
$numero    = mysqli_real_escape_string($config, preg_replace('/[^0-9]/', '', $_POST['numero']));

// Insertamos en la tabla real: usr_billetera
$query = "INSERT INTO usr_billetera (user_uuid, id_metodo_pago, nombre_titular, datos_encriptados, id_status)
          VALUES ('$user_id', '$id_metodo', '$titular', '$numero', 1)";

if (mysqli_query($config, $query)) {
    header("Location: ../perfil.php?pago=success");
} else {
    die("Error en la base de datos: " . mysqli_error($config));
}
?>