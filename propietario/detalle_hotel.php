<?php
session_start();
include("../config/config.php");

if (!isset($_GET['id'])) {
    header("Location: mis_hoteles.php");
    exit();
}

$id = $_GET['id'];

// ACTUALIZAR
if ($_POST) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $update = "UPDATE catalogo 
               SET nombre='$nombre', descripcion='$descripcion' 
               WHERE id_catalogo='$id'";
    mysqli_query($config, $update);

    header("Location: mis_hoteles.php");
    exit();
}

// OBTENER DATOS
$sql = "SELECT * FROM catalogo WHERE id_catalogo='$id'";
$res = mysqli_query($config, $sql);
$hotel = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light p-4">

<div class="container bg-white shadow-sm p-4 rounded">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Detalle del Hotel</h3>
        <a href="mis_hoteles.php" class="btn btn-outline-secondary btn-sm">Volver</a>
    </div>

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?php echo $hotel['nombre']; ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="4"><?php echo $hotel['descripcion']; ?></textarea>
        </div>

        <button class="btn btn-primary">Guardar Cambios</button>

    </form>

</div>

</body>
</html>