<?php

$host="localhost";
$user="root";
$password="";
$db="travel_marketing";

$conn=new mysqli($host,$user,$password,$db);

if($conn->connect_error){
 die("Error DB ".$conn->connect_error);
}

?>