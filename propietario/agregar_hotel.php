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

/* PAISES */
$paises = mysqli_query($config, "SELECT id, name FROM countries ORDER BY name");

/* ESTADOS */
$estados = mysqli_query($config, "SELECT id, name, country_id FROM states ORDER BY name");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim(mysqli_real_escape_string($config, $_POST['nombre']));
    $descripcion = trim(mysqli_real_escape_string($config, $_POST['descripcion']));
    $precio_min = floatval($_POST['precio_min']);
    $precio_max = floatval($_POST['precio_max']);
    $tipos = $_POST['tipos'] ?? [];

    if ($precio_min < 0 || $precio_max < 0) {
        $error = "Precios inválidos";
    } elseif ($precio_min > $precio_max) {
        $error = "Precio mínimo mayor al máximo";
    } elseif (empty($nombre) || empty($descripcion)) {
        $error = "Completa todos los campos";
    } else {

        /* INSERT HOTEL (SIN pais_id NI estado_id) */
        $sql = "
            INSERT INTO catalogo 
            (propietario_uuid, nombre, descripcion, precio_min, precio_max)
            VALUES 
            ('$propietario_uuid', '$nombre', '$descripcion', '$precio_min', '$precio_max')
        ";

        if (mysqli_query($config, $sql)) {

            $id_hotel = mysqli_insert_id($config);

            /* TIPOS */
            foreach ($tipos as $id_tipo) {
                mysqli_query($config, "
                    INSERT INTO cat_catalogo_habitacion (id_catalogo, id_tipo)
                    VALUES ('$id_hotel', '$id_tipo')
                ");
            }

            /* 🔥 IMÁGENES CLOUDINARY */
            if (!empty($_FILES['imagen']['name'][0])) {

                $upload = new UploadApi();

                foreach ($_FILES['imagen']['tmp_name'] as $key => $tmp_name) {

                    if ($_FILES['imagen']['error'][$key] == 0) {

                        $file_hash = md5_file($tmp_name);

                        /* EVITA DUPLICADOS */
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
</head>

<body class="bg-light p-5">

<div class="container col-md-6">

<h4 class="mb-4">🏨 Agregar Hotel</h4>

<?php if($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="nombre" class="form-control mb-3" placeholder="Nombre" required>

<textarea name="descripcion" class="form-control mb-3" placeholder="Descripción" required></textarea>

<div class="row mb-3">
<div class="col">
<input type="number" name="precio_min" class="form-control" placeholder="Precio min" required>
</div>
<div class="col">
<input type="number" name="precio_max" class="form-control" placeholder="Precio max" required>
</div>
</div>

<!-- PAIS -->
<select name="pais_id" id="pais" class="form-select mb-3" required>
<option value="">Selecciona país</option>
<?php while($p = mysqli_fetch_assoc($paises)): ?>
<option value="<?php echo $p['id']; ?>">
<?php echo $p['name']; ?>
</option>
<?php endwhile; ?>
</select>

<!-- ESTADO -->
<select name="estado_id" id="estado" class="form-select mb-3" required>
<option value="">Selecciona estado</option>
<?php while($e = mysqli_fetch_assoc($estados)): ?>
<option value="<?php echo $e['id']; ?>" data-country="<?php echo $e['country_id']; ?>">
<?php echo $e['name']; ?>
</option>
<?php endwhile; ?>
</select>

<!-- TIPOS -->
<select name="tipos[]" class="form-select mb-3" multiple required>
<?php while($t = mysqli_fetch_assoc($res_tipos)): ?>
<option value="<?php echo $t['id_tipo']; ?>">
<?php echo $t['nombre']; ?>
</option>
<?php endwhile; ?>
</select>

<!-- 🔥 IMÁGENES -->
<label class="form-label fw-bold small text-muted">
IMÁGENES DEL HOTEL
</label>
<input type="file" name="imagen[]" class="form-control mb-4" accept="image/*" multiple>

<button class="btn btn-primary w-100">
Guardar Hotel
</button>

</form>

</div>

<!-- JS FILTRO -->
<script>
document.getElementById("pais").addEventListener("change", function() {
    let pais_id = this.value;
    let estados = document.getElementById("estado").options;

    for (let opt of estados) {
        if (opt.value === "") continue;
        opt.style.display = (opt.dataset.country == pais_id) ? "block" : "none";
    }

    document.getElementById("estado").value = "";
});
</script>

</body>
</html>