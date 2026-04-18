<?php
$host = "216.246.46.215";
$user = "xjgkxxoh_UNIDtrabajo09";
$pass = "PP;7nwiBg8M4DYsg";
$db   = "xjgkxxoh_travel_maketing";

$config = mysqli_connect($host, $user, $pass, $db);

if(!$config){
    die("Error de conexión");
}
?>