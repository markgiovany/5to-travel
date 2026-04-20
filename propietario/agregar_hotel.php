<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";

use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

/* 🔥 UUID v4 REAL */
function generar_uuid_v4() {
    $data = random_bytes(16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$propietario_uuid = $_SESSION['user_uuid'];

/* PAISES */
$paises = mysqli_query($config, "SELECT id, name FROM countries ORDER BY name");

/* ESTADOS */
$estados = mysqli_query($config, "SELECT id, name, country_id FROM states ORDER BY name");

/* CIUDADES */
$ciudades = mysqli_query($config, "SELECT id, name, state_id, country_id FROM cities ORDER BY name");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim(mysqli_real_escape_string($config, $_POST['nombre']));
    $descripcion = trim(mysqli_real_escape_string($config, $_POST['descripcion']));

    $pais_id = intval($_POST['pais_id']);
    $estado_id = intval($_POST['estado_id']);
    $ciudad_id = intval($_POST['ciudad_id']);

    if (empty($nombre) || empty($descripcion)) {
        $error = "Completa todos los campos";
    } elseif (!$pais_id || !$estado_id || !$ciudad_id) {
        $error = "Debes seleccionar país, estado y ciudad";
    } else {

        /* 1. INSERT HOTEL */
        $sql = "
            INSERT INTO catalogo 
            (propietario_uuid, nombre, descripcion)
            VALUES 
            ('$propietario_uuid', '$nombre', '$descripcion')
        ";

        if (mysqli_query($config, $sql)) {

            $id_hotel = mysqli_insert_id($config);

            /* 2. UBICACIÓN CON UUID REAL */
            $uuid_ubicacion = generar_uuid_v4();

            $direccion = $descripcion;

            mysqli_query($config, "
                INSERT INTO cat_ubicacion
                (
                    uuid_ubicacion,
                    direccion,
                    id_status,
                    country_id,
                    state_id,
                    city_id,
                    id_catalogo
                )
                VALUES
                (
                    '$uuid_ubicacion',
                    '$direccion',
                    1,
                    '$pais_id',
                    '$estado_id',
                    '$ciudad_id',
                    '$id_hotel'
                )
            ");

            /* 3. IMÁGENES */
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
</head>

<body class="bg-light p-5">

<div class="container col-md-6">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">🏨 Agregar Hotel</h4>
    <a href="propietario_dashboard.php" class="btn btn-outline-secondary btn-sm">
        ← Volver
    </a>
</div>

<?php if($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="nombre" class="form-control mb-3" placeholder="Nombre" required>

<textarea name="descripcion" class="form-control mb-3" placeholder="Descripción" required></textarea>

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
<select name="estado_id" id="estado" class="form-select mb-3" disabled required>
<option value="">Selecciona estado</option>
<?php while($e = mysqli_fetch_assoc($estados)): ?>
<option value="<?php echo $e['id']; ?>" data-country="<?php echo $e['country_id']; ?>">
<?php echo $e['name']; ?>
</option>
<?php endwhile; ?>
</select>

<!-- CIUDAD -->
<select name="ciudad_id" id="ciudad" class="form-select mb-3" disabled required>
<option value="">Selecciona ciudad</option>
<?php while($c = mysqli_fetch_assoc($ciudades)): ?>
<option value="<?php echo $c['id']; ?>" 
        data-state="<?php echo $c['state_id']; ?>" 
        data-country="<?php echo $c['country_id']; ?>">
<?php echo $c['name']; ?>
</option>
<?php endwhile; ?>
</select>

<input type="file" name="imagen[]" class="form-control mb-4" accept="image/*" multiple>

<button class="btn btn-primary w-100">Guardar Hotel</button>

</form>

</div>

<script>
const pais = document.getElementById("pais");
const estado = document.getElementById("estado");
const ciudad = document.getElementById("ciudad");

estado.disabled = true;
ciudad.disabled = true;

pais.addEventListener("change", function() {

    let pais_id = this.value;

    estado.disabled = !pais_id;
    ciudad.disabled = true;

    estado.value = "";
    ciudad.value = "";

    for (let opt of estado.options) {
        if (opt.value === "") continue;
        opt.style.display = (opt.dataset.country == pais_id) ? "block" : "none";
    }

    for (let opt of ciudad.options) {
        if (opt.value === "") continue;
        opt.style.display = "none";
    }
});

estado.addEventListener("change", function() {

    let estado_id = this.value;
    let pais_id = pais.value;

    ciudad.disabled = !estado_id;
    ciudad.value = "";

    for (let opt of ciudad.options) {
        if (opt.value === "") continue;

        opt.style.display = (
            opt.dataset.state == estado_id &&
            opt.dataset.country == pais_id
        ) ? "block" : "none";
    }
});
</script>

</body>
</html>