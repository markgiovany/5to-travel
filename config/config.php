<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "travel_marketing";

$config = mysqli_connect($host,$user,$pass,$db);

if(!$config){
    die("Error de conexión");
}

?>