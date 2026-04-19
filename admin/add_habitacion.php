<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";
use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['u'])) {
    header("Location: hoteles.php");
    exit();
}

$uuid_hotel = mysqli_real_escape_string($config, $_GET['u']);
$res_h = mysqli_query($config, "SELECT id_catalogo, nombre FROM catalogo WHERE uuid = '$uuid_hotel'");
$hotel = mysqli_fetch_assoc($res_h);

if (!$hotel) {
    header("Location: hoteles.php");
    exit();
}

$res_tipos = mysqli_query($config, "SELECT id_tipo, nombre FROM cat_tipo ORDER BY nombre ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_hotel_db = $hotel['id_catalogo'];
    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($config, $_POST['descripcion']);
    $precio = mysqli_real_escape_string($config, $_POST['precio']);
    $capacidad = mysqli_real_escape_string($config, $_POST['capacidad']);
    $disponibilidad = mysqli_real_escape_string($config, $_POST['disponibilidad']);
    $id_tipo = mysqli_real_escape_string($config, $_POST['id_tipo']);

    mysqli_begin_transaction($config);

    try {
        // 1. Crear habitación
        $query_hab = "INSERT INTO cat_catalogo_habitacion 
                      (id_catalogo, uuid, id_tipo, nombre, descripcion, precio, capacidad, disponibilidad, id_status) 
                      VALUES 
                      ('$id_hotel_db', uuid(), '$id_tipo', '$nombre', '$descripcion', '$precio', '$capacidad', '$disponibilidad', 1)";
        
        if (!mysqli_query($config, $query_hab)) throw new Exception("Error al crear habitación");
        $id_hab_nuevo = mysqli_insert_id($config);

        // 2. Procesar fotos con lógica de "No Duplicados"
        if (isset($_FILES['foto']) && !empty($_FILES['foto']['name'][0])) {
            $upload = new UploadApi();
            
            foreach ($_FILES['foto']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['foto']['error'][$key] == 0) {
                    
                    // CALCULAR HASH (Huella única del archivo)
                    $file_hash = md5_file($tmp_name);

                    // BUSCAR SI YA EXISTE ESTA IMAGEN
                    $check_img = mysqli_query($config, "SELECT url_imagen FROM cat_imagen WHERE hash_archivo = '$file_hash' LIMIT 1");
                    
                    if (mysqli_num_rows($check_img) > 0) {
                        // YA EXISTE: Reutilizamos la URL
                        $img_data = mysqli_fetch_assoc($check_img);
                        $url_final = $img_data['url_imagen'];
                    } else {
                        // NO EXISTE: Subimos a Cloudinary
                        $resultado_cloud = $upload->upload($tmp_name, ['folder' => 'brooking_habitaciones']);
                        $url_final = $resultado_cloud['secure_url'];
                    }

                    // Insertar vínculo (sea nueva o reutilizada)
                    $query_img = "INSERT INTO cat_imagen (id_catalogo, id_habitacion, url_imagen, hash_archivo, id_status) 
                                  VALUES ('$id_hotel_db', '$id_hab_nuevo', '$url_final', '$file_hash', 1)";
                    
                    mysqli_query($config, $query_img);
                }
            }
        }

        mysqli_commit($config);
        header("Location: habitaciones.php?u=$uuid_hotel&msg=added");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($config);
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Habitación | <?php echo $hotel['nombre']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
</head>
<body class="bg-light p-5">

<div class="container">
    <div class="col-md-7 mx-auto card shadow-sm border-0 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0">Añadir Habitación</h4>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?php echo $hotel['nombre']; ?></span>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger border-0 shadow-sm small">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="habForm">
            <div class="mb-3">
                <label class="form-label fw-bold small text-muted">NOMBRE DE LA HABITACIÓN</label>
                <input type="text" name="nombre" class="form-control rounded-pill" 
                       value="<?php echo $_POST['nombre'] ?? ''; ?>" placeholder="Ej: Master Suite King Size" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small text-muted">CATEGORÍA / TIPO</label>
                <select name="id_tipo" class="form-select rounded-pill" required>
                    <option value="" disabled <?php echo !isset($_POST['id_tipo']) ? 'selected' : ''; ?>>Selecciona un tipo...</option>
                    <?php 
                    mysqli_data_seek($res_tipos, 0);
                    while($tipo = mysqli_fetch_assoc($res_tipos)): 
                    ?>
                        <option value="<?php echo $tipo['id_tipo']; ?>" <?php echo (isset($_POST['id_tipo']) && $_POST['id_tipo'] == $tipo['id_tipo']) ? 'selected' : ''; ?>>
                            <?php echo $tipo['nombre']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small text-muted">DESCRIPCIÓN DETALLADA</label>
                <textarea name="descripcion" class="form-control rounded-4" rows="3" placeholder="Describe las amenidades..."><?php echo $_POST['descripcion'] ?? ''; ?></textarea>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">PRECIO POR NOCHE</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill">$</span>
                        <input type="number" name="precio" class="form-control border-start-0 rounded-end-pill" 
                               step="0.01" value="<?php echo $_POST['precio'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">CAPACIDAD (PAXS)</label>
                    <input type="number" name="capacidad" class="form-control rounded-pill" 
                           value="<?php echo $_POST['capacidad'] ?? ''; ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">STOCK DISPONIBLE</label>
                    <input type="number" name="disponibilidad" class="form-control rounded-pill" 
                           value="<?php echo $_POST['disponibilidad'] ?? ''; ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-muted">FOTOS DE LA HABITACIÓN</label>
                <div class="p-3 border-2 border-dashed rounded-4 bg-white text-center">
                    <input type="file" name="foto[]" class="form-control" accept="image/*" multiple required>
                    <small class="text-muted d-block mt-2">Puedes seleccionar varias imágenes a la vez.</small>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold">Guardar Habitación</button>
            <a href="habitaciones.php?u=<?php echo $uuid_hotel; ?>" class="btn btn-link w-100 mt-2 text-muted text-decoration-none">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
    // --- PERSISTENCIA CON LOCALSTORAGE ---
    const draftKey = "hab_draft_<?php echo $uuid_hotel; ?>";
    const form = document.getElementById('habForm');
    const inputs = form.querySelectorAll('input, select, textarea');

    // Recuperar datos al cargar
    window.addEventListener('load', () => {
        const saved = JSON.parse(localStorage.getItem(draftKey)) || {};
        inputs.forEach(input => {
            if (input.type !== 'file' && saved[input.name]) {
                input.value = saved[input.name];
            }
        });
    });

    // Guardar mientras se escribe
    form.addEventListener('input', () => {
        const data = {};
        inputs.forEach(input => {
            if (input.type !== 'file') data[input.name] = input.value;
        });
        localStorage.setItem(draftKey, JSON.stringify(data));
    });

    // Borrar al enviar con éxito
    form.addEventListener('submit', () => {
        setTimeout(() => localStorage.removeItem(draftKey), 1000);
    });
</script>

</body>
</html>