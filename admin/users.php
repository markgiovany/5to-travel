<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$role_val = isset($_GET['role']) ? mysqli_real_escape_string($config, $_GET['role']) : '';
$search_val = isset($_GET['search']) ? mysqli_real_escape_string($config, $_GET['search']) : '';
$status_val = isset($_GET['status']) ? mysqli_real_escape_string($config, $_GET['status']) : '';

$condiciones = [];
if (!empty($status_val)) {
    $condiciones[] = "l.id_status = '$status_val'";
}
if (!empty($role_val)) {
    $condiciones[] = "r.rol = '$role_val'";
}
if (!empty($search_val)) {
    $condiciones[] = "(u.first_name LIKE '%$search_val%' OR u.last_name LIKE '%$search_val%' OR e.email LIKE '%$search_val%')";
}

$filtro = "";
if (count($condiciones) > 0) {
    $filtro = " WHERE " . implode(" AND ", $condiciones);
}

$query = "SELECT 
            u.uuid, 
            u.first_name, 
            u.last_name, 
            u.created_at,
            r.rol AS rol_nombre, 
            l.id_status,
            s.nombre AS estado_nombre,
            (SELECT email FROM usr_emails WHERE user_uuid = u.uuid LIMIT 1) AS email,
            (SELECT telefono FROM usr_telefonos WHERE user_uuid = u.uuid LIMIT 1) AS telefono
          FROM usr_users u
          LEFT JOIN usr_users_login l ON u.uuid = l.user_uuid
          LEFT JOIN usr_roles r ON l.id_rol = r.id_rol
          LEFT JOIN status s ON l.id_status = s.id_status
          $filtro
          GROUP BY u.uuid
          ORDER BY u.first_name ASC";

$resultado = mysqli_query($config, $query);

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($config));
}

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($config));
}

$res_status_list = mysqli_query($config, "SELECT * FROM status WHERE id_status IN (1, 2, 4)");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brooking | Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
    <style>
        .action-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            min-width: 120px;
        }
        .status-link {
            text-decoration: none !important;
        }
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
            <li><a href="users.php" class="nav-link active"><i class="bi bi-people"></i> Usuarios</a></li>
            <li><a href="hoteles.php" class="nav-link"><i class="bi bi-building"></i> Hoteles</a></li>
            <li><a href="reservaciones.php" class="nav-link"><i class="bi bi-calendar-check"></i> Reservaciones</a></li>
        </ul>
        <div class="p-3 border-top">
            <a href="../auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <h2 class="fw-bold m-0">Gestión de Usuarios</h2>
                <a href="add_user.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
                </a>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Admin, <strong><?php echo $_SESSION['first_name'] ?? 'Admin'; ?></strong></span>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['first_name'] ?? 'Admin'; ?>&background=6f42c1&color=fff" class="rounded-circle" width="40">
            </div>
        </div>
<?php if (isset($_SESSION['flash'])): 
    $flash = $_SESSION['flash'];
    $icon = [
        'success' => 'bi-check-circle-fill',
        'info'    => 'bi-info-circle-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        'danger'  => 'bi-x-circle-fill'
    ][$flash['type']] ?? 'bi-bell-fill';
?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="<?= $icon ?> me-2"></i>
        <strong><?= $flash['title'] ?></strong> <?= $flash['msg'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <?php 
        unset($_SESSION['flash']); 
    ?>
<?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <a href="users.php?search=<?php echo $search_val; ?>&status=<?php echo $status_val; ?>" class="btn btn-sm rounded-pill px-3 <?php echo !isset($_GET['role']) || $_GET['role'] == '' ? 'btn-dark' : 'btn-outline-dark'; ?>">Todos</a>
                <a href="users.php?role=admin&search=<?php echo $search_val; ?>&status=<?php echo $status_val; ?>" class="btn btn-sm rounded-pill px-3 <?php echo ($role_val == 'admin') ? 'btn-primary' : 'btn-outline-primary'; ?>">Administradores</a>
                <a href="users.php?role=propietario&search=<?php echo $search_val; ?>&status=<?php echo $status_val; ?>" class="btn btn-sm rounded-pill px-3 <?php echo ($role_val == 'propietario') ? 'btn-warning text-white' : 'btn-outline-warning'; ?>">Propietarios</a>
                <a href="users.php?role=user&search=<?php echo $search_val; ?>&status=<?php echo $status_val; ?>" class="btn btn-sm rounded-pill px-3 <?php echo ($role_val == 'user') ? 'btn-secondary' : 'btn-outline-secondary'; ?>">Usuarios</a>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if(!empty($search_val) || !empty($role_val) || !empty($status_val)): ?>
                    <a href="users.php" class="btn btn-link text-muted p-0" title="Limpiar filtros">
                        <i class="bi bi-x-circle fs-5"></i>
                    </a>
                <?php endif; ?>
                
                <div style="width: 300px;">
                    <form method="GET" class="input-group input-group-sm">
                        <?php if(!empty($role_val)) echo '<input type="hidden" name="role" value="'.$role_val.'">'; ?>
                        <?php if(!empty($status_val)) echo '<input type="hidden" name="status" value="'.$status_val.'">'; ?>
                        <input type="text" name="search" class="form-control rounded-start-pill" placeholder="Buscar usuario..." value="<?php echo htmlspecialchars($search_val); ?>">
                        <button class="btn btn-dark rounded-end-pill px-3" type="submit"><i class="bi bi-search"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <small class="text-muted fw-bold me-2">ESTADO:</small>
            <a href="users.php?role=<?php echo $role_val; ?>&search=<?php echo $search_val; ?>" class="status-link btn btn-xs <?php echo empty($status_val) ? 'fw-bold text-dark' : 'text-muted'; ?>" style="font-size: 0.8rem;">Todos</a>
            <?php while($s = mysqli_fetch_assoc($res_status_list)): ?>
                <a href="users.php?role=<?php echo $role_val; ?>&search=<?php echo $search_val; ?>&status=<?php echo $s['id_status']; ?>" 
                   class="status-link btn btn-xs ms-2 <?php echo ($status_val == $s['id_status']) ? 'fw-bold text-dark text-decoration-underline' : 'text-muted'; ?>" 
                   style="font-size: 0.8rem;">
                    <?php echo $s['nombre']; ?>
                </a>
            <?php endwhile; ?>
        </div>

        <div class="card stat-card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start">Nombre Completo</th>
                                <th class="text-start">Contacto</th>
                                <th>Rol / Estado</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($resultado) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($resultado)): ?>
                                    
                                        <td class="text-start">
                                            <a href="view_user.php?u=<?php echo $row['uuid']; ?>" class="text-decoration-none text-dark"><strong><?php echo $row['first_name'] . " " . $row['last_name']; ?></strong>
                                        </a>
                                        </td>
                                <td class="text-start text-dark">
                                    <div class="small fw-bold"><?php echo $row['email'] ?? 'Sin correo'; ?></div>
                                    <div class="text-muted small"><i class="bi bi-telephone"></i>                           <?php echo !empty($row['telefono']) ? $row['telefono'] : 'Sin teléfono'; ?></div>
                                </td>
                                <td>
                                    <?php 
                                $badge_class = "bg-secondary text-white"; 
                                $r = $row['rol_nombre'] ?? 'user';
                                if($r == 'admin') $badge_class = "bg-primary text-white";
                                if($r == 'propietario') $badge_class = "bg-warning text-white";
                            ?>
                            <span class="badge <?php echo $badge_class; ?> rounded-pill px-3 text-capitalize mb-1"><?php echo $r; ?></span>
                            <br>
                            <small class="text-muted"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; color: <?php echo ($row['id_status']==1) ? '#198754' : (($row['id_status']==2) ? '#dc3545' : '#ffc107'); ?>;"></i><?php echo $row['estado_nombre']; ?></small>
                        </td>
                        <td><span class="small"><?php echo ($row['created_at']) ? date('d M, Y', strtotime($row['created_at'])) : 'N/A'; ?></span></td>

                        <td class="text-center">
                            <div class="action-container">
                                <?php if($row['id_status'] == 1):?>
                                    <a href="status_user.php?u=<?php echo $row['uuid']; ?>&to=2" class="btn btn-sm text-danger" title="Eliminar" onclick="return confirm('Eliminar usuario?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php elseif($row['id_status'] == 2 || $row['id_status'] == 4):?>
                                    <a href="status_user.php?u=<?php echo $row['uuid']; ?>&to=1" class="btn btn-sm text-success" title="Reactivar">
                                        <i class="bi-arrow-counterclockwise"></i>
                                    </a>
                                <?php endif; ?>

                                <a href="edit_user.php?u=<?php echo $row['uuid']; ?>" title="Editar" class="btn btn-sm text-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Sin coincidencias</td>
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