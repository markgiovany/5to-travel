<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$uuid = $_SESSION['user_uuid'];

$sql = "SELECT h.*, c.nombre AS hotel
FROM res_habitacion h
JOIN catalogo c ON h.id_catalogo = c.id_catalogo
WHERE c.propietario_uuid = '$uuid'";

$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Habitaciones</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light p-4">

<div class="container bg-white shadow-sm p-4 rounded">

<div class="d-flex justify-content-between mb-4">
    <h4>Mis Habitaciones</h4>

    <a href="propietario_dashboard.php" class="btn btn-outline-secondary btn-sm">
        Volver al Panel
    </a>
</div>

<table class="table table-hover">
<thead class="table-light">
<tr>
<th>Hotel</th>
<th>Número</th>
<th>Precio</th>
<th>Estado</th>
</tr>
</thead>

<tbody>
<?php while($row=mysqli_fetch_assoc($res)): ?>
<tr>
<td><strong><?= $row['hotel'] ?></strong></td>
<td>#<?= $row['numero'] ?></td>
<td class="text-success fw-bold">$<?= $row['precio'] ?></td>
<td><span class="badge bg-success"><?= $row['estado'] ?></span></td>
</tr>
<?php endwhile; ?>
</tbody>

</table>

</div>

</body>
</html>