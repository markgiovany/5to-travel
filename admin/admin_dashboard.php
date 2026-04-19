<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$res_users = mysqli_query($config, "SELECT COUNT(*) as total FROM usr_users_login WHERE id_status != 2");
$total_users = mysqli_fetch_assoc($res_users)['total'];

$res_props = mysqli_query($config, "SELECT COUNT(*) as total FROM catalogo WHERE id_status != 2");
$total_props = mysqli_fetch_assoc($res_props)['total'];

$res_count_reservas = mysqli_query($config, "SELECT COUNT(*) as total FROM res_reserva WHERE id_status != 7");
$total_reservas = mysqli_fetch_assoc($res_count_reservas)['total'];

$query_actividad = "SELECT r.id_reserva, r.fecha_entrada, s.nombre as estado_nombre, u.first_name, u.last_name, c.nombre as hotel_nombre 
                    FROM res_reserva r
                    INNER JOIN usr_users u ON r.user_uuid = u.uuid
                    INNER JOIN cat_catalogo_habitacion ch ON r.id_habitacion = ch.id_habitacion
                    INNER JOIN catalogo c ON ch.id_catalogo = c.id_catalogo
                    INNER JOIN status s ON r.id_status = s.id_status
                    ORDER BY r.id_reserva DESC LIMIT 10";
$res_actividad = mysqli_query($config, $query_actividad);
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
            <li><a href="admin_dashboard.php" class="nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="bi bi-people"></i> Usuarios</a></li>
            <li><a href="hoteles.php" class="nav-link"><i class="bi bi-building"></i> Hoteles</a></li>
            <li><a href="reservaciones.php" class="nav-link"><i class="bi bi-calendar-check"></i> Reservaciones</a></li>
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
                    <strong><?php echo $_SESSION['first_name'] ?? 'Admin'; ?></strong>
                </span>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['first_name'] ?? 'Admin'; ?>&background=6f42c1&color=fff" class="rounded-circle" width="40">
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card p-4">
                    <span class="text-muted small uppercase fw-bold">Usuarios Activos</span>
                    <h3 class="mb-0 mt-2"><?php echo number_format($total_users); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-4">
                    <span class="text-muted small uppercase fw-bold">Propiedades Activas</span>
                    <h3 class="mb-0 mt-2"><?php echo number_format($total_props); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-4">
                    <span class="text-muted small uppercase fw-bold">Reservaciones</span>
                    <h3 class="mb-0 mt-2"><?php echo number_format($total_reservas); ?></h3> 
                </div>
            </div>
        </div>

        <div class="card stat-card shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Actividad Reciente</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Hotel</th>
                                <th>Usuario</th>
                                <th>Fecha Entrada</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($reserva = mysqli_fetch_assoc($res_actividad)): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?php echo $reserva['hotel_nombre']; ?></td>
                                <td><?php echo $reserva['first_name'] . " " . $reserva['last_name']; ?></td>
                                <td><?php echo date('d M Y', strtotime($reserva['fecha_entrada'])); ?></td>
                                <td>
                                    <?php 
                                        // Asignación de colores basada en el nombre del estatus (Lógica PHP)
                                        $clase_bg = 'bg-secondary';
                                        $nombre_est = $reserva['estado_nombre'];

                                        if($nombre_est == 'Activo' || $nombre_est == 'Confirmado') $clase_bg = 'bg-success';
                                        elseif($nombre_est == 'Pendiente' || $nombre_est == 'Revision') $clase_bg = 'bg-warning text-dark';
                                        elseif($nombre_est == 'Cancelado' || $nombre_est == 'Inactivo') $clase_bg = 'bg-danger';
                                        elseif($nombre_est == 'Baneado') $clase_bg = 'bg-dark';
                                    ?>
                                    <span class="badge <?php echo $clase_bg; ?> rounded-pill px-3">
                                        <?php echo $nombre_est; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>