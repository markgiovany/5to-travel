<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($config, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($config, $_POST['last_name']);
    $email      = mysqli_real_escape_string($config, $_POST['email']);
    $phone      = mysqli_real_escape_string($config, $_POST['phone']);
    $role       = mysqli_real_escape_string($config, $_POST['role']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

$email_regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match($email_regex, $email)) {
        $_SESSION['flash'] = [
            'type'  => 'danger',
            'title' => 'Correo inválido',
            'msg'   => 'El formato del correo es incorrecto (ejemplo: usuario@dominio.com).'
        ];
        header("Location: add_user.php");
        exit();
    }

    // 2. Validar Teléfono (Debe tener exactamente 10 dígitos)
    if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $_SESSION['flash'] = [
            'type'  => 'danger',
            'title' => 'Teléfono inválido',
            'msg'   => 'El número telefónico debe tener exactamente 10 dígitos.'
        ];
        header("Location: add_user.php");
        exit();
    }

    
    $uuid = bin2hex(random_bytes(16)); 

    mysqli_begin_transaction($config);

    try {
        mysqli_query($config, "INSERT INTO usr_users (uuid, first_name, last_name) VALUES ('$uuid', '$first_name', '$last_name')");

        mysqli_query($config, "INSERT INTO usr_emails (user_uuid, email) VALUES ('$uuid', '$email')");

        if (!empty($phone)) {
            mysqli_query($config, "INSERT INTO usr_telefonos (user_uuid, telefono) VALUES ('$uuid', '$phone')");
        }

        mysqli_query($config, "INSERT INTO usr_users_login (user_uuid, password, role) VALUES ('$uuid', '$password', '$role')");

        mysqli_commit($config);
        $_SESSION['flash'] = ['type' => 'success', 'title' => '¡Excelente!', 'msg' => 'El usuario ha sido registrado correctamente.'];
        header("Location: users.php");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($config);
        $_SESSION['flash'] = [
            'type' => 'danger',
            'title' => 'Error de registro',
            'msg' => 'No se pudo crear el usuario: ' . $e->getMessage()
        ];
        header("Location: add_user.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Brooking | Nuevo Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
</head>
<body class="bg-light p-5">
<div class="container">
    <div class="col-md-5 mx-auto card shadow-sm border-0 p-4 rounded-3">
        <h4 class="fw-bold mb-4">Registrar Nuevo Usuario</h4>

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

    <?php unset($_SESSION['flash']); // Limpiamos la sesión después de mostrarlo ?>
<?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold">NOMBRE</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Ej: Juan" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold">APELLIDO</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Ej: Pérez" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">CORREO ELECTRÓNICO</label>
                <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">TELÉFONO</label>
                <input type="text" name="phone" class="form-control" placeholder="Ej. 9981234567">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">CONTRASEÑA</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">ROL ASIGNADO</label>
                <select name="role" class="form-select" required>
                    <option value="user">Usuario (Cliente)</option>
                    <option value="propietario">Propietario (Dueño de Hotel)</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold rounded-pill">Crear Usuario</button>
            <a href="users.php" class="btn btn-link w-100 mt-2 text-muted text-decoration-none text-center small">Cancelar</a>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>