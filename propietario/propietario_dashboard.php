<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$propietario_uuid = $_SESSION['user_uuid'];
$nombre_usuario = $_SESSION['first_name'] ?? "Usuario";

/* ELIMINAR HOTEL */
if (isset($_GET['delete'])) {

    $id_delete = (int)$_GET['delete'];

    mysqli_begin_transaction($config);

    try {

        mysqli_query($config, "DELETE FROM cat_imagen WHERE id_catalogo = $id_delete");
        mysqli_query($config, "DELETE FROM cat_catalogo_habitacion WHERE id_catalogo = $id_delete");
        mysqli_query($config, "DELETE FROM catalogo WHERE id_catalogo = $id_delete");

        mysqli_commit($config);

        header("Location: propietario_dashboard.php");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($config);
        die("Error eliminando hotel: " . $e->getMessage());
    }
}

/* HOTELES */
$sql_hoteles = "
SELECT * FROM catalogo 
WHERE propietario_uuid = '$propietario_uuid'
AND (id_status IS NULL OR id_status != 6)
ORDER BY id_catalogo DESC
";

$res_hoteles = mysqli_query($config, $sql_hoteles);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard Propietario</title>

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
}

.hotel-row {
    cursor: pointer;
}
</style>
</head>

<body>

<div class="sidebar d-flex flex-column p-2">
    <div class="text-center py-3">
        <img src="../imagenes/brooking.png" width="160">
    </div>

    <ul class="nav nav-pills flex-column mb-auto">
        <li>
            <a href="propietario_dashboard.php" class="nav-link active">
                <i class="bi bi-speedometer2 me-2"></i> Panel de Control
            </a>
        </li>

        <li>
            <a href="mis_reservaciones.php" class="nav-link">
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

<div class="main-content">

<div class="top-bar shadow-sm">
    <h5 class="mb-0">Panel de Control</h5>
    <span>Bienvenido, <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong></span>
</div>

<div class="card shadow-sm">

<div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Hoteles</h5>

    <div>
        <a href="agregar_hotel.php" class="btn btn-primary btn-sm">+ Hotel</a>
        <a href="agregar_habitacion.php" class="btn btn-success btn-sm">+ Habitación</a>
    </div>
</div>

<div class="card-body table-responsive">

<table class="table table-hover align-middle">

<thead class="table-light">
<tr>
    <th>Imagen</th>
    <th>Hotel</th>
    <th>Descripción</th>
    <th>Tipos</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($res_hoteles)): ?>

<?php
$id = $row['id_catalogo'];

/* IMÁGENES */
$res_img = mysqli_query($config, "
SELECT url_imagen 
FROM cat_imagen 
WHERE id_catalogo = '$id'
");

$imgs = [];
while($img = mysqli_fetch_assoc($res_img)){
    $imgs[] = $img['url_imagen'];
}

/* TIPOS */
$res_tipos = mysqli_query($config, "
SELECT DISTINCT t.nombre
FROM cat_catalogo_habitacion ch
INNER JOIN cat_tipo t ON t.id_tipo = ch.id_tipo
WHERE ch.id_catalogo = '$id'
");

$tipos = [];
while($t = mysqli_fetch_assoc($res_tipos)){
    $tipos[] = $t['nombre'];
}

/* 🔥 FIX: PRECIOS DESDE HABITACIONES */
$res_precio = mysqli_query($config, "
SELECT 
    MIN(precio) AS precio_min,
    MAX(precio) AS precio_max
FROM cat_catalogo_habitacion
WHERE id_catalogo = '$id'
");

$precio_data = mysqli_fetch_assoc($res_precio);

$precio_min = $precio_data['precio_min'] ?? 0;
$precio_max = $precio_data['precio_max'] ?? 0;
?>

<tr class="hotel-row"
data-bs-toggle="modal"
data-bs-target="#modal<?php echo $id; ?>">

<td>
<?php if(!empty($imgs)): ?>
    <img src="<?php echo $imgs[0]; ?>" width="65" height="65"
    style="object-fit:cover;border-radius:10px;">
<?php else: ?>
    <div style="width:65px;height:65px;background:#ddd;border-radius:10px;"></div>
<?php endif; ?>
</td>

<td class="fw-bold">
    <div class="d-flex justify-content-between align-items-center">
        <span><?php echo htmlspecialchars($row['nombre']); ?></span>

        <span class="badge bg-success ms-2">
            💰 $<?php echo number_format($precio_min,2); ?> - 
            $<?php echo number_format($precio_max,2); ?>
        </span>
    </div>
</td>

<td class="text-muted small">
<?php echo substr($row['descripcion'],0,70); ?>...
</td>

<td>
<?php if(empty($tipos)): ?>
    <span class="text-muted small">Sin habitaciones</span>
<?php else: ?>
    <?php foreach($tipos as $t): ?>
        <span class="badge bg-primary me-1 mb-1"><?php echo $t; ?></span>
    <?php endforeach; ?>
<?php endif; ?>
</td>

<td>
<a href="editar.php?id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm">
<i class="bi bi-pencil"></i>
</a>

<a href="propietario_dashboard.php?delete=<?php echo $id; ?>"
class="btn btn-outline-danger btn-sm"
onclick="return confirm('¿Eliminar hotel?')">
<i class="bi bi-trash"></i>
</a>
</td>

</tr>

<!-- MODAL -->
<div class="modal fade" id="modal<?php echo $id; ?>" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5><?php echo $row['nombre']; ?></h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<?php if(!empty($imgs)): ?>
<div id="carousel<?php echo $id; ?>" class="carousel slide mb-3"
data-bs-ride="carousel"
data-bs-interval="3000">

<div class="carousel-inner">

<?php foreach($imgs as $index => $img): ?>
<div class="carousel-item <?php echo $index == 0 ? 'active' : ''; ?>">
<img src="<?php echo $img; ?>"
style="width:100%;height:250px;object-fit:cover;border-radius:10px;">
</div>
<?php endforeach; ?>

</div>

<button class="carousel-control-prev" type="button"
data-bs-target="#carousel<?php echo $id; ?>" data-bs-slide="prev">
<span class="carousel-control-prev-icon"></span>
</button>

<button class="carousel-control-next" type="button"
data-bs-target="#carousel<?php echo $id; ?>" data-bs-slide="next">
<span class="carousel-control-next-icon"></span>
</button>

</div>
<?php endif; ?>

<p><?php echo $row['descripcion']; ?></p>

<hr>

<div class="mb-2">
<strong>💰 Rango de precios:</strong><br>

<span class="badge bg-success fs-6">
Min: $<?php echo number_format($precio_min, 2); ?>
</span>

<span class="badge bg-danger fs-6 ms-2">
Max: $<?php echo number_format($precio_max, 2); ?>
</span>

</div>

</div>

</div>
</div>
</div>

<?php endwhile; ?>

</tbody>
</table>

</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>