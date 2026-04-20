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

$hoteles = mysqli_query($config, "
    SELECT id_catalogo, nombre 
    FROM catalogo 
    WHERE propietario_uuid = '$propietario_uuid'
");

$tipos = mysqli_query($config, "SELECT * FROM cat_tipo");
$estados = mysqli_query($config, "SELECT * FROM status");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_catalogo = intval($_POST['id_catalogo']);
    $id_tipo = intval($_POST['id_tipo']);
    $precio = floatval($_POST['precio']);
    $id_status = intval($_POST['id_status']);
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;

    /* 🔥 VALIDACIÓN SEGURA (EVITA ERROR FK) */
    $checkHotel = mysqli_query($config, "
        SELECT id_catalogo 
        FROM catalogo 
        WHERE id_catalogo = '$id_catalogo'
        LIMIT 1
    ");

    if (mysqli_num_rows($checkHotel) == 0) {
        die("❌ Error: el hotel seleccionado no existe o no es válido.");
    }

    if ($cantidad <= 0) {
        $error = "La cantidad debe ser mayor a 0";
    } else {

        mysqli_begin_transaction($config);

        try {

            $ids_habitaciones = [];
            $upload = new UploadApi();

            for ($i = 0; $i < $cantidad; $i++) {

                $uuid = uniqid();
                $numero = "HAB-" . time() . "-" . ($i + 1);

                $insert = mysqli_query($config, "
                    INSERT INTO res_habitacion 
                    (uuid_habitacion, id_catalogo, numero, precio, id_status)
                    VALUES 
                    ('$uuid', '$id_catalogo', '$numero', '$precio', '$id_status')
                ");

                if (!$insert) {
                    throw new Exception("Error al crear habitación #" . ($i + 1));
                }

                $id_habitacion = mysqli_insert_id($config);
                $ids_habitaciones[] = $id_habitacion;

                /* relación tipo */
                mysqli_query($config, "
                    INSERT INTO cat_catalogo_habitacion (id_catalogo, id_tipo)
                    VALUES ('$id_catalogo', '$id_tipo')
                ");
            }

            /* 🖼 IMAGEN (CLOUDINARY + CACHE HASH) */
            if (!empty($_FILES['imagen']['name'])) {

                $tmp_name = $_FILES['imagen']['tmp_name'];

                if ($_FILES['imagen']['error'] == 0) {

                    $file_hash = md5_file($tmp_name);

                    $check_img = mysqli_query($config, "
                        SELECT url_imagen 
                        FROM cat_imagen 
                        WHERE hash_archivo = '$file_hash' 
                        LIMIT 1
                    ");

                    if (mysqli_num_rows($check_img) > 0) {
                        $img_data = mysqli_fetch_assoc($check_img);
                        $url_final = $img_data['url_imagen'];
                    } else {
                        $resultado = $upload->upload($tmp_name, [
                            'folder' => 'habitaciones_propietario'
                        ]);
                        $url_final = $resultado['secure_url'];
                    }

                    foreach ($ids_habitaciones as $id_hab) {

                        mysqli_query($config, "
                            INSERT INTO cat_imagen 
                            (id_catalogo, id_habitacion, url_imagen, hash_archivo, id_status)
                            VALUES 
                            ('$id_catalogo', '$id_hab', '$url_final', '$file_hash', 1)
                        ");
                    }
                }
            }

            mysqli_commit($config);

            header("Location: agregar_habitacion.php?ok=1");
            exit();

        } catch (Exception $e) {

            mysqli_rollback($config);
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar Habitación</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

<h3 class="mb-3">🏨 Agregar Habitación PRO</h3>

<a href="propietario_dashboard.php" class="btn btn-outline-primary mb-3">
← Volver al Dashboard
</a>

<?php if(isset($error)): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<!-- HOTEL -->
<select name="id_catalogo" class="form-select mb-3" required>
<option value="">Selecciona hotel</option>
<?php while($h = mysqli_fetch_assoc($hoteles)): ?>
<option value="<?php echo $h['id_catalogo']; ?>">
<?php echo $h['nombre']; ?>
</option>
<?php endwhile; ?>
</select>

<!-- TIPO -->
<select name="id_tipo" class="form-select mb-3" required>
<option value="">Tipo de habitación</option>
<?php while($t = mysqli_fetch_assoc($tipos)): ?>
<option value="<?php echo $t['id_tipo']; ?>">
<?php echo $t['nombre']; ?>
</option>
<?php endwhile; ?>
</select>

<!-- PRECIO -->
<input type="number" step="0.01" name="precio" class="form-control mb-3" placeholder="Precio" required>

<!-- CANTIDAD -->
<label class="form-label fw-bold small text-muted">
CANTIDAD DE HABITACIONES
</label>
<input type="number" name="cantidad" class="form-control mb-3" value="1" required>

<!-- STATUS -->
<select name="id_status" class="form-select mb-3" required>
<option value="">Estado</option>
<?php while($s = mysqli_fetch_assoc($estados)): ?>
<option value="<?php echo $s['id_status']; ?>">
<?php echo $s['nombre']; ?>
</option>
<?php endwhile; ?>
</select>

<!-- IMAGEN -->
<input type="file" name="imagen" class="form-control mb-3" accept="image/*">

<button class="btn btn-success w-100">
Guardar habitaciones
</button>

</form>

</div>

</body>
</html>