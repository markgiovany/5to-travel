<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$search_val = isset($_GET['search']) ? mysqli_real_escape_string($config, $_GET['search']) : '';
$status_val = isset($_GET['status']) ? mysqli_real_escape_string($config, $_GET['status']) : '';

$condiciones = [];
if (!empty($status_val)) {
    $condiciones[] = "r.id_status = '$status_val'";
} else {
    $condiciones[] = "r.id_status IN (SELECT id_status FROM status WHERE nombre IN ('Revision', 'Confirmado', 'Cancelado'))";
}

if (!empty($search_val)) {
    $condiciones[] = "(c.nombre LIKE '%$search_val%' OR u.first_name LIKE '%$search_val%')";
}

$filtro_sql = " WHERE " . implode(" AND ", $condiciones);

$query = "SELECT r.uuid_reserva,r.id_reserva, r.created_at, r.fecha_entrada, s.nombre as estado_nombre, r.id_status,
          u.first_name, u.last_name, e.email,
          c.nombre as hotel_nombre,
          h.nombre as habitacion_nombre, h.precio,
          p.monto as monto_pagado
          FROM res_reserva r
          INNER JOIN usr_users u ON r.user_uuid = u.uuid
          LEFT JOIN usr_emails e ON u.uuid = e.user_uuid
          INNER JOIN catalogo c ON r.id_catalogo = c.id_catalogo
          INNER JOIN cat_catalogo_habitacion h ON r.id_habitacion = h.id_habitacion
          INNER JOIN status s ON r.id_status = s.id_status
          LEFT JOIN res_registro_pago p ON r.id_reserva = p.id_reserva
          $filtro_sql
          ORDER BY r.id_reserva DESC";

$resultado = mysqli_query($config, $query);
$res_status_list = mysqli_query($config, "SELECT * FROM status WHERE id_status IN (8, 5, 6) ORDER BY FIELD(id_status, 8, 5, 6)");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Brooking | Control de Reservaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
    <style>
        .dot { font-size: 0.6rem; vertical-align: middle; }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar d-flex flex-column shadow-sm">
        <div class="p-4 text-center"><img src="../imagenes/brooking.png" alt="Logo" width="140"></div>
        <ul class="nav flex-column mb-auto">
            <li><a href="admin_dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="bi bi-people"></i> Usuarios</a></li>
            <li><a href="hoteles.php" class="nav-link"><i class="bi bi-building"></i> Hoteles</a></li>
            <li><a href="reservaciones.php" class="nav-link active"><i class="bi bi-calendar-check"></i> Reservaciones</a></li>
        </ul>
        <div class="p-3 border-top">
            <a href="../auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold m-0">Control de Reservaciones</h2>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Admin <strong><?php echo $_SESSION['first_name'] ?? 'Admin'; ?></strong></span>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['first_name'] ?? 'Admin'; ?>&background=6f42c1&color=fff" class="rounded-circle" width="40">
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <small class="text-muted fw-bold me-2">ESTADO:</small>
                <a href="reservaciones.php" class="btn btn-xs <?php echo empty($status_val) ? 'fw-bold text-dark' : 'text-muted'; ?>" style="font-size: 0.8rem;">Todas</a>
                <?php mysqli_data_seek($res_status_list, 0); while($s = mysqli_fetch_assoc($res_status_list)): ?>
                    <a href="reservaciones.php?status=<?php echo $s['id_status']; ?>" 
                       class="ms-2 btn btn-xs <?php echo ($status_val == $s['id_status']) ? 'fw-bold text-dark text-decoration-underline' : 'text-muted'; ?>" 
                       style="font-size: 0.8rem;">
                        <?php echo $s['nombre']; ?>
                    </a>
                <?php endwhile; ?>
            </div>
            
            <form method="GET" class="input-group input-group-sm" style="width: 300px;">
                <input type="text" name="search" class="form-control rounded-start-pill" placeholder="Buscar por hotel o cliente..." value="<?php echo htmlspecialchars($search_val); ?>">
                <button class="btn btn-dark rounded-end-pill px-3" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4"> 
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ref.</th>
                                <th>Entrada</th>
                                <th>Cliente</th>
                                <th>Hotel / Habitación</th>
                                <th class="text-center">Estatus</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php if(mysqli_num_rows($resultado) > 0): ?>
        <?php while($res = mysqli_fetch_assoc($resultado)): ?>
        <tr class="align-middle">
            
            <td>
                <div class="fw-bold text-dark">BK-<?php echo str_pad($res['id_reserva'], 7, "0", STR_PAD_LEFT); ?></div>
                <div class="text-muted" style="font-size: 0.7rem;"><?php echo date('d/m/Y H:i', strtotime($res['created_at'])); ?></div>
            </td>

            <td>
                <span class="badge bg-light text-primary border fw-bold px-2 py-1">
                    <?php echo date('d/m/Y', strtotime($res['fecha_entrada'])); ?>
                </span>
            </td>

            <td>
                <div class="fw-bold text-dark"><?php echo $res['first_name'] . " " . $res['last_name']; ?></div>
                <div class="text-muted small text-lowercase"><?php echo $res['email'] ?? 'Sin email'; ?></div>
            </td>

            <td>
                <div class="fw-bold text-primary"><?php echo $res['hotel_nombre']; ?></div>
                <div class="text-muted small"><?php echo $res['habitacion_nombre']; ?></div>
            </td>

            <td class="text-center">
                <?php 
                    $status_raw = $res['estado_nombre'];
                    $status_clean = mb_strtolower(trim($status_raw), 'UTF-8');
                    $color_dot = '#6c757d';
                    if (str_contains($status_clean, 'confirm')) { $color_dot = '#198754'; } 
                    elseif (str_contains($status_clean, 'revis') || str_contains($status_clean, 'pendien')) { $color_dot = '#ffc107'; } 
                    elseif (str_contains($status_clean, 'cancel')) { $color_dot = '#dc3545'; }
                ?>
                <span class="badge border rounded-pill px-3 py-1 text-dark fw-normal d-inline-flex align-items-center bg-white shadow-sm" style="font-size: 0.75rem;">
                    <i class="bi bi-circle-fill me-2" style="color: <?php echo $color_dot; ?>; font-size: 0.5rem;"></i>
                    <?php echo $status_raw; ?>
                </span>
            </td>
                                    <td class="text-center">
    <div class="d-inline-flex gap-1">
                 <a href="view_reserva.php?uuid=<?php echo $res['uuid_reserva']; ?>" 
           class="btn btn-sm text-primary p-1" title="Ver detalle">
            <i class="bi bi-eye"></i>
        </a>

        <?php if($res['id_status'] == 8): ?>
            <a href="update_reserva.php?uuid=<?php echo $res['uuid_reserva']; ?>&action=approve" 
               class="btn btn-sm text-success p-1" title="Aprobar">
                <i class="bi bi-check-lg"></i>
            </a>
            <a href="update_reserva.php?uuid=<?php echo $res['uuid_reserva']; ?>&action=reject" 
               class="btn btn-sm text-danger p-1" title="Rechazar">
                <i class="bi bi-x-lg"></i>
            </a>
        <?php endif; ?>

    </div>
</td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">No se encontraron reservaciones registradas.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>