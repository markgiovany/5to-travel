<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$filtro = "";
if (isset($_GET['role']) && !empty($_GET['role'])) {
    $role_val = mysqli_real_escape_string($conexion, $_GET['role']);
    $filtro = " WHERE l.role = '$role_val' ";
}

$query = "SELECT u.uuid, u.first_name, u.last_name, t.telefono, l.role, e.email, u.created_at 
          FROM usr_users u
          INNER JOIN usr_emails e ON u.uuid = e.user_uuid 
          INNER JOIN usr_users_login l ON u.uuid = l.user_uuid
          LEFT JOIN usr_telefonos t ON u.uuid = t.user_uuid
          $filtro
          ORDER BY u.created_at DESC";

$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conexion));
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

        <div class="d-flex gap-2 mb-3">
            <a href="users.php" class="btn btn-sm rounded-pill px-3 <?php echo !isset($_GET['role']) ? 'btn-dark' : 'btn-outline-dark'; ?>">Todos</a>
            <a href="users.php?role=admin" class="btn btn-sm rounded-pill px-3 <?php echo (isset($_GET['role']) && $_GET['role'] == 'admin') ? 'btn-primary' : 'btn-outline-primary'; ?>">Administradores</a>
            <a href="users.php?role=propietario" class="btn btn-sm rounded-pill px-3 <?php echo (isset($_GET['role']) && $_GET['role'] == 'propietario') ? 'btn-warning text-white' : 'btn-outline-warning'; ?>">Propietarios</a>
            <a href="users.php?role=user" class="btn btn-sm rounded-pill px-3 <?php echo (isset($_GET['role']) && $_GET['role'] == 'user') ? 'btn-secondary' : 'btn-outline-secondary'; ?>">Usuarios</a>
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
                            <?php while($row = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td><strong><?php echo $row['first_name'] . " " . $row['last_name']; ?></strong></td>
                                <td>
                                    <div class="small fw-bold"><?php echo $row['email']; ?></div>
                                    <div class="text-muted small">
                                        <i class="bi bi-telephone"></i> <?php echo !empty($row['telefono']) ? $row['telefono'] : 'Sin teléfono'; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        $badge_class = "bg-secondary text-white"; 
                                        if($row['role'] == 'admin') $badge_class = "bg-primary text-white";
                                        if($row['role'] == 'propietario') $badge_class = "bg-warning text-white";
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?> rounded-pill px-3 text-capitalize">
                                        <?php echo $row['role']; ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?php echo substr($row['uuid'], 0, 8); ?>...</td>
                                <td><span class="small"><?php echo date('d M, Y', strtotime($row['created_at'])); ?></span></td>
                                <td class="text-center">
                                    <a href="delete_user.php?id=<?php echo $row['uuid']; ?>" class="btn btn-sm text-danger" onclick="return confirm('¿Borrar este usuario?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <a href="edit_user.php?id=<?php echo $row['uuid']; ?>" class="btn btn-sm text-primary ms-1">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
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