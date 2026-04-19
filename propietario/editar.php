<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$propietario_uuid = $_SESSION['user_uuid'];

/* HOTEL */
$sql = "SELECT * FROM catalogo WHERE id_catalogo='$id' AND propietario_uuid='$propietario_uuid'";
$res = mysqli_query($config, $sql);
$hotel = mysqli_fetch_assoc($res);

if (!$hotel) {
    die("Hotel no encontrado");
}

/* TIPOS */
$sql_tipos = "SELECT * FROM cat_tipo";
$res_tipos = mysqli_query($config, $sql_tipos);

/* TIPOS YA ASIGNADOS */
$sql_selected = "
SELECT id_tipo 
FROM cat_catalogo_habitacion 
WHERE id_catalogo='$id'
";
$res_selected = mysqli_query($config, $sql_selected);

$selected = [];
while ($s = mysqli_fetch_assoc($res_selected)) {
    $selected[] = $s['id_tipo'];
}

/* ACTUALIZAR */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($config, $_POST['descripcion']);

    mysqli_query($config, "
        UPDATE catalogo 
        SET nombre='$nombre', descripcion='$descripcion'
        WHERE id_catalogo='$id'
    ");

    /* BORRAR TIPOS ANTERIORES */
    mysqli_query($config, "DELETE FROM cat_catalogo_habitacion WHERE id_catalogo='$id'");

    /* INSERTAR NUEVOS TIPOS */
    foreach ($_POST['tipos'] as $tipo) {
        mysqli_query($config, "
            INSERT INTO cat_catalogo_habitacion (id_catalogo, id_tipo)
            VALUES ('$id', '$tipo')
        ");
    }

    /* IMAGEN (OPCIONAL) */
    if (!empty($_FILES['imagen']['name'])) {

        $nombre_img = time() . "_" . basename($_FILES['imagen']['name']);
        $tmp = $_FILES['imagen']['tmp_name'];

        $ruta = "../imagenes/" . $nombre_img;
        $bd = "imagenes/" . $nombre_img;

        move_uploaded_file($tmp, $ruta);

        mysqli_query($config, "
            INSERT INTO cat_imagen (id_catalogo, url_imagen)
            VALUES ('$id', '$bd')
        ");
    }

    header("Location: propietario_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Hotel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card shadow">

<div class="card-header bg-primary text-white">
    Editar Hotel
</div>

<div class="card-body">

<form method="POST" enctype="multipart/form-data">

<!-- NOMBRE -->
<div class="mb-3">
<label>Nombre</label>
<input type="text" name="nombre"
value="<?php echo htmlspecialchars($hotel['nombre']); ?>"
class="form-control" required>
</div>

<!-- DESCRIPCIÓN -->
<div class="mb-3">
<label>Descripción</label>
<textarea name="descripcion" class="form-control" required><?php echo htmlspecialchars($hotel['descripcion']); ?></textarea>
</div>

<!-- TIPOS -->
<div class="mb-3">
<label>Tipos de habitación</label>

<select name="tipos[]" class="form-select" multiple required>
<?php while($t = mysqli_fetch_assoc($res_tipos)): ?>
<option value="<?php echo $t['id_tipo']; ?>"
<?php echo in_array($t['id_tipo'], $selected) ? 'selected' : ''; ?>>
    <?php echo $t['nombre']; ?>
</option>
<?php endwhile; ?>
</select>
</div>

<!-- IMAGEN -->
<div class="mb-3">
<label>Actualizar Imagen (opcional)</label>
<input type="file" name="imagen" class="form-control">
</div>

<button class="btn btn-primary w-100">
    Guardar Cambios
</button>

</form>

</div>

</div>

</div>

</div>

</div>

</body>
</html>