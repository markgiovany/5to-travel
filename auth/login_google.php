<?php

include("../config/database.php");

$data=json_decode(file_get_contents("php://input"));

$token=$data->token;

$payload=json_decode(file_get_contents(
"https://oauth2.googleapis.com/tokeninfo?id_token=".$token
));

$email=$payload->email;
$name=$payload->name;

$result=$conn->query("SELECT user_uuid FROM usr_emails WHERE email='$email'");

if($result->num_rows==0){

$uuid=uniqid();

$conn->query("INSERT INTO usr_users(uuid,first_name,last_name)
VALUES('$uuid','$name','')");

$conn->query("INSERT INTO usr_emails(email,user_uuid)
VALUES('$email','$uuid')");

$conn->query("INSERT INTO usr_users_login(user_uuid,status,role)
VALUES('$uuid','activo','cliente')");

}

echo "ok";

?>