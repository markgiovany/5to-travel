<?php
session_start();
include("../config/conexion.php");

$uuid = $_SESSION['user_uuid'];

$hoteles = mysqli_query($conexion,"SELECT * FROM catalogo WHERE propietario_uuid='$uuid'");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_catalogo = $_POST['id_catalogo'];
    $numero = $_POST['numero'];
    $precio = $_POST['precio'];
    $estado = $_POST['estado'];

    mysqli_query($conexion,"INSERT INTO res_habitacion 
    (id_catalogo, numero, precio, estado)
    VALUES ('$id_catalogo','$numero','$precio','$estado')");

    header("Location: mis_habitaciones.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Agregar Habitación</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light p-5">

<div class="container">
<h4>Agregar Habitación</h4>

<form method="POST">

<div class="mb-3">
<label>Hotel</label>
<select name="id_catalogo" class="form-control">
<?php while($h=mysqli_fetch_assoc($hoteles)): ?>
<option value="<?= $h['id_catalogo'] ?>"><?= $h['nombre'] ?></option>
<?php endwhile; ?>
</select>
</div>

<div class="mb-3">
<label>Número</label>
<input name="numero" class="form-control" required>
</div>

<div class="mb-3">
<label>Precio</label>
<input name="precio" class="form-control" required>
</div>

<div class="mb-3">
<label>Estado</label>
<select name="estado" class="form-control">
<option>Disponible</option>
<option>No disponible</option>
</select>
</div>

<button class="btn btn-primary">Guardar</button>
<a href="mis_habitaciones.php" class="btn btn-secondary">Cancelar</a>

</form>
</div>

</body>
</html>