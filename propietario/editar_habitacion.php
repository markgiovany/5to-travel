<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";
use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$uuid = $_GET['uuid'] ?? '';
$user_uuid = $_SESSION['user_uuid'];

/* obtener habitación */
$sql = "SELECT ch.*, t.nombre AS tipo_nombre
FROM cat_catalogo_habitacion ch
INNER JOIN catalogo c ON c.id_catalogo = ch.id_catalogo
LEFT JOIN cat_tipo t ON t.id_tipo = ch.id_tipo
WHERE ch.uuid = '$uuid' 
AND c.propietario_uuid = '$user_uuid'";

$res = mysqli_query($config, $sql);
$habitacion = mysqli_fetch_assoc($res);

if (!$habitacion) {
    die("Habitación no encontrada");
}

/* tipos */
$tipos = mysqli_query($config, "SELECT * FROM cat_tipo");
$estados = mysqli_query($config, "
    SELECT * FROM status 
    WHERE nombre IN ('Activo','Mantenimiento')
");

/* update */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $capacidad = intval($_POST['capacidad']);
    $precio = floatval($_POST['precio']);
    $tipo = intval($_POST['tipo']);
    $estado = intval($_POST['estado']);

    /* actualizar datos */
    mysqli_query($config, "
        UPDATE cat_catalogo_habitacion 
        SET nombre='$nombre',
            capacidad='$capacidad',
            precio='$precio',
            id_tipo='$tipo',
            id_status='$estado'
        WHERE uuid='$uuid'
    ");

    /* subir imagenes a cloudinary */
    if (!empty($_FILES['imagenes']['name'][0])) {

        foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {

            $resultado = (new UploadApi())->upload($tmp_name);
            $url = $resultado['secure_url'];

            mysqli_query($config, "
                INSERT INTO cat_imagen (id_habitacion, url_imagen)
                VALUES ('{$habitacion['id_habitacion']}', '$url')
            ");
        }
    }

    header("Location: habitaciones.php");
    exit();
}
$imagenes = mysqli_query($config, "
    SELECT * FROM cat_imagen 
    WHERE id_habitacion = '{$habitacion['id_habitacion']}'
");
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

<form method="POST" enctype="multipart/form-data">

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

<!-- estatus -->
<div class="mb-3">
<label class="form-label small text-muted">ESTATUS</label>

<select name="estado" class="form-select">
<?php while($e = mysqli_fetch_assoc($estados)): ?>
<option value="<?= $e['id_status'] ?>"
<?= $habitacion['id_status'] == $e['id_status'] ? 'selected' : '' ?>>
<?= $e['nombre'] ?>
</option>
<?php endwhile; ?>
</select>
</div>

<!-- subir imágenes -->
<div class="mb-3">
<label class="form-label small text-muted">IMÁGENES</label>
<input type="file" name="imagenes[]" multiple class="form-control">
</div>

<!-- imágenes actuales -->
<div class="mb-3">
<label class="form-label small text-muted">IMÁGENES ACTUALES</label>

<div class="d-flex flex-wrap gap-2">
<?php while($img = mysqli_fetch_assoc($imagenes)): ?>
    <div style="position: relative;">
        
        <img src="<?= $img['url_imagen'] ?>" width="100" class="rounded">

        <a href="eliminar_imagen.php?id=<?= $img['id_imagen'] ?>&uuid=<?= $uuid ?>"
        class="btn btn-danger btn-sm"
        style="position:absolute; top:0; right:0;"
        onclick="return confirm('¿Eliminar imagen?')">
            ×
        </a>

    </div>
<?php endwhile; ?>
</div>
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