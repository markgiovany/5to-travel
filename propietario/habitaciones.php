<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$uuid = $_SESSION['user_uuid'];

/* filtros */
$estado = $_GET['estado'] ?? 'todos';
$buscar = $_GET['buscar'] ?? '';
$tipo = $_GET['tipo'] ?? '';

/* tipos */
$tipos = mysqli_query($config, "SELECT * FROM cat_tipo");

/*  consulta dinámica */
$sql = "SELECT 
    ch.id_habitacion,
    ch.nombre,
    c.nombre AS hotel,
    t.nombre AS tipo,
    ch.capacidad,
    ch.precio,
    s.nombre AS estado
FROM cat_catalogo_habitacion ch
INNER JOIN catalogo c ON c.id_catalogo = ch.id_catalogo
LEFT JOIN cat_tipo t ON t.id_tipo = ch.id_tipo
LEFT JOIN status s ON s.id_status = ch.id_status
WHERE c.propietario_uuid = '$uuid'
";

if ($estado != 'todos') {
    $sql .= " AND s.nombre = '$estado'";
}

if (!empty($buscar)) {
    $sql .= " AND (ch.nombre LIKE '%$buscar%' OR t.nombre LIKE '%$buscar%')";
}

if (!empty($tipo)) {
    $sql .= " AND ch.id_tipo = $tipo";
}

$sql .= " ORDER BY ch.id_habitacion DESC";

$res = mysqli_query($config, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Habitaciones</title>

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

.badge {
    border-radius: 20px;
    padding: 6px 10px;
    font-size: 12px;
}


.status-link {
    text-decoration: none;
    color: #6c757d;
    margin-left: 10px;
}

.status-link.active {
    font-weight: bold;
    color: black;
}

.search-box {
    display: flex;
}

.search-box input {
    border-radius: 20px 0 0 20px;
}

.search-box button {
    border-radius: 0 20px 20px 0;
}
</style>
</head>

<body>

<!-- sidebar -->
<div class="sidebar d-flex flex-column p-2">
    <div class="text-center py-3">
        <img src="../imagenes/brooking.png" width="160">
    </div>

    <ul class="nav nav-pills flex-column mb-auto">

    <li>
        <a href="propietario_dashboard.php" class="nav-link">
            <i class="bi bi-speedometer2 me-2"></i> Panel 
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

    <li>
        <a href="habitaciones.php" class="nav-link active">
            <i class="bi bi-door-open me-2"></i> Habitaciones
        </a>
    </li>

</ul>

    <hr>
    <a href="../auth/logout.php" class="btn btn-danger w-100">Cerrar Sesión</a>
</div>

<!-- contenido -->
<div class="main-content">

<div class="top-bar">
    <h5 class="mb-0">Panel de Habitaciones</h5>
</div>

<div class="card shadow-sm">

<div class="card-header bg-white">
    <h5 class="mb-0">Habitaciones</h5>

    <!-- estados -->
    <div class="mt-2">
        <strong>Estado:</strong>

        <a class="status-link <?= $estado=='todos'?'active':'' ?>" 
        href="?estado=todos&tipo=<?= $tipo ?>&buscar=<?= $buscar ?>">Todos</a>

        <a class="status-link <?= $estado=='Activo'?'active':'' ?>" 
        href="?estado=Activo&tipo=<?= $tipo ?>&buscar=<?= $buscar ?>">Activo</a>

        <a class="status-link <?= $estado=='Inactivo'?'active':'' ?>" 
        href="?estado=Inactivo&tipo=<?= $tipo ?>&buscar=<?= $buscar ?>">Inactivo</a>

        <a class="status-link <?= $estado=='Mantenimiento'?'active':'' ?>" 
        href="?estado=Mantenimiento&tipo=<?= $tipo ?>&buscar=<?= $buscar ?>">Mantenimiento</a>
    </div>
</div>

<div class="card-body">

<!-- filtros -->
<div class="d-flex justify-content-between mb-3">

<div class="d-flex gap-2">

    <!-- tipo -->
    <form method="GET">
        <input type="hidden" name="estado" value="<?= $estado ?>">
        <input type="hidden" name="buscar" value="<?= $buscar ?>">

        <select name="tipo" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Todos los tipos</option>

            <?php while($t = mysqli_fetch_assoc($tipos)): ?>
                <option value="<?= $t['id_tipo'] ?>" <?= $tipo == $t['id_tipo'] ? 'selected' : '' ?>>
                    <?= $t['nombre'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <!-- buscador -->
    <form method="GET" class="search-box">
        <input type="hidden" name="estado" value="<?= $estado ?>">
        <input type="hidden" name="tipo" value="<?= $tipo ?>">

        <input type="text" name="buscar" value="<?= $buscar ?>" 
        class="form-control form-control-sm" placeholder="Buscar nombre...">

        <button class="btn btn-dark btn-sm">
            <i class="bi bi-search"></i>
        </button>
    </form>

</div>

</div>

<!-- tabla -->
<table class="table table-hover align-middle">

<thead class="table-light">
<tr>
    <th>Habitación / Tipo</th>
    <th>Capacidad</th>
    <th>Estatus</th>
    <th>Precio</th>
    <th class="text-end">Acciones</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($res)): ?>

<tr>

<td>
    <strong><?= $row['tipo'] ?></strong><br>
    
</td>

<td><?= $row['capacidad'] ?> pers.</td>

<td>
    <span class="badge bg-success">
        <?= $row['estado'] ?? 'Activo' ?>
    </span>
</td>

<td>
    <span class="badge bg-success">
        $<?= number_format($row['precio'],2) ?>
    </span>
</td>

<td class="text-end">

<a href="editar_habitacion.php?id=<?= $row['id_habitacion'] ?>" 
class="btn btn-outline-primary btn-sm">
    <i class="bi bi-pencil"></i>
</a>

<a href="eliminar.php?id=<?= $row['id_habitacion'] ?>" 
class="btn btn-outline-danger btn-sm">
    <i class="bi bi-trash"></i>
</a>

</td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>
</div>

</div>

</body>
</html>