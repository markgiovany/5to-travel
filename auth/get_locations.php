<?php
include("../config/config.php");

// Si pedimos estados de un país
if (isset($_POST['pais_id'])) {
    $id = intval($_POST['pais_id']);
    $query = "SELECT id, name FROM states WHERE country_id = $id ORDER BY name ASC";
    $res = mysqli_query($config, $query);
    
    echo '<option value="">Selecciona un estado</option>';
    while($row = mysqli_fetch_assoc($res)) {
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
    }
}

// Si pedimos ciudades de un estado
if (isset($_POST['estado_id'])) {
    $id = intval($_POST['estado_id']);
    $query = "SELECT id, name FROM cities WHERE state_id = $id ORDER BY name ASC";
    $res = mysqli_query($config, $query);
    
    echo '<option value="">Selecciona una ciudad</option>';
    while($row = mysqli_fetch_assoc($res)) {
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
    }
}
?>