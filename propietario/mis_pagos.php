<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$uuid = $_SESSION['user_uuid'];

/* PAGOS */
$sql = "SELECT 
p.*, 
c.nombre,
s.nombre AS estado_nombre
FROM res_registro_pago p
JOIN res_reserva r ON p.id_reserva = r.id_reserva
JOIN cat_catalogo_habitacion h ON r.id_habitacion = h.id_habitacion
JOIN catalogo c ON h.id_catalogo = c.id_catalogo
LEFT JOIN status s ON p.id_status = s.id_status
WHERE c.propietario_uuid = '$uuid'";

$res = mysqli_query($config, $sql);

/* INGRESOS DEL MES */
$ingresos_mes_sql = "
SELECT SUM(monto) as total
FROM res_registro_pago
WHERE MONTH(fecha_pago) = MONTH(CURRENT_DATE())
AND YEAR(fecha_pago) = YEAR(CURRENT_DATE())
AND id_status = 5
";

$ingresos_mes_res = mysqli_query($config, $ingresos_mes_sql);
$ingresos_mes = mysqli_fetch_assoc($ingresos_mes_res)['total'] ?? 0;

/* GRAFICA */
$grafica_sql = "
SELECT DATE_FORMAT(fecha_pago, '%Y-%m') as mes, SUM(monto) as total
FROM res_registro_pago
WHERE id_status = 5
GROUP BY mes
ORDER BY mes DESC
LIMIT 6
";

$grafica_res = mysqli_query($config, $grafica_sql);

$labels = [];
$data = [];

while($g = mysqli_fetch_assoc($grafica_res)){
    $labels[] = $g['mes'];
    $data[] = $g['total'];
}

$labels = array_reverse($labels);
$data = array_reverse($data);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pagos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
:root {
    --blue-dark: #4b62f4;
    --sidebar-width: 250px;
}

body {
    background-color: #f4f6f9;
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: var(--sidebar-width);
    background: white;
    border-right: 1px solid #dee2e6;
    position: fixed;
    height: 100%;
}

.sidebar .nav-link {
    color: #333;
    padding: 12px 20px;
    font-weight: 500;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    background: #e9ecef;
    color: var(--blue-dark);
    border-left: 4px solid var(--blue-dark);
}

.main-content {
    margin-left: var(--sidebar-width);
    flex-grow: 1;
    padding: 20px;
    width: 100%;
}

.top-bar {
    background: var(--blue-dark);
    color: white;
    margin: -20px -20px 20px -20px;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar d-flex flex-column p-2">

    <div class="text-center py-3">
        <img src="../imagenes/brooking.png" width="160">
    </div>

    <ul class="nav nav-pills flex-column mb-auto">

        <li>
            <a href="propietario_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2 me-2"></i> Panel de control
            </a>
        </li>

        <li>
            <a href="mis_reservaciones.php" class="nav-link">
                <i class="bi bi-calendar-check me-2"></i> Reservaciones
            </a>
        </li>

        <li>
            <a href="mis_pagos.php" class="nav-link active">
                <i class="bi bi-cash-coin me-2"></i> Pagos
            </a>
        </li>

    </ul>

    <hr>

    <a href="../auth/logout.php" class="btn btn-danger w-100">
        Cerrar Sesión
    </a>

</div>

<!-- MAIN -->
<div class="main-content">

<div class="top-bar shadow-sm">

    <h5 class="mb-0">Gestión de Pagos</h5>

    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalGrafica">
        📊 Ver gráfica
    </button>

</div>

<!-- INGRESOS -->
<div class="p-3 bg-white shadow-sm rounded mb-3">
    <h5 class="mb-0">Ingresos del mes</h5>
    <h2 class="text-success">$<?= number_format($ingresos_mes,2) ?></h2>
</div>

<!-- TABLA PAGOS -->
<div class="card shadow-sm">
<div class="card-body table-responsive">

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
<td>
<span class="badge bg-primary">
<?= $row['estado_nombre'] ?? 'Sin estado' ?>
</span>
</td>
<td><?= $row['fecha_pago'] ?></td>
</tr>
<?php endwhile; ?>
</tbody>

</table>

</div>
</div>

</div>

<!-- MODAL GRAFICA -->
<div class="modal fade" id="modalGrafica" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">📊 Ingresos últimos meses</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <canvas id="grafica"></canvas>
</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('grafica'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            data: <?= json_encode($data) ?>,
            backgroundColor: '#4b62f4'
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        responsive: true
    }
});
</script>

</body>
</html>