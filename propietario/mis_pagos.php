<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$uuid = $_SESSION['user_uuid'];

$sql = "SELECT p.*, c.nombre
FROM res_registro_pago p
JOIN res_reserva r ON p.id_reserva = r.id_reserva
JOIN res_habitacion h ON r.id_habitacion = h.id_habitacion
JOIN catalogo c ON h.id_catalogo = c.id_catalogo
WHERE c.propietario_uuid = '$uuid'";

$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pagos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light p-4">

<div class="container bg-white p-4 shadow-sm rounded">

<div class="d-flex justify-content-between mb-4">
    <h4>Gestión de Pagos</h4>

    <a href="propietario_dashboard.php" class="btn btn-outline-secondary btn-sm">
        Volver al Panel
    </a>
</div>

<table class="table table-hover">
<thead class="table-light">
<tr>
<th>Hotel</th>
<th>Monto</th>
<th>Método</th>
<th>Estado</th>
<th>Fecha</th>
</tr>
</thead>

<tbody>
<?php while($row=mysqli_fetch_assoc($res)): ?>
<tr>
<td><?= $row['nombre'] ?></td>
<td class="text-success fw-bold">$<?= $row['monto'] ?></td>
<td><?= $row['id_metodo_pago'] ?></td>
<td><span class="badge bg-success"><?= $row['estado_pago'] ?></span></td>
<td><?= $row['fecha_pago'] ?></td>
</tr>
<?php endwhile; ?>
</tbody>

</table>

</div>

</body>
</html>