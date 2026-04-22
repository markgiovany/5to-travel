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
    $condiciones[] = "c.id_status = '$status_val'";
} else {
    $condiciones[] = "c.id_status IN (1, 2, 7)";
}

if (!empty($search_val)) {
    $condiciones[] = "(c.nombre LIKE '%$search_val%' OR u.first_name LIKE '%$search_val%' OR u.last_name LIKE '%$search_val%')";
}

$filtro_sql = " WHERE " . implode(" AND ", $condiciones);

$query = "SELECT c.id_catalogo, c.uuid, c.nombre, c.descripcion, u.first_name, u.last_name, s.nombre as estado_nombre, c.id_status,
          (SELECT COUNT(*) FROM cat_catalogo_habitacion ch WHERE ch.id_catalogo = c.id_catalogo) as habitaciones_totales
          FROM catalogo c
          LEFT JOIN usr_users u ON c.propietario_uuid = u.uuid 
          LEFT JOIN status s ON c.id_status = s.id_status
          $filtro_sql
          ORDER BY c.id_catalogo DESC";

$resultado = mysqli_query($config, $query);
$res_status_list = mysqli_query($config, "SELECT * FROM status WHERE id_status IN (1, 2, 7)");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Brooking | Gestión de Hoteles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
    <style>
        .hotel-link { text-decoration: none; transition: 0.2s; }
        .hotel-link:hover { opacity: 0.7; }
        .status-link { text-decoration: none !important; }
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
            <li><a href="hoteles.php" class="nav-link active"><i class="bi bi-building"></i> Hoteles</a></li>
            <li><a href="reservaciones.php" class="nav-link"><i class="bi bi-calendar-check"></i> Reservaciones</a></li>
        </ul>
        <div class="p-3 border-top">
            <a href="../auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <h2 class="fw-bold m-0">Gestión de Hoteles</h2>
                <a href="add_hotel.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-building-add"></i> Agregar Hotel
                </a>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Admin <strong><?php echo $_SESSION['first_name'] ?? 'Admin'; ?></strong></span>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['first_name'] ?? 'Admin'; ?>&background=6f42c1&color=fff" class="rounded-circle" width="40">
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] == 'added'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>¡Excelente!</strong> El hotel ha sido registrado correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['msg'] == 'updated'): ?>
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>¡Actualizado!</strong> Los cambios se guardaron con éxito.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-trash-fill me-2"></i>
                    <strong>Hotel eliminado.</strong> Los datos del hotel y sus fotos han sido ocultados.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <small class="text-muted fw-bold me-2">ESTADO:</small>
                <a href="hoteles.php?search=<?php echo $search_val; ?>" class="status-link btn btn-xs <?php echo empty($status_val) ? 'fw-bold text-dark' : 'text-muted'; ?>" style="font-size: 0.8rem;">Todos</a>
                <?php mysqli_data_seek($res_status_list, 0); while($s = mysqli_fetch_assoc($res_status_list)): ?>
                    <a href="hoteles.php?search=<?php echo $search_val; ?>&status=<?php echo $s['id_status']; ?>" 
                       class="status-link btn btn-xs ms-2 <?php echo ($status_val == $s['id_status']) ? 'fw-bold text-dark text-decoration-underline' : 'text-muted'; ?>" 
                       style="font-size: 0.8rem;">
                        <?php echo $s['nombre']; ?>
                    </a>
                <?php endwhile; ?>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if(!empty($search_val) || !empty($status_val)): ?>
                    <a href="hoteles.php" class="btn btn-link text-muted p-0 me-1" title="Limpiar filtros">
                        <i class="bi bi-x-circle fs-5"></i>
                    </a>
                <?php endif; ?>
                
                <div style="width: 300px;">
                    <form method="GET" class="input-group input-group-sm">
                        <?php if(!empty($status_val)) echo '<input type="hidden" name="status" value="'.$status_val.'">'; ?>
                        <input type="text" name="search" class="form-control rounded-start-pill" placeholder="Buscar hotel..." value="<?php echo htmlspecialchars($search_val); ?>">
                        <button class="btn btn-dark rounded-end-pill px-3" type="submit"><i class="bi bi-search"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card stat-card shadow-sm border-0 rounded-3">
            <div class="card-body p-4"> 
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre del Hotel</th>
                                <th>Propietario</th>
                                <th class="text-center">Estatus</th>
                                <th class="text-center">Habitaciones Totales</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($resultado) > 0): ?>
                                <?php while($hotel = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td>
                                        <a href="habitaciones.php?u=<?= $hotel['uuid'] ?>" class="hotel-link text-decoration-none text-dark">
                                            <div class="fw-bold"><?php echo $hotel['nombre']; ?></div>
                                            <div class="text-muted small"><?php echo mb_strimwidth($hotel['descripcion'], 0, 45, "..."); ?></div>
                                        </a>
                                    </td>
                                    <td class="small fw-bold text-dark">
                                        <?php echo $hotel['first_name'] . " " . $hotel['last_name']; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge border rounded-pill text-dark fw-normal px-2">
                                            <?php 
                                                // Definición de colores por ID de estatus
                                                $color_dot = '#dc3545'; // Rojo por defecto (Inactivo)
                                                if($hotel['id_status'] == 1) $color_dot = '#198754'; // Verde (Activo)
                                                if($hotel['id_status'] == 7) $color_dot = '#ffc107'; // Amarillo (Mantenimiento)
                                            ?>
                                            <i class="bi bi-circle-fill dot me-1" style="color: <?= $color_dot ?>;"></i>
                                            <?= $hotel['estado_nombre'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge border rounded-pill px-3 bg-light text-dark">
                                            <i class="bi bi-door-open me-1"></i> 
                                            <?php echo $hotel['habitaciones_totales']; ?> habs.
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if($hotel['id_status'] == 1): ?>
                                            <a href="delete_hotel.php?u=<?= $hotel['uuid'] ?>" class="btn btn-sm text-danger" title="Eliminar" onclick="return confirm('¿Desea dar de baja este hotel?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="reactivar_hotel.php?u=<?= $hotel['uuid'] ?>" class="btn btn-sm text-success" title="Reactivar">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="edit_hotel.php?u=<?= $hotel['uuid'] ?>" class="btn btn-sm text-primary" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">Sin coincidencias</td></tr>
                            <?php endif; ?>
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