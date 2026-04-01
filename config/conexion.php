<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "travel_marketing";

$conexion = mysqli_connect($host,$user,$pass,$db);

if(!$conexion){
    die("Error de conexión");
}

?>