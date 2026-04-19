<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$propietario_uuid = $_SESSION['user_uuid'];
$nombre_usuario = $_SESSION['first_name'] ?? "Esteban";

/* ELIMINAR HOTEL (SAFE) */
if (isset($_GET['delete'])) {

    $id_delete = (int)$_GET['delete'];

    mysqli_begin_transaction($config);

    try {

        mysqli_query($config, "DELETE FROM cat_imagen WHERE id_catalogo = $id_delete");
        mysqli_query($config, "DELETE FROM cat_catalogo_habitacion WHERE id_catalogo = $id_delete");
        mysqli_query($config, "DELETE FROM res_habitacion WHERE id_catalogo = $id_delete");
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
SELECT c.*
FROM catalogo c
WHERE c.propietario_uuid = '$propietario_uuid'
AND (c.id_status IS NULL OR c.id_status != 6)
ORDER BY c.id_catalogo DESC
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
:root { --blue-dark: #4b62f4; --sidebar-width: 250px; }

body {
    background-color: #f4f6f9;
    display: flex;
    min-height: 100vh;
    overflow-x: hidden;
}

.sidebar {
    width: var(--sidebar-width);
    background: white;
    border-right: 1px solid #dee2e6;
    position: fixed;
    height: 100%;
    z-index: 1000;
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

.card-custom {
    border: none;
    border-radius: 12px;
    overflow: hidden;
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
        <li><a href="propietario_dashboard.php" class="nav-link active">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a></li>

        <li><a href="mis_reservaciones.php" class="nav-link">
            <i class="bi bi-calendar-check me-2"></i> Reservaciones
        </a></li>

        <li><a href="mis_pagos.php" class="nav-link">
            <i class="bi bi-cash-coin me-2"></i> Pagos
        </a></li>
    </ul>

    <hr>

    <div class="p-2">
        <a href="../auth/logout.php" class="btn btn-danger w-100">
            <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
        </a>
    </div>
</div>

<!-- MAIN -->
<div class="main-content">

<div class="top-bar shadow-sm">
    <h5 class="mb-0">Panel de Control</h5>
    <span>Bienvenido, <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong></span>
</div>

<div class="card shadow-sm card-custom">

<div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0 fw-bold">Hoteles</h5>

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
    <th>Tipos de habitaciones</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($res_hoteles)): ?>

<?php
$id = $row['id_catalogo'];

/* IMAGEN */
$res_img = mysqli_query($config, "
SELECT url_imagen 
FROM cat_imagen 
WHERE id_catalogo = '$id'
LIMIT 1
");

$img = mysqli_fetch_assoc($res_img)['url_imagen'] ?? null;

/* TIPOS DE HABITACIÓN */
$res_tipos = mysqli_query($config, "
SELECT t.nombre
FROM cat_catalogo_habitacion ch
INNER JOIN cat_tipo t ON t.id_tipo = ch.id_tipo
WHERE ch.id_catalogo = '$id'
");
?>

<tr>

<!-- IMAGEN -->
<td>
<?php if($img): ?>
    <img src="/5to-travel/<?php echo $img; ?>"
         width="55"
         height="55"
         style="object-fit:cover;border-radius:6px;">
<?php else: ?>
    <div style="width:55px;height:55px;background:#ccc;border-radius:6px;"></div>
<?php endif; ?>
</td>

<!-- HOTEL -->
<td class="fw-bold"><?php echo htmlspecialchars($row['nombre']); ?></td>

<!-- DESCRIPCIÓN -->
<td class="text-muted small">
<?php echo htmlspecialchars(substr($row['descripcion'],0,80)); ?>...
</td>

<!-- TIPOS -->
<td>
<?php while($t = mysqli_fetch_assoc($res_tipos)): ?>
    <span class="badge bg-primary me-1 mb-1">
        <?php echo $t['nombre']; ?>
    </span>
<?php endwhile; ?>
</td>

<!-- ACCIONES -->
<td>
    <div class="btn-group">

        <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil"></i>
        </a>

        <a href="propietario_dashboard.php?delete=<?php echo $id; ?>" 
           class="btn btn-outline-danger btn-sm"
           onclick="return confirm('¿Seguro que deseas eliminar este hotel?')">
            <i class="bi bi-trash"></i>
        </a>

    </div>
</td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>