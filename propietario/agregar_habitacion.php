<?php
session_start();
include("../config/config.php");

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

    $uuid = uniqid();
    $numero = $_POST['numero'];
    $id_catalogo = $_POST['id_catalogo'];
    $id_tipo = $_POST['id_tipo'];
    $precio = $_POST['precio'];
    $id_status = $_POST['id_status'];

    /* 1. INSERT HABITACIÓN (PRIMERO) */
    $insert = mysqli_query($config, "
        INSERT INTO res_habitacion 
        (uuid_habitacion, numero, id_catalogo, precio, id_status)
        VALUES 
        ('$uuid', '$numero', '$id_catalogo', '$precio', '$id_status')
    ");

    if ($insert) {

        $id_habitacion = mysqli_insert_id($config);

        /* 2. RELACIÓN TIPO */
        mysqli_query($config, "
            INSERT INTO cat_catalogo_habitacion (id_catalogo, id_tipo)
            VALUES ('$id_catalogo', '$id_tipo')
        ");

        /* 3. IMAGEN (YA CON ID CORRECTO) */
        if (!empty($_FILES['imagen']['name'])) {

            $carpeta = "../imagenes/habitaciones/";
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0777, true);
            }

            $nombreImg = time() . "_" . basename($_FILES["imagen"]["name"]);
            $ruta = $carpeta . $nombreImg;

            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta)) {

                mysqli_query($config, "
                    INSERT INTO cat_imagen 
                    (id_catalogo, id_habitacion, url_imagen, id_status)
                    VALUES 
                    ('$id_catalogo', '$id_habitacion', 'imagenes/habitaciones/$nombreImg', 1)
                ");
            }
        }

        header("Location: agregar_habitacion.php?ok=1");
        exit();
    } else {
        echo "Error: " . mysqli_error($config);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Agregar Habitación PRO</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

<h3 class="mb-3">🏨 Agregar Habitación</h3>

<a href="propietario_dashboard.php" class="btn btn-outline-primary mb-3">
← Volver al Dashboard
</a>

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

<!-- NÚMERO -->
<input type="number" name="numero" class="form-control mb-3" placeholder="Número de habitación" required>

<!-- PRECIO -->
<input type="number" step="0.01" name="precio" class="form-control mb-3" placeholder="Precio" required>

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
<input type="file" name="imagen" class="form-control mb-3">

<button class="btn btn-success w-100">Guardar habitación</button>

</form>

</div>

</body>
</html>