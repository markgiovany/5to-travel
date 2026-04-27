<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";
use Cloudinary\Api\Upload\UploadApi;

// Consulta para propietarios
$query_propietarios = "SELECT u.uuid, u.first_name, u.last_name 
                        FROM usr_users u
                        INNER JOIN usr_users_login l ON u.uuid = l.user_uuid
                        WHERE l.id_rol = '2'";
$resultado = mysqli_query($config, $query_propietarios);

// Cargar países para el select
$query_paises = "SELECT id, name FROM countries ORDER BY name ASC";
$resultado_paises = mysqli_query($config, $query_paises);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($config, $_POST['descripcion']);
    $propietario_uuid = mysqli_real_escape_string($config, $_POST['propietario_uuid']);
    $direccion_texto = mysqli_real_escape_string($config, $_POST['ubicacion_texto']);
    
    // Capturamos los 3 IDs de ubicación
    $id_pais = intval($_POST['id_pais']);
    $id_estado = intval($_POST['id_estado']);
    $id_ciudad = intval($_POST['id_ciudad']); 
    
    mysqli_begin_transaction($config);

    try {
        if (!isset($_FILES['foto']) || empty($_FILES['foto']['name'][0])) {
            throw new Exception("Debes seleccionar al menos una foto.");
        }
        $query_new_loc = "INSERT INTO cat_ubicacion (direccion, country_id, state_id, city_id, id_status) 
                          VALUES ('$direccion_texto', $id_pais, $id_estado, $id_ciudad, (SELECT id_status FROM status WHERE nombre = 'Activo' LIMIT 1))";
        
        if (!mysqli_query($config, $query_new_loc)) {
            throw new Exception("Error al insertar ubicación: " . mysqli_error($config));
        }
        
        $id_ubicacion = mysqli_insert_id($config);
        $query_hotel = "INSERT INTO catalogo (uuid, propietario_uuid, nombre, descripcion, id_ubicacion) 
                VALUES (UUID(), '$propietario_uuid', '$nombre', '$descripcion', '$id_ubicacion')";
        
        mysqli_query($config, $query_hotel);
        $id_hotel = mysqli_insert_id($config);

        // Lógica de Cloudinary
        $upload = new UploadApi();
        foreach ($_FILES['foto']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['foto']['error'][$key] == 0) {
                $resultado_cloud = $upload->upload($tmp_name, ['folder' => 'brooking_hoteles']);
                $url_final = $resultado_cloud['secure_url'];

                $query_img = "INSERT INTO cat_imagen (id_catalogo, url_imagen, id_status) 
                              VALUES ('$id_hotel', '$url_final', (SELECT id_status FROM status WHERE nombre = 'Activo' LIMIT 1))";
                mysqli_query($config, $query_img);
            }
        }

        mysqli_commit($config);
        header("Location: hoteles.php?msg=added"); exit();

    } catch (Exception $e) {
        mysqli_rollback($config);
        die("<div style='color:red; font-family:sans-serif;'><strong>Error:</strong> " . $e->getMessage() . "</div>");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Brooking | Nuevo Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container">
    <div class="col-md-6 mx-auto card shadow-sm border-0 p-4">
        <h4 class="fw-bold mb-4">Registrar Nuevo Hotel</h4>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label fw-bold small">NOMBRE DEL HOTEL</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold small">DESCRIPCIÓN</label>
                <textarea name="descripcion" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">PAÍS</label>
                <select id="pais" name="id_pais" class="form-select" required>
                    <option value="">Selecciona un país</option>
                    <?php while($p = mysqli_fetch_assoc($resultado_paises)): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">ESTADO / PROVINCIA</label>
                <select id="estado" name="id_estado" class="form-select" required disabled>
                    <option value="">Selecciona un país primero</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">CIUDAD</label>
                <select id="ciudad" name="id_ciudad" class="form-select" required disabled>
                    <option value="">Selecciona un estado primero</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">UBICACIÓN (DIRECCIÓN)</label>
                <input type="text" name="ubicacion_texto" class="form-control" placeholder="Calle, número, colonia..." required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">FOTOS</label>
                <input type="file" name="foto[]" class="form-control" accept="image/*" multiple required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-primary">DUEÑO</label>
                <select name="propietario_uuid" class="form-select" required>
                    <option value="">-- Seleccionar --</option>
                    <?php 
                    // Reiniciamos el puntero del resultado de usuarios por si acaso
                    mysqli_data_seek($resultado, 0);
                    while($usuario = mysqli_fetch_assoc($resultado)): 
                    ?>
                        <option value="<?php echo $usuario['uuid']; ?>">
                            <?php echo $usuario['first_name'] . " " . $usuario['last_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold">Guardar Hotel</button>
            <a href="hoteles.php" class="btn btn-link w-100 mt-2 text-muted text-decoration-none">Cancelar</a>
        </form>
    </div>
</div>

<script>
document.getElementById('pais').addEventListener('change', function() {
    let paisId = this.value;
    let estadoSelect = document.getElementById('estado');
    let ciudadSelect = document.getElementById('ciudad');

    if (paisId) {
        fetch('../auth/get_locations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'pais_id=' + paisId
        })
        .then(response => response.text())
        .then(data => {
            estadoSelect.innerHTML = data;
            estadoSelect.disabled = false;
            ciudadSelect.innerHTML = '<option value="">Selecciona un estado primero</option>';
            ciudadSelect.disabled = true;
        });
    }
});

document.getElementById('estado').addEventListener('change', function() {
    let estadoId = this.value;
    let ciudadSelect = document.getElementById('ciudad');

    if (estadoId) {
        fetch('../auth/get_locations.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'estado_id=' + estadoId
        })
        .then(response => response.text())
        .then(data => {
            ciudadSelect.innerHTML = data;
            ciudadSelect.disabled = false;
        });
    }
});
</script>

</body>
</html>