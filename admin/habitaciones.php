<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['u'])) {
    header("Location: hoteles.php");
    exit();
}

$uuid_hotel = mysqli_real_escape_string($config, $_GET['u']);

$res_hotel = mysqli_query($config, "SELECT id_catalogo, nombre FROM catalogo WHERE uuid = '$uuid_hotel'");
$hotel_info = mysqli_fetch_assoc($res_hotel);

if (!$hotel_info) {
    header("Location: hoteles.php");
    exit();
}

$id_hotel = $hotel_info['id_catalogo'];
$where_clauses = ["ch.id_catalogo = '$id_hotel'"];

// --- VARIABLES DE FILTRO ---
$search_val = isset($_GET['search_hab']) ? mysqli_real_escape_string($config, $_GET['search_hab']) : '';
$tipo_val = isset($_GET['tipo']) ? mysqli_real_escape_string($config, $_GET['tipo']) : '';
$status_val = isset($_GET['status']) ? mysqli_real_escape_string($config, $_GET['status']) : '';

if (!empty($search_val)) {
    $where_clauses[] = "ch.nombre LIKE '%$search_val%'";
}

if (!empty($tipo_val)) {
    $where_clauses[] = "ch.id_tipo = '$tipo_val'";
}

if (!empty($status_val)) {
    $where_clauses[] = "ch.id_status = '$status_val'";
}

$where_sql = " WHERE " . implode(" AND ", $where_clauses);

// --- QUERIES ---
$query = "SELECT ch.*, t.nombre as tipo_nombre, s.nombre as estado_nombre,
          (SELECT COUNT(*) FROM reservas r WHERE r.id_habitacion = ch.id_habitacion) as ocupadas 
          FROM cat_catalogo_habitacion ch
          LEFT JOIN cat_tipo t ON ch.id_tipo = t.id_tipo
          LEFT JOIN status s ON ch.id_status = s.id_status
          $where_sql 
          ORDER BY ch.precio ASC";

$resultado = mysqli_query($config, $query);
$res_tipos_lista = mysqli_query($config, "SELECT * FROM cat_tipo");
// Obtenemos solo Activo(1), Inactivo(2), Mantenimiento(7)
$res_status_list = mysqli_query($config, "SELECT * FROM status WHERE id_status IN (1, 2, 7) ORDER BY FIELD(id_status, 1, 2, 7)"); 

if (!$resultado) {
    die("Error en la consulta SQL: " . mysqli_error($config));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brooking | Habitaciones de <?php echo $hotel_info['nombre']; ?></title>
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
        <div class="p-4 text-center">
            <img src="../imagenes/brooking.png" alt="Logo" width="140">
        </div>
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
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <div class="d-flex align-items-center gap-3 mb-1">
                    <h2 class="fw-bold m-0">Gestión de Habitaciones</h2>
                    <a href="add_habitacion.php?u=<?php echo $uuid_hotel; ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bi-door-open"></i> Agregar Habitación
                    </a>
                </div>
                <p class="text-primary fw-semibold m-0 fs-5">
                    <i class="bi bi-building me-1"></i> <?php echo $hotel_info['nombre']; ?>
                </p>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Admin, <strong><?php echo $_SESSION['first_name'] ?? 'Admin'; ?></strong></span>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['first_name'] ?? 'Admin'; ?>&background=6f42c1&color=fff" class="rounded-circle" width="40">
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] == 'added'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>¡Excelente!</strong> La habitación ha sido registrada con éxito.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['msg'] == 'updated'): ?>
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>¡Actualizado!</strong> Los detalles de la habitación se guardaron correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-trash-fill me-2"></i>
                    <strong>Habitación eliminada.</strong> El registro ha sido removido del inventario.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['msg'] == 'error'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    <strong>Error:</strong> No se pudo procesar la solicitud de la habitación.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="mb-4">
            <small class="text-muted fw-bold me-2">ESTADO:</small>
            <a href="habitaciones.php?u=<?php echo $uuid_hotel; ?>&tipo=<?php echo urlencode($tipo_val); ?>&search_hab=<?php echo urlencode($search_val); ?>" 
               class="status-link btn btn-xs <?php echo empty($status_val) ? 'fw-bold text-dark text-decoration-none' : 'text-muted'; ?>" 
               style="font-size: 0.8rem;">Todos</a>
            
            <?php 
            mysqli_data_seek($res_status_list, 0);
            while($s = mysqli_fetch_assoc($res_status_list)): 
            ?>
                <a href="habitaciones.php?u=<?php echo $uuid_hotel; ?>&tipo=<?php echo urlencode($tipo_val); ?>&search_hab=<?php echo urlencode($search_val); ?>&status=<?php echo $s['id_status']; ?>" 
                   class="status-link btn btn-xs ms-2 <?php echo ($status_val == $s['id_status']) ? 'fw-bold text-dark text-decoration-none' : 'text-muted'; ?>" 
                   style="font-size: 0.8rem;">
                    <?php echo $s['nombre']; ?>
                </a>
            <?php endwhile; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <form method="GET" class="d-flex align-items-center gap-3 flex-grow-1">
                <input type="hidden" name="u" value="<?php echo $uuid_hotel; ?>">
                <input type="hidden" name="status" value="<?php echo $status_val; ?>">

                <div style="min-width: 220px;">
                    <label class="small fw-bold text-muted mb-1 d-block" style="margin-left: 10px; font-size: 0.7rem; letter-spacing: 0.5px;">TIPO DE HABITACIÓN</label>
                    <select name="tipo" class="form-select form-select-sm rounded-pill border-0 shadow-sm px-3" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <?php 
                        mysqli_data_seek($res_tipos_lista, 0); 
                        while($t = mysqli_fetch_assoc($res_tipos_lista)): 
                        ?>
                            <option value="<?php echo $t['id_tipo']; ?>" <?php echo ($tipo_val == $t['id_tipo']) ? 'selected' : ''; ?>>
                                <?php echo $t['nombre']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div style="width: 300px;">
                    <label class="small fw-bold text-muted mb-1 d-block" style="margin-left: 10px; font-size: 0.7rem; letter-spacing: 0.5px;">BUSCAR NOMBRE</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search_hab" class="form-control rounded-start-pill border-0 shadow-sm px-3" 
                               placeholder="Ej: Suite..." value="<?php echo htmlspecialchars($search_val); ?>">
                        <button class="btn btn-dark rounded-end-pill px-3 shadow-sm" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            <a href="hoteles.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-arrow-left"></i> Volver a la lista
            </a>
        </div>

        <div class="card stat-card shadow-sm border-0 rounded-3">
            <div class="card-body p-4"> 
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Habitación / Tipo</th>
                                <th class="text-center">Capacidad</th>
                                <th class="text-center">Estatus</th>
                                <th class="text-center">Precio</th>
                                <th class="text-center pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($resultado) > 0): ?>
                                <?php while($hab = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo $hab['nombre']; ?></div>
                                        <div class="badge bg-primary rounded-pill small fw-normal">
                                            <?php echo $hab['tipo_nombre'] ?? 'Sin tipo'; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border rounded-pill">
                                            <i class="bi bi-people"></i> <?php echo $hab['capacidad']; ?> paxs.
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge border rounded-pill text-dark fw-normal px-2">
                                            <i class="bi bi-circle-fill dot me-1" style="color: <?= ($hab['id_status']==1) ? '#198754' : (($hab['id_status']==7) ? '#ffc107' : '#dc3545') ?>;"></i>
                                            <?= $hab['estado_nombre'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center text-success fw-bold">
                                        $<?php echo number_format($hab['precio'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="delete_habitacion.php?name=<?php echo urlencode($hab['nombre']); ?>&u=<?php echo $uuid_hotel; ?>" 
                                           class="btn btn-sm text-danger" onclick="return confirm('¿Borrar?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <a href="edit_habitacion.php?uh=<?php echo urlencode($hab['uuid']); ?>&u=<?php echo $uuid_hotel; ?>" class="btn btn-sm text-primary ms-1">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No se encontraron habitaciones.</td>
                                </tr>
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