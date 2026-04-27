<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";

use Cloudinary\Api\Upload\UploadApi;

if (isset($_POST['ajax_type'])) {
    $id = intval($_POST['val_id'] ?? 0);

    if ($_POST['ajax_type'] == 'get_states') {
        $res = mysqli_query($config, "SELECT id, name FROM states WHERE country_id = $id ORDER BY name");
        echo '<option value="">Selecciona estado</option>';
        while ($r = mysqli_fetch_assoc($res)) {
            echo "<option value='{$r['id']}'>{$r['name']}</option>";
        }
    }

    if ($_POST['ajax_type'] == 'get_cities') {
        $res = mysqli_query($config, "SELECT id, name FROM cities WHERE state_id = $id ORDER BY name");
        if (mysqli_num_rows($res) > 0) {
            echo '<option value="">Selecciona ciudad</option>';
            while ($r = mysqli_fetch_assoc($res)) {
                echo "<option value='{$r['id']}'>{$r['name']}</option>";
            }
        } else {
            echo '<option value="">No se encontraron ciudades</option>';
        }
    }
    exit; 
}

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$uuid_hotel = mysqli_real_escape_string($config, $_GET['u'] ?? '');
$id_numerico = mysqli_real_escape_string($config, $_GET['id'] ?? '');
$propietario_uuid = $_SESSION['user_uuid'];

if ($uuid_hotel != '') {
    $sql = "SELECT * FROM catalogo WHERE uuid = '$uuid_hotel' AND propietario_uuid = '$propietario_uuid'";
} else {
    $sql = "SELECT * FROM catalogo WHERE id_catalogo = '$id_numerico' AND propietario_uuid = '$propietario_uuid'";
}

$res = mysqli_query($config, $sql);
$hotel = mysqli_fetch_assoc($res);

if (!$hotel) {
    die("Error: El hotel no existe o no tienes permiso para editarlo.");
}

$id = $hotel['id_catalogo'];

$res_ubicacion = mysqli_query($config, "SELECT * FROM cat_ubicacion WHERE id_catalogo = '$id'");
$ubicacion = mysqli_fetch_assoc($res_ubicacion);

$current_country = $ubicacion['country_id'] ?? 0;
$current_state = $ubicacion['state_id'] ?? 0;
$current_city = $ubicacion['city_id'] ?? 0;

if (isset($_GET['del_img'])) {
    $id_img = intval($_GET['del_img']);
    mysqli_query($config, "DELETE FROM cat_imagen WHERE id_imagen = '$id_img'");
    header("Location: editar.php?u=" . $uuid_hotel . "&id=" . $id);
    exit();
}

$paises = mysqli_query($config, "SELECT id, name FROM countries ORDER BY name");
$estados = mysqli_query($config, "SELECT id, name FROM states WHERE country_id = '$current_country' ORDER BY name");
$ciudades = mysqli_query($config, "SELECT id, name FROM cities WHERE state_id = '$current_state' ORDER BY name");

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['ajax_type'])) {
    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($config, $_POST['descripcion']);
    $pais_id = intval($_POST['pais_id']);
    $estado_id = intval($_POST['estado_id']);
    $ciudad_id = intval($_POST['ciudad_id']);
    $id_status = intval($_POST['id_status']);

    mysqli_query($config, "UPDATE catalogo SET nombre = '$nombre', descripcion = '$descripcion', id_status = '$id_status' WHERE id_catalogo = '$id'");
    
    mysqli_query($config, "UPDATE cat_ubicacion SET country_id = '$pais_id', state_id = '$estado_id', city_id = '$ciudad_id' WHERE id_catalogo = '$id'");

    if (isset($_FILES['imagen']) && !empty($_FILES['imagen']['name'][0])) {
        $upload = new UploadApi();
        foreach ($_FILES['imagen']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['imagen']['error'][$key] == 0) {
                $file_hash = md5_file($tmp_name);
                $result = $upload->upload($tmp_name, ['folder' => 'brooking_hoteles']);
                $url_final = $result['secure_url'];
                mysqli_query($config, "INSERT INTO cat_imagen (id_catalogo, url_imagen, hash_archivo, id_status) VALUES ('$id', '$url_final', '$file_hash', 1)");
            }
        }
    }
    header("Location: propietario_dashboard.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Hotel | 5to-travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow border-0 rounded-4 p-4">
        <h4 class="fw-bold mb-4">Editar Hotel</h4>

        <form method="POST" enctype="multipart/form-data">
            <label class="small fw-bold">Nombre del Hotel</label>
            <input type="text" name="nombre" class="form-control mb-3" value="<?php echo htmlspecialchars($hotel['nombre']); ?>" required>

            <label class="small fw-bold">Descripción</label>
            <textarea name="descripcion" class="form-control mb-3" rows="4"><?php echo htmlspecialchars($hotel['descripcion']); ?></textarea>

            <div class="row">
                <div class="col-md-4">
                    <label class="small fw-bold">País</label>
                    <select name="pais_id" id="pais" class="form-select mb-3" required>
                        <option value="">Selecciona país</option>
                        <?php while($p = mysqli_fetch_assoc($paises)): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $current_country) ? 'selected' : ''; ?>>
                                <?php echo $p['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold">Estado</label>
                    <select name="estado_id" id="estado" class="form-select mb-3" required>
                        <option value="">Selecciona estado</option>
                        <?php while($e = mysqli_fetch_assoc($estados)): ?>
                            <option value="<?php echo $e['id']; ?>" <?php echo ($e['id'] == $current_state) ? 'selected' : ''; ?>>
                                <?php echo $e['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold">Ciudad</label>
                    <select name="ciudad_id" id="ciudad" class="form-select mb-3" required>
                        <option value="">Selecciona ciudad</option>
                        <?php while($c = mysqli_fetch_assoc($ciudades)): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($c['id'] == $current_city) ? 'selected' : ''; ?>>
                                <?php echo $c['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="mb-4 mt-2">
                <label class="fw-bold">Galería Actual</label>
                <div class="d-flex flex-wrap gap-3 mt-2">
                    <?php
                    $res_gal = mysqli_query($config, "SELECT * FROM cat_imagen WHERE id_catalogo = '$id'");
                    while($img = mysqli_fetch_assoc($res_gal)):
                    ?>
                    <div class="position-relative">
                        <img src="<?php echo $img['url_imagen']; ?>" width="100" height="100" style="object-fit:cover;border-radius:10px;">
                        <a href="editar.php?del_img=<?php echo $img['id_imagen']; ?>&u=<?php echo $uuid_hotel; ?>&id=<?php echo $id; ?>" 
                           class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle"
                           style="width:22px;height:22px;padding:0;margin:-5px;"
                           onclick="return confirm('¿Eliminar imagen?')">
                           &times;
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <label class="small fw-bold">Subir nuevas imágenes</label>
            <input type="file" name="imagen[]" class="form-control mb-3" multiple>

            <label class="small fw-bold">Estado del Registro</label>
            <select name="id_status" class="form-select mb-4">
                <option value="1" <?php echo ($hotel['id_status']==1)?'selected':''; ?>>Activo</option>
                <option value="2" <?php echo ($hotel['id_status']==2)?'selected':''; ?>>Inactivo</option>
                <option value="7" <?php echo ($hotel['id_status']==7)?'selected':''; ?>>Mantenimiento</option>
            </select>

            <button class="btn btn-primary w-100 py-2 fw-bold shadow-sm">ACTUALIZAR DATOS</button>
        </form>
    </div>
</div>

<script>
const paisSelect = document.getElementById("pais");
const estadoSelect = document.getElementById("estado");
const ciudadSelect = document.getElementById("ciudad");

paisSelect.addEventListener("change", function() {
    const formData = new FormData();
    formData.append('ajax_type', 'get_states');
    formData.append('val_id', this.value);

    fetch('editar.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(data => {
            estadoSelect.innerHTML = data;
            ciudadSelect.innerHTML = '<option value="">Selecciona ciudad</option>';
        });
});

estadoSelect.addEventListener("change", function() {
    const formData = new FormData();
    formData.append('ajax_type', 'get_cities');
    formData.append('val_id', this.value);

    fetch('editar.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(data => {
            ciudadSelect.innerHTML = data;
        });
});
</script>

</body>
</html>