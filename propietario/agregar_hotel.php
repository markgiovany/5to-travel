<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";

use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$propietario_uuid = $_SESSION['user_uuid'];

/* TIPOS */
$res_tipos = mysqli_query($config, "SELECT * FROM cat_tipo");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim(mysqli_real_escape_string($config, $_POST['nombre']));
    $descripcion = trim(mysqli_real_escape_string($config, $_POST['descripcion']));

    $precio_min = floatval($_POST['precio_min']);
    $precio_max = floatval($_POST['precio_max']);

    $tipos = $_POST['tipos'] ?? [];

    if ($precio_min < 0 || $precio_max < 0) {
        $error = "Los precios no pueden ser negativos";
    } elseif ($precio_min > $precio_max) {
        $error = "El precio mínimo no puede ser mayor que el máximo";
    } elseif (empty($nombre) || empty($descripcion)) {
        $error = "Completa todos los campos";
    } else {

        /* INSERT HOTEL */
        $sql = "
            INSERT INTO catalogo 
            (propietario_uuid, nombre, descripcion, precio_min, precio_max)
            VALUES 
            ('$propietario_uuid', '$nombre', '$descripcion', '$precio_min', '$precio_max')
        ";

        if (mysqli_query($config, $sql)) {

            $id_hotel = mysqli_insert_id($config);

            /* TIPOS */
            if (!empty($tipos)) {
                foreach ($tipos as $id_tipo) {
                    $id_tipo = (int)$id_tipo;

                    mysqli_query($config, "
                        INSERT INTO cat_catalogo_habitacion (id_catalogo, id_tipo)
                        VALUES ('$id_hotel', '$id_tipo')
                    ");
                }
            }

            /* 🖼️ MULTI IMAGEN CLOUDINARY */
            if (!empty($_FILES['imagen']['name'][0])) {

                $upload = new UploadApi();

                foreach ($_FILES['imagen']['tmp_name'] as $key => $tmp_name) {

                    if ($_FILES['imagen']['error'][$key] == 0) {

                        $file_hash = md5_file($tmp_name);

                        $check = mysqli_query($config, "
                            SELECT url_imagen 
                            FROM cat_imagen 
                            WHERE hash_archivo = '$file_hash'
                            LIMIT 1
                        ");

                        if (mysqli_num_rows($check) > 0) {
                            $img_data = mysqli_fetch_assoc($check);
                            $url_final = $img_data['url_imagen'];
                        } else {
                            $result = $upload->upload($tmp_name, [
                                'folder' => 'hoteles'
                            ]);
                            $url_final = $result['secure_url'];
                        }

                        mysqli_query($config, "
                            INSERT INTO cat_imagen 
                            (id_catalogo, id_habitacion, url_imagen, hash_archivo, id_status)
                            VALUES 
                            ('$id_hotel', NULL, '$url_final', '$file_hash', 1)
                        ");
                    }
                }
            }

            header("Location: propietario_dashboard.php?hotel=ok");
            exit();

        } else {
            $error = "Error al guardar hotel";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar Hotel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light p-5">

<div class="container">
<div class="col-md-7 mx-auto">

<div class="card shadow-sm border-0 p-4 rounded-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">🏨 Agregar Hotel</h4>
    <a href="propietario_dashboard.php" class="btn btn-outline-primary btn-sm">
        ← Volver
    </a>
</div>

<?php if($error != ""): ?>
<div class="alert alert-danger border-0 shadow-sm small">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?php echo $error; ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<label class="form-label fw-bold small text-muted">NOMBRE DEL HOTEL</label>
<input type="text" name="nombre" class="form-control rounded-pill mb-3" required>

<label class="form-label fw-bold small text-muted">DESCRIPCIÓN</label>
<textarea name="descripcion" class="form-control rounded-4 mb-3" required></textarea>

<div class="row g-3 mb-3">
    <div class="col">
        <label class="form-label fw-bold small text-muted">PRECIO MÍNIMO</label>
        <input type="number" step="0.01" name="precio_min"
        class="form-control rounded-pill" required>
    </div>

    <div class="col">
        <label class="form-label fw-bold small text-muted">PRECIO MÁXIMO</label>
        <input type="number" step="0.01" name="precio_max"
        class="form-control rounded-pill" required>
    </div>
</div>

<label class="form-label fw-bold small text-muted">TIPOS DE HABITACIÓN</label>
<select name="tipos[]" class="form-select rounded-4 mb-3" multiple required>
<?php while($t = mysqli_fetch_assoc($res_tipos)): ?>
<option value="<?php echo $t['id_tipo']; ?>">
<?php echo $t['nombre']; ?>
</option>
<?php endwhile; ?>
</select>

<label class="form-label fw-bold small text-muted">
IMÁGENES DEL HOTEL (PUEDES SELECCIONAR VARIAS)
</label>
<input type="file" name="imagen[]" class="form-control mb-4" accept="image/*" multiple>

<button class="btn btn-primary w-100 fw-bold">
Guardar Hotel
</button>

</form>

</div>

</div>
</div>

</body>
</html>