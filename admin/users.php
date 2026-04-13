<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$role_val = isset($_GET['role']) ? mysqli_real_escape_string($config, $_GET['role']) : '';
$search_val = isset($_GET['search']) ? mysqli_real_escape_string($config, $_GET['search']) : '';

$condiciones = [];
if (!empty($role_val)) {
    $condiciones[] = "l.role = '$role_val'";
}
if (!empty($search_val)) {
    $condiciones[] = "(u.first_name LIKE '%$search_val%' OR u.last_name LIKE '%$search_val%' OR e.email LIKE '%$search_val%')";
}

$filtro = "";
if (count($condiciones) > 0) {
    $filtro = " WHERE " . implode(" AND ", $condiciones);
}

$query = "SELECT u.uuid, u.first_name, u.last_name, t.telefono, l.role, e.email, u.created_at 
          FROM usr_users u
          LEFT JOIN usr_emails e ON u.uuid = e.user_uuid 
          LEFT JOIN usr_users_login l ON u.uuid = l.user_uuid
          LEFT JOIN usr_telefonos t ON u.uuid = t.user_uuid
          $filtro
          ORDER BY u.created_at DESC";

$resultado = mysqli_query($config, $query);

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($config));
}
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
            <a href="../auth/logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
            </a>
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
                <span class="me-3 text-muted">Admin <strong><?php echo $_SESSION['first_name'] ?? 'Admin'; ?></strong></span>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['first_name'] ?? 'Admin'; ?>&background=6f42c1&color=fff" class="rounded-circle" width="40">
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2">
                <a href="users.php?search=<?php echo $search_val; ?>" class="btn btn-sm rounded-pill px-3 <?php echo !isset($_GET['role']) ? 'btn-dark' : 'btn-outline-dark'; ?>">Todos</a>
                <a href="users.php?role=admin&search=<?php echo $search_val; ?>" class="btn btn-sm rounded-pill px-3 <?php echo (isset($_GET['role']) && $_GET['role'] == 'admin') ? 'btn-primary' : 'btn-outline-primary'; ?>">Administradores</a>
                <a href="users.php?role=propietario&search=<?php echo $search_val; ?>" class="btn btn-sm rounded-pill px-3 <?php echo (isset($_GET['role']) && $_GET['role'] == 'propietario') ? 'btn-warning text-white' : 'btn-outline-warning'; ?>">Propietarios</a>
                <a href="users.php?role=user&search=<?php echo $search_val; ?>" class="btn btn-sm rounded-pill px-3 <?php echo (isset($_GET['role']) && $_GET['role'] == 'user') ? 'btn-secondary' : 'btn-outline-secondary'; ?>">Usuarios</a>
            </div>

            <div style="width: 300px;">
                <form method="GET" class="input-group input-group-sm">
                    <?php if(!empty($role_val)): ?>
                        <input type="hidden" name="role" value="<?php echo $role_val; ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="form-control rounded-start-pill" placeholder="Buscar usuario..." value="<?php echo htmlspecialchars($search_val); ?>">
                    <button class="btn btn-dark rounded-end-pill px-3" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <?php 
                    if($_GET['msg'] == 'deleted') echo '¡Usuario eliminado correctamente!';
                    elseif($_GET['msg'] == 'added') echo '¡Usuario creado con éxito!';
                    else echo '¡Datos actualizados!';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card stat-card shadow-sm border-0 rounded-3">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Contacto</th>
                                <th>Rol</th>
                                <th>UUID / ID</th>
                                <th>Registro</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($resultado) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td><strong><?php echo $row['first_name'] . " " . $row['last_name']; ?></strong></td>
                                    <td>
                                        <div class="small fw-bold"><?php echo $row['email'] ?? 'Sin correo'; ?></div>
                                        <div class="text-muted small">
                                            <i class="bi bi-telephone"></i> <?php echo !empty($row['telefono']) ? $row['telefono'] : 'Sin teléfono'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $badge_class = "bg-secondary text-white"; 
                                            $r = $row['role'] ?? 'user';
                                            if($r == 'admin') $badge_class = "bg-primary text-white";
                                            if($r == 'propietario') $badge_class = "bg-warning text-white";
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?> rounded-pill px-3 text-capitalize">
                                            <?php echo $r; ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?php echo substr($row['uuid'], 0, 8); ?>...</td>
                                    <td><span class="small"><?php echo ($row['created_at']) ? date('d M, Y', strtotime($row['created_at'])) : 'N/A'; ?></span></td>
                                    <td class="text-center">
                                        <a href="delete_user.php?id=<?php echo $row['uuid']; ?>" class="btn btn-sm text-danger" onclick="return confirm('¿Borrar este usuario?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <a href="edit_user.php?u=<?php echo $row['uuid']; ?>" class="btn btn-sm text-primary ms-1">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <p class="mb-2">Sin coincidencias</p>
                                        <a href="users.php" class="btn btn-sm btn-outline-secondary rounded-pill">Limpiar filtros</a>
                                    </td>
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