<?php
session_start();
include("../config/conexion.php");
// PASO 1: Incluir configuración de Cloudinary
require_once "../config/cloudinary_config.php";
use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// 1. Obtener datos del hotel por ID
if (isset($_GET['id'])) {
    $id_hotel = mysqli_real_escape_string($conexion, $_GET['id']);
    
    // Traemos la info del hotel y solo la primera imagen para la vista previa
    $query = "SELECT c.*, i.url_imagen 
              FROM catalogo c
              LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
              WHERE c.id_catalogo = '$id_hotel' LIMIT 1";
    
    $res = mysqli_query($conexion, $query);
    $hotel = mysqli_fetch_assoc($res);

    if (!$hotel) {
        header("Location: hoteles.php");
        exit();
    }
}

// 2. Consulta para el select de propietarios
$query_propietarios = "SELECT u.uuid, u.first_name, u.last_name 
                       FROM usr_users u
                       INNER JOIN usr_users_login l ON u.uuid = l.user_uuid
                       WHERE l.role = 'propietario' OR l.role = 'admin'";
$res_propietarios = mysqli_query($conexion, $query_propietarios);

// 3. Procesar la actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_hotel = mysqli_real_escape_string($conexion, $_POST['id_catalogo']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $propietario_uuid = mysqli_real_escape_string($conexion, $_POST['propietario_uuid']);

    mysqli_begin_transaction($conexion);

    try {
        // Actualizar datos básicos
        mysqli_query($conexion, "UPDATE catalogo SET 
            nombre = '$nombre', 
            descripcion = '$descripcion', 
            propietario_uuid = '$propietario_uuid' 
            WHERE id_catalogo = '$id_hotel'");

        // PASO 2: Lógica para AÑADIR múltiples fotos (si seleccionan nuevas)
        if (isset($_FILES['foto']) && !empty($_FILES['foto']['name'][0])) {
            $upload = new UploadApi();
            
            foreach ($_FILES['foto']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['foto']['error'][$key] == 0) {
                    $resultado_cloud = $upload->upload($tmp_name, [
                        'folder' => 'brooking_hoteles'
                    ]);
                    $nueva_url = $resultado_cloud['secure_url'];

                    // Insertamos las nuevas fotos en la tabla cat_imagen
                    mysqli_query($conexion, "INSERT INTO cat_imagen (id_catalogo, url_imagen, status) 
                                             VALUES ('$id_hotel', '$nueva_url', 'active')");
                }
            }
        }

        mysqli_commit($conexion);
        header("Location: hoteles.php?msg=updated");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        die("Error al actualizar: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brooking | Editar Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
</head>
<body>

<div class="d-flex">
    <div class="sidebar d-flex flex-column shadow-sm">
        <div class="p-4 text-center">
            <img src="../imagenes/brooking.png" alt="Logo" width="140">
        </div>
        <ul class="nav flex-column mb-auto">
            <li><a href="admin_dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="bi bi-people"></i> Usuarios</a></li>
            <li><a href="hoteles.php" class="nav-link active"><i class="bi bi-building"></i> Hoteles</a></li>
            <li><a href="reservaciones.php" class="nav-link"><i class="bi bi-calendar-check"></i> Reservaciones</a></li>
        </ul>
        <div class="p-3 border-top">
            <a href="../auth/logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
            </a>
        </div>
    </div>

    <div class="content p-5 w-100">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold m-0">Editar Hotel</h2>
            <a href="hoteles.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-arrow-left"></i> Volver a la lista
            </a>
        </div>

        <div class="card stat-card shadow-sm border-0 col-md-6 mx-auto rounded-3 mt-5">
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_catalogo" value="<?php echo $hotel['id_catalogo']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">NOMBRE DEL HOTEL</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo $hotel['nombre']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">DESCRIPCIÓN</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?php echo $hotel['descripcion']; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">VISTA PREVIA DE IMAGEN</label>
                        <div class="mb-2">
                            <img src="<?php echo $hotel['url_imagen']; ?>" class="img-thumbnail rounded" style="max-height: 150px;">
                        </div>
                        <label class="form-label text-muted small fw-bold">AÑADIR MÁS FOTOS (OPCIONAL)</label>
                        <input type="file" name="foto[]" class="form-control" accept="image/*" multiple>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">PROPIETARIO ASIGNADO</label>
                        <select name="propietario_uuid" class="form-select border-primary">
                            <?php while($prop = mysqli_fetch_assoc($res_propietarios)): ?>
                                <option value="<?php echo $prop['uuid']; ?>" <?php echo ($hotel['propietario_uuid'] == $prop['uuid']) ? 'selected' : ''; ?>>
                                    <?php echo $prop['first_name'] . ' ' . $prop['last_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold py-2 rounded-pill">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>