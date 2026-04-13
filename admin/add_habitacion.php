<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";
use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['u'])) {
    header("Location: hoteles.php");
    exit();
}

$uuid_hotel = mysqli_real_escape_string($config, $_GET['u']);
$res_h = mysqli_query($config, "SELECT id_catalogo, nombre FROM catalogo WHERE uuid = '$uuid_hotel'");
$hotel = mysqli_fetch_assoc($res_h);
$res_tipos = mysqli_query($config, "SELECT id_tipo, nombre FROM cat_tipo ORDER BY nombre ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_hotel_db = $hotel['id_catalogo'];
    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($config, $_POST['descripcion']);
    $precio = mysqli_real_escape_string($config, $_POST['precio']);
    $capacidad = mysqli_real_escape_string($config, $_POST['capacidad']);
    $disponibilidad = mysqli_real_escape_string($config, $_POST['disponibilidad']);
    $id_tipo = mysqli_real_escape_string($config, $_POST['id_tipo']);

    try {
        //Lógica pal Cloudinary
        $url_foto = ""; 
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload = new UploadApi();
            $resultado_cloud = $upload->upload($_FILES['foto']['tmp_name'], [
                'folder' => 'brooking_habitaciones'
            ]);
            $url_foto = $resultado_cloud['secure_url'];
        } else {
            throw new Exception("Debes subir una foto de la habitación.");
        }

        $query = "INSERT INTO cat_catalogo_habitacion 
                  (id_catalogo, uuid, nombre, descripcion, precio, capacidad, disponibilidad, status, id_tipo, foto_url) 
                  VALUES 
                  ('$id_hotel_db', uuid(), '$nombre', '$descripcion', '$precio', '$capacidad', '$disponibilidad', 'active', '$id_tipo', '$url_foto')";

        if (mysqli_query($config, $query)) {
            header("Location: habitaciones.php?u=$uuid_hotel&msg=added");
            exit();
        } else {
            throw new Exception(mysqli_error($config));
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Habitación | <?php echo $hotel['nombre']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
</head>
<body class="bg-light p-5">

<div class="container">
    <div class="col-md-6 mx-auto card shadow-sm border-0 p-4">
        <h4 class="fw-bold mb-4">Nueva Habitación para <span class="text-primary"><?php echo $hotel['nombre']; ?></span></h4>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label fw-bold small">NOMBRE</label>
                <input type="text" name="nombre" class="form-control rounded-pill" placeholder="Ej. Suite Vista al Mar" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">CATEGORÍA</label>
                <select name="id_tipo" class="form-select rounded-pill" required>
                    <option value="" disabled selected>Selecciona tipo...</option>
                    <?php while($tipo = mysqli_fetch_assoc($res_tipos)): ?>
                        <option value="<?php echo $tipo['id_tipo']; ?>"><?php echo $tipo['nombre']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">DESCRIPCIÓN</label>
                <textarea name="descripcion" class="form-control" rows="2"></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small">PRECIO</label>
                    <input type="number" name="precio" class="form-control rounded-pill" step="0.01" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small">CAPACIDAD</label>
                    <input type="number" name="capacidad" class="form-control rounded-pill" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">FOTOS</label>
                <input type="file" name="foto[]" class="form-control" accept="image/*" multiple required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small">HABITACIONES DISPONIBLES</label>
                <input type="number" name="disponibilidad" class="form-control rounded-pill" required>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary fw-bold rounded-pill">Guardar Habitación</button>
                <a href="habitaciones.php?u=<?php echo $uuid_hotel; ?>" class="btn btn-link text-decoration-none small text-center">Cancelar</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>