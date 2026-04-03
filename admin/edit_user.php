<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $uuid = mysqli_real_escape_string($conexion, $_GET['id']);
    
    $query = "SELECT u.first_name, u.last_name, l.role, e.email, t.telefono 
              FROM usr_users u
              INNER JOIN usr_emails e ON u.uuid = e.user_uuid 
              INNER JOIN usr_users_login l ON u.uuid = l.user_uuid
              INNER JOIN usr_telefonos t ON u.uuid = t.user_uuid
              WHERE u.uuid = '$uuid'";
    
    $res = mysqli_query($conexion, $query);
    $user = mysqli_fetch_assoc($res);

    if (!$user) {
        header("Location: users.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_role = mysqli_real_escape_string($conexion, $_POST['role']);
    $new_phone = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $user_id = mysqli_real_escape_string($conexion, $_POST['uuid']);

    mysqli_query($conexion, "UPDATE usr_users_login SET role = '$new_role' WHERE user_uuid = '$user_id'");
    mysqli_query($conexion, "UPDATE usr_telefonos SET telefono = '$new_phone' WHERE user_uuid = '$user_id'");

    header("Location: users.php?msg=updated");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brooking | Editar Usuario</title>
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
            <h2 class="fw-bold m-0">Editar Perfil de Usuario</h2>
            <a href="users.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-arrow-left"></i> Volver a la lista
            </a>
        </div>

        <div class="card stat-card shadow-sm border-0 col-md-6 mx-auto rounded-3 mt-5">
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="uuid" value="<?php echo $uuid; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">NOMBRE COMPLETO</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $user['first_name'] . ' ' . $user['last_name']; ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">CORREO ELECTRÓNICO</label>
                        <input type="email" class="form-control bg-light" value="<?php echo $user['email']; ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">NÚMERO TELEFÓNICO</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="telefono" class="form-control border-primary" 
                                   value="<?php echo $user['telefono']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">ROL DEL SISTEMA</label>
                        <select name="role" class="form-select border-primary">
                            <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>Usuario Estándar</option>
                            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                            <option value="propietario" <?php echo ($user['role'] == 'propietario') ? 'selected' : ''; ?>>Propietario</option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold py-2 rounded-pill">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>