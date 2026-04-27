<?php
include("config/config.php");

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);
$data = [];

if ($type === 'state' && $id > 0) {
    // esto segun me busca los estados por pais
    $res = mysqli_query($config, "SELECT id, name FROM states WHERE country_id = $id ORDER BY name ASC");
    while($row = mysqli_fetch_assoc($res)) { $data[] = $row; }
} elseif ($type === 'city' && $id > 0) {
    // y esto busca ciudades por estado
    $res = mysqli_query($config, "SELECT id, name FROM cities WHERE state_id = $id ORDER BY name ASC");
    while($row = mysqli_fetch_assoc($res)) { $data[] = $row; }
}

header('Content-Type: application/json');
echo json_encode($data);
?>