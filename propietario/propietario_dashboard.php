<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$propietario_uuid = $_SESSION['user_uuid'];
$nombre_usuario = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : "Esteban";


$sql_conteo = "SELECT COUNT(*) as total FROM catalogo WHERE propietario_uuid = '$propietario_uuid'";
$res_conteo = mysqli_query($conexion, $sql_conteo);
$total_hoteles = mysqli_fetch_assoc($res_conteo)['total'];

$query_res = "SELECT COUNT(*) as total FROM res_reserva"; 
$ejecutar_res = mysqli_query($conexion, $query_res);
$total_reservas = ($ejecutar_res) ? mysqli_fetch_assoc($ejecutar_res)['total'] : 0;

$sql_hoteles = "SELECT * FROM catalogo WHERE propietario_uuid = '$propietario_uuid' ORDER BY id_catalogo DESC";
$res_hoteles = mysqli_query($conexion, $sql_hoteles);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Propietario - BookingEngineer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --blue-dark: #4b62f4; --sidebar-width: 250px; }
        body { background-color: #f4f6f9; display: flex; min-height: 100vh; overflow-x: hidden; }
        .sidebar { width: var(--sidebar-width); background: white; border-right: 1px solid #dee2e6; position: fixed; height: 100%; z-index: 1000; }
        .sidebar .nav-link { color: #333; padding: 12px 20px; font-weight: 500; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #e9ecef; color: var(--blue-dark); border-left: 4px solid var(--blue-dark); }
        .main-content { margin-left: var(--sidebar-width); flex-grow: 1; padding: 20px; width: 100%; }
        .top-bar { background: var(--blue-dark); color: white; margin: -20px -20px 20px -20px; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .card-stats { border: none; border-radius: 10px; background: white; }
        .icon-box { width: 50px; height: 50px; background: #e7f0ff; color: var(--blue-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .icon-box.green { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>

    <div class="sidebar d-flex flex-column p-2">
        <div class="text-center py-3">
            <img src="../imagenes/brooking.png" alt="Logo" width="160">
        </div>
        <ul class="nav nav-pills flex-column mb-auto">
            <li><a href="propietario_dashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li><a href="mis_hoteles.php" class="nav-link"><i class="bi bi-airplane me-2"></i> Mis Hoteles</a></li>
            <li><a href="mis_reservaciones.php" class="nav-link"><i class="bi bi-calendar-check me-2"></i> Reservaciones</a></li>
            <li><a href="mis_habitaciones.php" class="nav-link"><i class="bi bi-door-open me-2"></i> Habitaciones</a></li>
            <a href="mis_pagos.php" class="nav-link">
    <i class="bi bi-cash-coin me-2"></i> Pagos
</a>
        </ul>
        <hr>
        <div class="p-2">
            <a href="../auth/logout.php" class="btn btn-danger w-100"><i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión</a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-bar shadow-sm">
            <h5 class="mb-0">Panel de Control</h5>
            <span>Bienvenido, <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong></span>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-stats shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box me-3"><i class="bi bi-building"></i></div>
                        <div>
                            <p class="text-muted mb-0">Total Propiedades</p>
                            <h3 class="fw-bold mb-0"><?php echo $total_hoteles; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-box green me-3"><i class="bi bi-calendar-check"></i></div>
                        <div>
                            <p class="text-muted mb-0">Nuevas Reservas</p>
                            <h3 class="fw-bold mb-0"><?php echo $total_reservas; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>  

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Listado de Hoteles</h5>
                <a href="agregar_hotel.php" class="btn btn-primary btn-sm rounded-pill px-3">+ Agregar Nuevo Hotel</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th>Nombre del Hotel</th>
                                <th>Descripción</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($res_hoteles) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($res_hoteles)): ?>
                                <tr>
                                    <td>#<?php echo $row['id_catalogo']; ?></td>
                                    <td><img src="https://via.placeholder.com/50" class="rounded" width="50" height="50" style="object-fit: cover;"></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($row['nombre']); ?></td>
                                    <td class="text-muted small"><?php echo htmlspecialchars(substr($row['descripcion'], 0, 80)); ?>...</td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="editar.php?id=<?php echo $row['id_catalogo']; ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                            <a href="eliminar.php?id=<?php echo $row['id_catalogo']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No tienes hoteles registrados todavía.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>