<?php
session_start();
include("../config/config.php");

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

            /* IMAGEN */
            if (!empty($_FILES['imagen']['name'])) {

                $carpeta = "../imagenes/hoteles/";
                if (!is_dir($carpeta)) {
                    mkdir($carpeta, 0777, true);
                }

                $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $nombre_img = "hotel_" . $id_hotel . "_" . time() . "." . $ext;

                $ruta = $carpeta . $nombre_img;
                $ruta_bd = "imagenes/hoteles/" . $nombre_img;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {

                    mysqli_query($config, "
                        INSERT INTO cat_imagen 
                        (id_catalogo, id_habitacion, url_imagen, id_status)
                        VALUES 
                        ('$id_hotel', NULL, '$ruta_bd', 1)
                    ");
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

<body class="bg-light">

<div class="container py-5">
<div class="col-md-6 mx-auto">

<div class="card shadow-lg border-0 rounded-4">

<!-- HEADER -->
<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <span>🏨 Agregar Hotel</span>
    <a href="propietario_dashboard.php" class="btn btn-light btn-sm">← Volver</a>
</div>

<div class="card-body">

<?php if($error != ""): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<!-- NOMBRE -->
<input type="text" name="nombre" class="form-control mb-3"
placeholder="Nombre del hotel" required>

<!-- DESCRIPCIÓN -->
<textarea name="descripcion" class="form-control mb-3"
placeholder="Descripción del hotel" required></textarea>

<!-- PRECIO -->
<div class="row">
    <div class="col">
        <input type="number" step="0.01" name="precio_min"
        class="form-control mb-3"
        placeholder="Precio mínimo" required>
    </div>
    <div class="col">
        <input type="number" step="0.01" name="precio_max"
        class="form-control mb-3"
        placeholder="Precio máximo" required>
    </div>
</div>

<!-- TIPOS -->
<label class="form-label">Tipos de habitación</label>
<select name="tipos[]" class="form-select mb-3" multiple required>
<?php while($t = mysqli_fetch_assoc($res_tipos)): ?>
<option value="<?php echo $t['id_tipo']; ?>">
<?php echo $t['nombre']; ?>
</option>
<?php endwhile; ?>
</select>

<!-- IMAGEN -->
<label class="form-label">Imagen del hotel</label>
<input type="file" name="imagen" class="form-control mb-3" accept="image/*">

<button class="btn btn-primary w-100 rounded-3">
Guardar Hotel
</button>

</form>

</div>
</div>

</div>
</div>

</body>
</html>