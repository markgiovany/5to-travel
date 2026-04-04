<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['user_uuid'])) {
    header("Location: ../auth/login.php");
    exit();
}

$propietario_uuid = $_SESSION['user_uuid'];
$sql = "SELECT id_catalogo, nombre, descripcion FROM catalogo WHERE propietario_uuid = '$propietario_uuid'";
$res = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Hoteles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container bg-white shadow-sm p-4 rounded">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Mis Hoteles Registrados</h3>
            <a href="propietario_dashboard.php" class="btn btn-outline-secondary btn-sm">Volver al Panel</a>
        </div>
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td>#<?php echo $row['id_catalogo']; ?></td>
                    <td><strong><?php echo $row['nombre']; ?></strong></td>
                    <td><?php echo htmlspecialchars(substr($row['descripcion'], 0, 50)); ?>...</td>
                </tr>
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($res) == 0): ?>
                    <tr><td colspan="3" class="text-center">No tienes hoteles aún.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>