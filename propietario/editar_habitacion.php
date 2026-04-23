<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$uuid = $_SESSION['user_uuid'];

/* obtener habitación */
$sql = "SELECT ch.*, t.nombre AS tipo_nombre
FROM cat_catalogo_habitacion ch
INNER JOIN catalogo c ON c.id_catalogo = ch.id_catalogo
LEFT JOIN cat_tipo t ON t.id_tipo = ch.id_tipo
WHERE ch.id_habitacion = '$id' 
AND c.propietario_uuid = '$uuid'";

$res = mysqli_query($config, $sql);
$habitacion = mysqli_fetch_assoc($res);

if (!$habitacion) {
    die("Habitación no encontrada");
}

/* tipos */
$tipos = mysqli_query($config, "SELECT * FROM cat_tipo");

/* update */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $capacidad = intval($_POST['capacidad']);
    $precio = floatval($_POST['precio']);
    $tipo = intval($_POST['tipo']);

    mysqli_query($config, "
        UPDATE cat_catalogo_habitacion 
        SET nombre='$nombre',
            capacidad='$capacidad',
            precio='$precio',
            id_tipo='$tipo'
        WHERE id_habitacion='$id'
    ");

    header("Location: habitaciones.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Habitación</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background-color: #f4f6f9;
}

.card {
    border-radius: 12px;
}
</style>
</head>

<body>

<div class="container mt-5">

<div class="col-md-6 mx-auto">

<div class="card shadow-sm p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Editar Habitación</h5>

    <a href="habitaciones.php" class="btn btn-outline-secondary btn-sm">
        ← Volver
    </a>
</div>

<form method="POST">

<!-- nombre -->
<div class="mb-3">
<label class="form-label small text-muted">NOMBRE</label>
<input type="text" name="nombre"
value="<?= htmlspecialchars($habitacion['nombre']) ?>"
class="form-control" required>
</div>

<!-- tipo -->
<div class="mb-3">
<label class="form-label small text-muted">TIPO DE HABITACIÓN</label>

<select name="tipo" class="form-select" required>
<?php while($t = mysqli_fetch_assoc($tipos)): ?>
<option value="<?= $t['id_tipo'] ?>"
<?= $habitacion['id_tipo'] == $t['id_tipo'] ? 'selected' : '' ?>>
<?= $t['nombre'] ?>
</option>
<?php endwhile; ?>
</select>
</div>

<!-- capacidad -->
<div class="mb-3">
<label class="form-label small text-muted">CAPACIDAD</label>
<input type="number" name="capacidad"
value="<?= $habitacion['capacidad'] ?>"
class="form-control" required>
</div>

<!-- precio -->
<div class="mb-4">
<label class="form-label small text-muted">PRECIO</label>
<input type="number" step="0.01" name="precio"
value="<?= $habitacion['precio'] ?>"
class="form-control" required>
</div>

<button class="btn btn-primary w-100">
    Guardar cambios
</button>

</form>

</div>
</div>

</div>

</body>
</html>