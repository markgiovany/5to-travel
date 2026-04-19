<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";

use Cloudinary\Api\Upload\UploadApi;

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

/* TIPOS SELECCIONADOS */
$sql_selected = "SELECT id_tipo FROM cat_catalogo_habitacion WHERE id_catalogo='$id'";
$res_selected = mysqli_query($config, $sql_selected);

$selected = [];
while ($s = mysqli_fetch_assoc($res_selected)) {
    $selected[] = $s['id_tipo'];
}

/* UPDATE */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($config, $_POST['descripcion']);

    mysqli_query($config, "
        UPDATE catalogo 
        SET nombre='$nombre', descripcion='$descripcion'
        WHERE id_catalogo='$id'
    ");

    mysqli_query($config, "DELETE FROM cat_catalogo_habitacion WHERE id_catalogo='$id'");

    if (!empty($_POST['tipos'])) {
        foreach ($_POST['tipos'] as $tipo) {
            mysqli_query($config, "
                INSERT INTO cat_catalogo_habitacion (id_catalogo, id_tipo)
                VALUES ('$id', '$tipo')
            ");
        }
    }

    /* IMAGEN CLOUDINARY */
    if (isset($_FILES['imagen']) && !empty($_FILES['imagen']['name'])) {

        $upload = new UploadApi();

        $tmp = $_FILES['imagen']['tmp_name'];
        $file_hash = md5_file($tmp);

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
            $result = $upload->upload($tmp, [
                'folder' => 'brooking_hoteles'
            ]);
            $url_final = $result['secure_url'];
        }

        mysqli_query($config, "
            INSERT INTO cat_imagen 
            (id_catalogo, url_imagen, hash_archivo)
            VALUES 
            ('$id', '$url_final', '$file_hash')
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

<body class="bg-light p-5">

<div class="container">

<div class="col-md-7 mx-auto card shadow-sm border-0 p-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">Editar Hotel</h4>
    <a href="propietario_dashboard.php" class="btn btn-outline-primary btn-sm">← Volver</a>
</div>

<form method="POST" enctype="multipart/form-data">

<!-- NOMBRE -->
<div class="mb-3">
<label class="form-label fw-bold small text-muted">NOMBRE DEL HOTEL</label>
<input type="text" name="nombre"
value="<?php echo htmlspecialchars($hotel['nombre']); ?>"
class="form-control rounded-pill" required>
</div>

<!-- DESCRIPCIÓN -->
<div class="mb-3">
<label class="form-label fw-bold small text-muted">DESCRIPCIÓN</label>
<textarea name="descripcion" class="form-control rounded-4" rows="3" required><?php echo htmlspecialchars($hotel['descripcion']); ?></textarea>
</div>

<!-- TIPOS -->
<div class="mb-3">
<label class="form-label fw-bold small text-muted">TIPOS DE HABITACIÓN</label>

<select name="tipos[]" class="form-select rounded-4" multiple required>
<?php while($t = mysqli_fetch_assoc($res_tipos)): ?>
<option value="<?php echo $t['id_tipo']; ?>"
<?php echo in_array($t['id_tipo'], $selected) ? 'selected' : ''; ?>>
    <?php echo $t['nombre']; ?>
</option>
<?php endwhile; ?>
</select>
</div>

<!-- IMAGEN -->
<div class="mb-4">
<label class="form-label fw-bold small text-muted">IMAGEN (CLOUDINARY)</label>
<input type="file" name="imagen" class="form-control" accept="image/*">
</div>

<div class="d-grid">
<button class="btn btn-primary w-100 fw-bold">
Guardar Cambios
</button>
</div>

</form>

</div>

</div>

</body>
</html>