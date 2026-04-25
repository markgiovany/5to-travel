<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$uuid = isset($_REQUEST['u']) ? mysqli_real_escape_string($config, $_REQUEST['u']) : (isset($_POST['uuid']) ? mysqli_real_escape_string($config, $_POST['uuid']) : null);

if (!$uuid) {
    header("Location: users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id    = mysqli_real_escape_string($config, $_POST['uuid']);
    $new_phone  = mysqli_real_escape_string($config, $_POST['telefono']);
    $new_first  = mysqli_real_escape_string($config, $_POST['first_name']);
    $new_last   = mysqli_real_escape_string($config, $_POST['last_name']);
    $new_email  = mysqli_real_escape_string($config, $_POST['email']);
    $new_password = $_POST['password'];
    $role_name_form = mysqli_real_escape_string($config, $_POST['role']);
    $role_query = mysqli_query($config, "SELECT id_rol FROM usr_roles WHERE rol = '$role_name_form'");
    $role_data = mysqli_fetch_assoc($role_query);
    $new_role_id = isset($roles_map[$_POST['role']]) ? $roles_map[$_POST['role']] : 3;
    $now = date("Y-m-d H:i:s");

    mysqli_begin_transaction($config);

    try {
        mysqli_query($config, "UPDATE usr_users SET first_name = '$new_first', last_name = '$new_last', updated_at = '$now' WHERE uuid = '$user_id'");

        mysqli_query($config, "INSERT INTO usr_emails (user_uuid, email) VALUES ('$user_id', '$new_email') ON DUPLICATE KEY UPDATE email = '$new_email'");

        mysqli_query($config, "INSERT INTO usr_telefonos (user_uuid, telefono) VALUES ('$user_id', '$new_phone') ON DUPLICATE KEY UPDATE telefono = '$new_phone'");

        mysqli_query($config, "UPDATE usr_users_login SET id_rol = $new_role_id WHERE user_uuid = '$user_id'");

        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            mysqli_query($config, "UPDATE usr_users_login SET password = '$hashed_password' WHERE user_uuid = '$user_id'");
        }

        mysqli_commit($config);
        $_SESSION['success_msg'] = "¡Usuario actualizado correctamente!";
        
        header("Location: users.php");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($config);
        die("Error al actualizar: " . $e->getMessage());
    }
}

$query = "SELECT u.first_name, u.last_name, r.rol AS role_name, e.email, t.telefono 
          FROM usr_users u
          LEFT JOIN usr_emails e ON u.uuid = e.user_uuid 
          LEFT JOIN usr_users_login l ON u.uuid = l.user_uuid
          LEFT JOIN usr_roles r ON l.id_rol = r.id_rol
          LEFT JOIN usr_telefonos t ON u.uuid = t.user_uuid
          WHERE u.uuid = '$uuid'";

$res = mysqli_query($config, $query);
$user = mysqli_fetch_assoc($res);

if (!$user) {
    header("Location: users.php");
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
                <form method="POST" action="edit_user.php">
                    <input type="hidden" name="uuid" value="<?php echo htmlspecialchars($uuid); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">NOMBRE(S)</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">APELLIDO(S)</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>

                   <div class="mb-3">
    <label class="form-label text-muted small fw-bold">CORREO ELECTRÓNICO</label>
    <input type="email" 
           name="email" 
           class="form-control" 
           value="<?php echo htmlspecialchars($user['email']); ?>" 
           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"
           title="Por favor, ingresa un correo válido (ejemplo@dominio.com)"
           required>
    <div class="form-text">Debe incluir un dominio válido (ej. .com, .mx).</div>
</div>

<div class="mb-3">
    <label class="form-label text-muted small fw-bold">NÚMERO TELEFÓNICO</label>
    <input type="text" name="telefono" class="form-control" 
           value="<?php echo htmlspecialchars($user['telefono']); ?>" 
           pattern="\d{10}" maxlength="10" minlength="10"
           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
           title="El número debe tener exactamente 10 dígitos" required>
    <div class="form-text">Deben ser 10 dígitos numéricos.</div>
</div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">ROL DEL SISTEMA</label>
                        <select name="role" class="form-select">
                            <option value="user" <?php echo ($user['role_name'] == 'user') ? 'selected' : ''; ?>>Usuario Estándar</option>
                            <option value="admin" <?php echo ($user['role_name'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                            <option value="propietario" <?php echo ($user['role_name'] == 'propietario') ? 'selected' : ''; ?>>Propietario</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">NUEVA CONTRASEÑA (Opcional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                    </div>

                    <div class="row g-2">
        <div class="col-md-6">
            <a href="users.php" class="btn btn-light w-100 fw-bold py-2 rounded-pill border shadow-sm text-muted">
            CANCELAR
            </a>
        </div>
        <div class="col-md-6">
            <button type="submit" class="btn btn-primary fw-bold py-2 rounded-pill w-100">
            ACTUALIZAR DATOS
            </button>
        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>