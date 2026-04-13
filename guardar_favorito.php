<?php
session_start();
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    if (!isset($_SESSION['favoritos'])) {
        $_SESSION['favoritos'] = [];
    }

    if (($key = array_search($id, $_SESSION['favoritos'])) !== false) {
        unset($_SESSION['favoritos'][$key]);
        echo "removido";
    } else {
        $_SESSION['favoritos'][] = $id;
        echo "agregado";
    }
}