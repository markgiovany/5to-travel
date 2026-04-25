<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";

use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$uuid_hotel = $_GET['u'] ?? '';
$id_numerico = $_GET['id'] ?? '';
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

// --- LOGICA CORREGIDA PARA ELIMINAR ERRORES ---
$id = $hotel['id_catalogo'];
$id_pais_actual = $hotel['pais_id'] ?? 0; // Se define ANTES de usarlo en las consultas

// Consulta de dueños corregida
$query_duenos = "SELECT uuid, first_name, last_name FROM usr_users ORDER BY first_name ASC";
$res_duenos = mysqli_query($config, $query_duenos);

// Consultas de ubicación
$paises = mysqli_query($config, "SELECT id, name FROM countries ORDER BY name ASC");
$res_ubicaciones = mysqli_query($config, "SELECT id, name FROM states WHERE country_id = '$id_pais_actual' ORDER BY name ASC");
    
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($config, $_POST['descripcion']);
    $id_pais = intval($_POST['pais_id']);
    $id_ubicacion = intval($_POST['id_ubicacion']); 
    $ciudad = mysqli_real_escape_string($config, $_POST['ciudad']);
    $direccion = mysqli_real_escape_string($config, $_POST['direccion']);
    $id_status = intval($_POST['id_status']);
    $nuevo_propietario = mysqli_real_escape_string($config, $_POST['propietario_uuid']);

    $sql_update = "UPDATE catalogo SET 
        nombre = '$nombre', 
        descripcion = '$descripcion', 
        pais_id = '$id_pais',
        id_ubicacion = '$id_ubicacion', 
        ciudad = '$ciudad',
        direccion = '$direccion',
        id_status = '$id_status',
        propietario_uuid = '$nuevo_propietario',
        update_at = NOW() 
        WHERE id_catalogo = '$id'";

    mysqli_query($config, $sql_update);

    /* MANEJO DE IMÁGENES (CLOUDINARY) */
    if (isset($_FILES['imagen']) && !empty($_FILES['imagen']['name'][0])) {
        $upload = new UploadApi();
        foreach ($_FILES['imagen']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['imagen']['error'][$key] == 0) {
                $file_hash = md5_file($tmp_name);
                $result = $upload->upload($tmp_name, ['folder' => 'brooking_hoteles']);
                $url_final = $result['secure_url'];
                mysqli_query($config, "INSERT INTO cat_imagen (id_catalogo, url_imagen, hash_archivo) VALUES ('$id', '$url_final', '$file_hash')");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow border-0 rounded-4 p-4">
        <h4 class="fw-bold mb-4">Editar Hotel</h4>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre del Hotel</label>
                <input type="text" name="nombre" class="form-control" value="<?php echo $hotel['nombre']; ?>" required>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">PAÍS</label>
                    <select name="pais_id" class="form-select">
                        <?php while($p = mysqli_fetch_assoc($paises)): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $id_pais_actual) ? 'selected' : ''; ?>>
                                <?php echo $p['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">ESTADO</label>
                    <select name="id_ubicacion" class="form-select">
                        <?php while($e = mysqli_fetch_assoc($res_ubicaciones)): ?>
                            <option value="<?php echo $e['id']; ?>" <?php echo ($e['id'] == $hotel['id_ubicacion']) ? 'selected' : ''; ?>>
                                <?php echo $e['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">CIUDAD</label>
                    <input type="text" name="ciudad" class="form-control" value="<?php echo $hotel['ciudad'] ?? ''; ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">DIRECCIÓN</label>
                <input type="text" name="direccion" class="form-control" value="<?php echo $hotel['direccion'] ?? ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">DESCRIPCIÓN</label>
                <textarea name="descripcion" class="form-control" rows="3"><?php echo $hotel['descripcion']; ?></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label text-primary fw-bold">GALERÍA ACTUAL</label>
                <div class="p-3 border rounded-3 bg-white d-flex flex-wrap gap-3">
                    <?php 
                    $res_gal = mysqli_query($config, "SELECT * FROM cat_imagen WHERE id_catalogo = '$id' AND id_habitacion IS NULL");
                    while($img = mysqli_fetch_assoc($res_gal)): ?>
                        <div class="position-relative">
                            <img src="<?php echo $img['url_imagen']; ?>" width="100" height="100" class="rounded object-fit-cover shadow-sm">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle" style="width:22px; height:22px; padding:0; margin:-5px;">&times;</button>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-primary">Subir nuevas imágenes</label>
                <input type="file" name="imagen[]" class="form-control" multiple>
            </div>

            <div class="row align-items-end mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">DUEÑO DEL HOTEL</label>
                    <select name="propietario_uuid" class="form-select">
                        <?php 
                        mysqli_data_seek($res_duenos, 0);
                        while($d = mysqli_fetch_assoc($res_duenos)): ?>
                            <option value="<?php echo $d['uuid']; ?>" <?php echo ($d['uuid'] == $hotel['propietario_uuid']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['first_name'] . " " . $d['last_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">ESTADO OPERATIVO</label>
                    <select name="id_status" class="form-select">
                        <option value="1" <?php echo ($hotel['id_status'] == 1) ? 'selected' : ''; ?>>Activo</option>
                        <option value="2" <?php echo ($hotel['id_status'] == 2) ? 'selected' : ''; ?>>Inactivo</option>
                        <option value="7" <?php echo ($hotel['id_status'] == 7) ? 'selected' : ''; ?>>Fuera de servicio (Mantenimiento)</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="propietario_dashboard.php" class="btn btn-light px-4 rounded-pill">CANCELAR</a>
                <button type="submit" class="btn btn-primary px-4 rounded-pill">ACTUALIZAR INFORMACIÓN</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>