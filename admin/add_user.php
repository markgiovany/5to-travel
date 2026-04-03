<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = mysqli_real_escape_string($conexion, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($conexion, $_POST['last_name']);
    $email      = mysqli_real_escape_string($conexion, $_POST['email']);
    $phone      = mysqli_real_escape_string($conexion, $_POST['phone']); // Nuevo campo
    $role       = mysqli_real_escape_string($conexion, $_POST['role']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $uuid = bin2hex(random_bytes(16)); 

    mysqli_begin_transaction($conexion);

    try {
        // 1. Datos personales
        mysqli_query($conexion, "INSERT INTO usr_users (uuid, first_name, last_name) VALUES ('$uuid', '$first_name', '$last_name')");

        // 2. Correo
        mysqli_query($conexion, "INSERT INTO usr_emails (user_uuid, email) VALUES ('$uuid', '$email')");

        // 3. Teléfono (Nueva inserción)
        if (!empty($phone)) {
            mysqli_query($conexion, "INSERT INTO usr_telefonos (user_uuid, telefono) VALUES ('$uuid', '$phone')");
        }

        // 4. Login y Rol
        mysqli_query($conexion, "INSERT INTO usr_users_login (user_uuid, password, role) VALUES ('$uuid', '$password', '$role')");

        mysqli_commit($conexion);
        header("Location: users.php?msg=added"); exit();

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        die("Error al crear usuario: " . $e->getMessage());
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
        <h4 class="fw-bold mb-4 text-center">Registrar Nuevo Usuario</h4>
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold">NOMBRE</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Ej: Mao" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold">APELLIDO</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Ej: R." required>
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
</body>
</html>