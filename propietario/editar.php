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
$query_status = mysqli_query($config, "SELECT * FROM status WHERE nombre IN ('Activo', 'Mantenimiento')");

$id_pais_actual = $hotel['pais_id'] ?? 0; 
$res_ubicaciones = mysqli_query($config, "SELECT id as id_ubicacion, name as nombre FROM states WHERE country_id = '$id_pais_actual' ORDER BY name ASC");/* UPDATE */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($config, $_POST['descripcion']);

   $id_ubicacion = intval($_POST['estado']); 
   $id_status = intval($_POST['id_status']);

   $sql = "UPDATE catalogo SET 
        nombre = '$nombre', 
        descripcion = '$descripcion', 
        id_ubicacion = '$id_ubicacion', 
        id_status = '$id_status',
        direccion = '" . mysqli_real_escape_string($config, $_POST['direccion']) . "',
        pais = '" . mysqli_real_escape_string($config, $_POST['pais']) . "',
        ciudad = '" . mysqli_real_escape_string($config, $_POST['ciudad']) . "'
        WHERE id_catalogo = '$id' AND propietario_uuid = '$propietario_uuid'";

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
    <label class="form-label fw-bold small text-muted">DIRECCIÓN EXACTA</label>
    <input type="text" name="direccion" 
           value="<?php echo htmlspecialchars($hotel['direccion'] ?? ''); ?>" 
           class="form-control rounded-pill" placeholder="Calle y número" required>
</div>

<div class="mb-3">
    <label class="form-label fw-bold small text-muted">CIUDAD ESPECÍFICA</label>
    <input type="text" name="ciudad" 
           value="<?php echo htmlspecialchars($hotel['ciudad'] ?? ''); ?>" 
           class="form-control rounded-pill" placeholder="Ej: Cancún" required>
</div>

<!-- ESTADO -->
<div class="mb-3">
    <label class="form-label fw-bold small text-muted">CIUDAD / ESTADO</label>
    <select name="estado" id="estado" class="form-select" required>
        <option value="">Selecciona una ubicación</option>
        <?php 
        mysqli_data_seek($res_ubicaciones, 0); 
        while($ub = mysqli_fetch_assoc($res_ubicaciones)): 
        ?>
            <option value="<?= $ub['id_ubicacion'] ?>" <?= ($ub['id_ubicacion'] == $hotel['id_ubicacion']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($ub['nombre']) ?>
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

<div class="mb-4">
    <label class="form-label fw-bold small text-muted">ESTADO OPERATIVO</label>
    <select name="id_status" class="form-select border-primary-subtle rounded-pill">
        <option value="1" <?php echo ($hotel['id_status'] == 1 || is_null($hotel['id_status'])) ? 'selected' : ''; ?>>Activo</option>
        <option value="2" <?php echo ($hotel['id_status'] == 2) ? 'selected' : ''; ?>>Inactivo</option>
        <option value="3" <?php echo ($hotel['id_status'] == 3) ? 'selected' : ''; ?>>Fuera de servicio (Mantenimiento)</option>
        
        <?php if (!empty($selected)): ?>
            <option value="4" <?php echo ($hotel['id_status'] == 4) ? 'selected' : ''; ?>>Reservada</option>
        <?php endif; ?>
    </select>
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