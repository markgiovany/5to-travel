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
$res_tipos = mysqli_query($config, "SELECT * FROM cat_tipo");

/* TIPOS SELECCIONADOS */
$res_selected = mysqli_query($config, "SELECT id_tipo FROM cat_catalogo_habitacion WHERE id_catalogo='$id'");
$selected = [];

while ($s = mysqli_fetch_assoc($res_selected)) {
    $selected[] = $s['id_tipo'];
}

/* PAISES */
$paises = mysqli_query($config, "SELECT id, name FROM countries ORDER BY name ASC");

/* ESTADOS */
$estados = mysqli_query($config, "SELECT id, name, country_id FROM states ORDER BY name ASC");

/* UPDATE */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($config, $_POST['descripcion']);

    $pais_id = intval($_POST['pais_id']);
    $estado_id = intval($_POST['estado_id']);

    mysqli_query($config, "
        UPDATE catalogo 
        SET nombre='$nombre', 
            descripcion='$descripcion',
            pais_id='$pais_id',
            estado_id='$estado_id'
        WHERE id_catalogo='$id'
    ");

    /* TIPOS */
    mysqli_query($config, "DELETE FROM cat_catalogo_habitacion WHERE id_catalogo='$id'");

    if (!empty($_POST['tipos'])) {
        foreach ($_POST['tipos'] as $tipo) {
            mysqli_query($config, "
                INSERT INTO cat_catalogo_habitacion (id_catalogo, id_tipo)
                VALUES ('$id', '$tipo')
            ");
        }
    }

    /* IMAGEN */
    if (isset($_FILES['imagen']) && !empty($_FILES['imagen']['name'])) {

        $upload = new UploadApi();
        $tmp = $_FILES['imagen']['tmp_name'];

        if ($_FILES['imagen']['error'] == 0) {

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
<textarea name="descripcion" class="form-control rounded-4" required><?php echo htmlspecialchars($hotel['descripcion']); ?></textarea>
</div>

<!-- PAIS -->
<div class="mb-3">
<label class="form-label fw-bold small text-muted">PAÍS</label>
<select name="pais_id" id="pais" class="form-select" required>
<option value="">Selecciona país</option>
<?php while($p = mysqli_fetch_assoc($paises)): ?>
<option value="<?php echo $p['id']; ?>"
<?php echo (isset($hotel['pais_id']) && $hotel['pais_id'] == $p['id']) ? 'selected' : ''; ?>>
<?php echo $p['name']; ?>
</option>
<?php endwhile; ?>
</select>
</div>

<!-- ESTADO -->
<div class="mb-3">
<label class="form-label fw-bold small text-muted">ESTADO</label>
<select name="estado_id" id="estado" class="form-select" required>
<option value="">Selecciona estado</option>
<?php while($e = mysqli_fetch_assoc($estados)): ?>
<option value="<?php echo $e['id']; ?>" 
data-country="<?php echo $e['country_id']; ?>"
<?php echo (isset($hotel['estado_id']) && $hotel['estado_id'] == $e['id']) ? 'selected' : ''; ?>>
<?php echo $e['name']; ?>
</option>
<?php endwhile; ?>
</select>
</div>

<!-- TIPOS -->
<div class="mb-3">
<label class="form-label fw-bold small text-muted">TIPOS DE HABITACIÓN</label>
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
<div class="mb-4">
<label class="form-label fw-bold small text-muted">IMAGEN</label>
<input type="file" name="imagen" class="form-control" accept="image/*">
</div>

<button class="btn btn-primary w-100">
Guardar Cambios
</button>

</form>

</div>
</div>

<!-- JS FILTRO -->
<script>
document.addEventListener("DOMContentLoaded", function(){

    const pais = document.getElementById("pais");
    const estado = document.getElementById("estado");

    const estadosOriginal = Array.from(estado.options);
    const estadoSeleccionado = "<?php echo $hotel['estado_id']; ?>";

    function filtrarEstados() {
        let pais_id = pais.value;

        estado.innerHTML = '<option value="">Selecciona estado</option>';

        estadosOriginal.forEach(opt => {
            if(opt.dataset && opt.dataset.country == pais_id){
                let nuevo = opt.cloneNode(true);

                // 🔥 Mantener seleccionado
                if(nuevo.value == estadoSeleccionado){
                    nuevo.selected = true;
                }

                estado.appendChild(nuevo);
            }
        });
    }

    // 🔥 IMPORTANTE: ejecutar al cargar
    filtrarEstados();

    // 🔁 Cuando cambia país
    pais.addEventListener("change", function(){
        filtrarEstados();
    });

});
</script>

</body>
</html>