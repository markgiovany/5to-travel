<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Usuarios totales
$res_users = mysqli_query($conexion, "SELECT COUNT(*) as total FROM usr_users");
$total_users = mysqli_fetch_assoc($res_users)['total'];

// Propiedades (hoteles) totales
$res_props = mysqli_query($conexion, "SELECT COUNT(*) as total FROM catalogo");
$total_props = mysqli_fetch_assoc($res_props)['total'];

// Actividad reciente (últimos usuarios registrados)
$query_actividad = "SELECT u.first_name, u.last_name, e.email, u.created_at 
                   FROM usr_users u
                   INNER JOIN usr_emails e ON u.uuid = e.user_uuid 
                   ORDER BY u.created_at DESC";
$res_actividad = mysqli_query($conexion, $query_actividad);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brooking | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
</head>
<body>

<div class="d-flex">
    <div class="sidebar d-flex flex-column shadow-sm">
        <div class="p-4 text-center">
            <img src="../imagenes/brooking.png" alt="Logo" width="140">
        </div>
        
        <ul class="nav flex-column mb-auto">
            <li><a href="#" class="nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="bi bi-people"></i> Usuarios</a></li>
            <li><a href="hoteles.php" class="nav-link"><i class="bi bi-building"></i> Hoteles</a></li>
            <li><a href="reservas.php" class="nav-link"><i class="bi bi-calendar-check"></i> Reservas</a></li>
        </ul>

        <div class="p-3 border-top">
            <a href="../auth/logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
            </a>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Overview</h2>
            <div class="d-flex align-items-center">
        <span class="me-3 text-muted">Bienvenido,   
            <strong><?php echo $_SESSION['first_name'] ?? 'Admin'; ?>
        </strong>
        </span>
            <img src="https://ui-avatars.com/api/?name=<?php 
            echo $_SESSION['first_name'] ?? 'Admin'; ?>&background=6f42c1&color=fff" class="rounded-circle" width="40">
        </div>
    </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card p-4">
                    <span class="text-muted small uppercase fw-bold">Usuarios Totales</span>
                    <h3 class="mb-0 mt-2"><?php echo number_format($total_users); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-4">
                    <span class="text-muted small uppercase fw-bold">Propiedades</span>
                    <h3 class="mb-0 mt-2"><?php echo number_format($total_props); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-4">
                    <span class="text-muted small uppercase fw-bold">Nuevas Reservas</span>
                    <h3 class="mb-0 mt-2">0</h3> </div>
            </div>
        </div>

        <div class="card stat-card shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Actividad Reciente</h5>
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = mysqli_fetch_assoc($res_actividad)): ?>
                        <tr>
                            <td><?php echo $user['first_name'] . " " . $user['last_name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td><span class="badge bg-success">Activo</span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>