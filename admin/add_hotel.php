<?php
session_start();
include("../config/conexion.php");
require_once "../config/cloudinary_config.php";
use Cloudinary\Api\Upload\UploadApi;

// 1. Cargamos los PROPIETARIOS
$query_propietarios = "SELECT u.uuid, u.first_name, u.last_name 
                        FROM usr_users u
                        INNER JOIN usr_users_login l ON u.uuid = l.user_uuid
                        WHERE l.role = 'propietario'";
$resultado = mysqli_query($conexion, $query_propietarios);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $propietario_uuid = mysqli_real_escape_string($conexion, $_POST['propietario_uuid']);
    $direccion_texto = mysqli_real_escape_string($conexion, $_POST['ubicacion_texto']);
    $disponibilidad = mysqli_real_escape_string($conexion, $_POST['disponibilidad']);
    
    mysqli_begin_transaction($conexion);

    try {
        if (!isset($_FILES['foto']) || empty($_FILES['foto']['name'][0])) {
            throw new Exception("Debes seleccionar al menos una foto.");
        }

        // PASO A: Guardar la dirección en cat_ubicacion (Columna 'direccion')
        // id_ciudad lo pongo en 1 por defecto según tu captura
        $query_new_loc = "INSERT INTO cat_ubicacion (direccion, id_ciudad, status) VALUES ('$direccion_texto', 1, 'Activo')";
        mysqli_query($conexion, $query_new_loc);
        $id_ubicacion = mysqli_insert_id($conexion);

        // PASO B: Insertar el hotel en catalogo
        // SOLO usamos: propietario_uuid, nombre, descripcion, disponibilidad, id_ubicacion
        // Eliminé id_group e id_tipo porque te daban error de "Unknown column"
        $query_hotel = "INSERT INTO catalogo (propietario_uuid, nombre, descripcion, disponibilidad, id_ubicacion) 
                        VALUES ('$propietario_uuid', '$nombre', '$descripcion', '$disponibilidad', '$id_ubicacion')";
        
        mysqli_query($conexion, $query_hotel);
        $id_hotel = mysqli_insert_id($conexion);

        // Lógica de Cloudinary
        $upload = new UploadApi();
        foreach ($_FILES['foto']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['foto']['error'][$key] == 0) {
                $resultado_cloud = $upload->upload($tmp_name, ['folder' => 'brooking_hoteles']);
                $url_final = $resultado_cloud['secure_url'];

                $query_img = "INSERT INTO cat_imagen (id_catalogo, url_imagen, status) 
                              VALUES ('$id_hotel', '$url_final', 'active')";
                mysqli_query($conexion, $query_img);
            }
        }

        mysqli_commit($conexion);
        header("Location: hoteles.php?msg=added"); exit();

    } catch (Exception $e) {
        mysqli_rollback($conexion);
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
                <label class="form-label fw-bold small">UBICACIÓN (DIRECCIÓN)</label>
                <input type="text" name="ubicacion_texto" class="form-control" placeholder="Escribe la dirección aquí..." required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">HABITACIONES TOTALES</label>
                <input type="number" name="disponibilidad" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">FOTOS</label>
                <input type="file" name="foto[]" class="form-control" accept="image/*" multiple required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-primary">DUEÑO</label>
                <select name="propietario_uuid" class="form-select" required>
                    <option value="">-- Seleccionar --</option>
                    <?php while($usuario = mysqli_fetch_assoc($resultado)): ?>
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
</body>
</html>