<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$uuid = $_SESSION['user_uuid'];

/* RESERVAS */
$sql = "SELECT r.*, c.nombre AS hotel, 
               u.first_name, u.last_name, 
               s.nombre AS estado_nombre
FROM res_reserva r
JOIN res_habitacion h ON r.id_habitacion = h.id_habitacion
JOIN catalogo c ON h.id_catalogo = c.id_catalogo
JOIN usr_users u ON r.user_uuid = u.uuid
JOIN status s ON r.id_status = s.id_status
WHERE c.propietario_uuid = '$uuid'";

$res = mysqli_query($config, $sql);

/* TOTAL DEL MES */
$total_mes_sql = "
SELECT COUNT(r.id_reserva) as total
FROM res_reserva r
JOIN res_habitacion h ON r.id_habitacion = h.id_habitacion
JOIN catalogo c ON h.id_catalogo = c.id_catalogo
WHERE c.propietario_uuid = '$uuid' 
AND MONTH(r.created_at) = MONTH(CURRENT_DATE())
AND YEAR(r.created_at) = YEAR(CURRENT_DATE())
";

$total_mes_res = mysqli_query($config, $total_mes_sql);
$total_mes = mysqli_fetch_assoc($total_mes_res)['total'] ?? 0;
/* GRAFICA */
$grafica_sql = "
SELECT DATE_FORMAT(r.created_at, '%Y-%m') as mes, COUNT(r.id_reserva) as total
FROM res_reserva r
JOIN res_habitacion h ON r.id_habitacion = h.id_habitacion
JOIN catalogo c ON h.id_catalogo = c.id_catalogo
WHERE c.propietario_uuid = '$uuid'
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
<title>Reservaciones</title>

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
    width: calc(100% - var(--sidebar-width));
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
            <a href="mis_reservaciones.php" class="nav-link active">
                <i class="bi bi-calendar-check me-2"></i> Reservaciones
            </a>
        </li>

        <li>
            <a href="mis_pagos.php" class="nav-link">
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
    <h5 class="mb-0">Reservaciones</h5>

    <button class="btn btn-light btn-sm"
    data-bs-toggle="modal"
    data-bs-target="#modalGrafica">
        📊 Ver gráfica
    </button>
</div>

<!-- KPI -->
<div class="d-flex justify-content-between align-items-center mb-3">

    <div class="p-3 bg-white shadow-sm rounded">
        <h6 class="mb-0">Reservaciones del mes</h6>
        <h3 class="text-primary mb-0"><?= $total_mes ?></h3>
    </div>

</div>

<!-- TABLA -->
<div class="card shadow-sm">

<div class="card-body table-responsive">

<table class="table table-hover">
<thead class="table-light">
<tr>
<th>Usuario</th>
<th>Hotel</th>
<th>Entrada</th>
<th>Salida</th>
<th>Estado</th>
</tr>
</thead>

<tbody>
<?php while($r=mysqli_fetch_assoc($res)): ?>
<tr>
<td><?= htmlspecialchars($r['first_name'] . " " . $r['last_name']) ?></td>
<td><?= htmlspecialchars($r['hotel']) ?></td>
<td><?= $r['fecha_entrada'] ?></td>
<td><?= $r['fecha_salida'] ?></td>
<td>
<span class="badge bg-primary">
<?= $r['estado_nombre'] ?>
</span>
</td>
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
    <h5 class="modal-title">📊 Reservaciones por mes</h5>
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
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            data: <?= json_encode($data) ?>,
            borderColor: '#4b62f4',
            tension: 0.3,
            fill: true
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