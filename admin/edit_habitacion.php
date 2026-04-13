<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";
use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['uh'])) {
    $uuid_hab = mysqli_real_escape_string($config, $_GET['uh']);

    $query = "SELECT h.*, c.uuid as hotel_uuid, c.nombre as hotel_nombre 
              FROM cat_catalogo_habitacion h
              INNER JOIN catalogo c ON h.id_catalogo = c.id_catalogo
              WHERE h.uuid = '$uuid_hab' LIMIT 1";
    
    $res = mysqli_query($config, $query);
    $habitacion = mysqli_fetch_assoc($res);

    if (!$habitacion) {
        header("Location: hoteles.php");
        exit();
    }
} else {
    header("Location: hoteles.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uuid_hab = mysqli_real_escape_string($config, $_POST['uuid_hab']);
    $id_hab_int = mysqli_real_escape_string($config, $_POST['id_habitacion']);
    $nombre_hab = mysqli_real_escape_string($config, $_POST['nombre_hab']);
    $descripcion_hab = mysqli_real_escape_string($config, $_POST['descripcion_hab']);
    $capacidad = mysqli_real_escape_string($config, $_POST['capacidad']);
    $precio = mysqli_real_escape_string($config, $_POST['precio']);
    $tipo = mysqli_real_escape_string($config, $_POST['tipo']);
    $hotel_uuid = mysqli_real_escape_string($config, $_POST['hotel_uuid']);

    mysqli_begin_transaction($config);

    try {
        $update_query = "UPDATE cat_catalogo_habitacion SET 
                        nombre = '$nombre_hab', 
                        descripcion = '$descripcion_hab',
                        capacidad = '$capacidad',
                        precio = '$precio',
                        id_tipo = '$tipo' 
                        WHERE uuid = '$uuid_hab'";
        
        mysqli_query($config, $update_query);

        if (isset($_FILES['foto']) && !empty($_FILES['foto']['name'][0])) {
            $upload = new UploadApi();
            foreach ($_FILES['foto']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['foto']['error'][$key] == 0) {
                    $resultado_cloud = $upload->upload($tmp_name, ['folder' => 'brooking_habitaciones']);
                    $nueva_url = $resultado_cloud['secure_url'];

                    mysqli_query($config, "INSERT INTO cat_imagen (id_habitacion, url_imagen, status) 
                                           VALUES ('$id_hab_int', '$nueva_url', 'active')");
                }
            }
        }

        mysqli_commit($config);
        header("Location: habitaciones.php?u=$hotel_uuid&msg=updated");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($config);
        die("Error al actualizar: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brooking | Editar Habitación</title>
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
            <div>
                <h2 class="fw-bold m-0">Editar Habitación</h2>
                <p class= small">Hotel: <span class="text-primary fw-bold"><?php echo $habitacion['hotel_nombre']; ?></span></p>
            </div>
            <a href="habitaciones.php?u=<?php echo $habitacion['hotel_uuid']; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-arrow-left"></i> Volver a la lista
            </a>
        </div>

        <div class="card stat-card shadow-sm border-0 col-md-7 mx-auto rounded-3">
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="uuid_hab" value="<?php echo $habitacion['uuid']; ?>">
                    <input type="hidden" name="id_habitacion" value="<?php echo $habitacion['id_habitacion']; ?>">
                    <input type="hidden" name="hotel_uuid" value="<?php echo $habitacion['hotel_uuid']; ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">NOMBRE DE LA HABITACIÓN</label>
                        <input type="text" name="nombre_hab" class="form-control" value="<?php echo $habitacion['nombre']; ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">CATEGORÍA</label>
                            <select name="tipo" class="form-select border-primary" required>
                                <?php $tipo_db = $habitacion['id_tipo'] ?? ''; ?>
                                <option value="1" <?php echo ($tipo_db == '1') ? 'selected' : ''; ?>>Individual (Single)</option>
                                <option value="2" <?php echo ($tipo_db == '2') ? 'selected' : ''; ?>>Doble (Double)</option>
                                <option value="3" <?php echo ($tipo_db == '3') ? 'selected' : ''; ?>>Triple</option>
                                <option value="4" <?php echo ($tipo_db == '4') ? 'selected' : ''; ?>>Cuádruple</option>
                                <option value="5" <?php echo ($tipo_db == '5') ? 'selected' : ''; ?>>Suite</option>
                                <option value="6" <?php echo ($tipo_db == '6') ? 'selected' : ''; ?>>Deluxe / Presidencial</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">CAPACIDAD (PAXS)</label>
                            <input type="number" name="capacidad" class="form-control" value="<?php echo $habitacion['capacidad']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">DESCRIPCIÓN</label>
                        <textarea name="descripcion_hab" class="form-control" rows="2"><?php echo $habitacion['descripcion']; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">PRECIO POR NOCHE ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="precio" class="form-control" value="<?php echo $habitacion['precio']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">GESTIÓN DE IMÁGENES</label>
                        <div class="alert alert-light border small">
                            <i class="bi bi-info-circle"></i> Las imágenes actuales se mostrarán cuando la tabla de fotos esté lista.
                        </div>
                        <label class="form-label small fw-bold">AÑADIR NUEVAS FOTOS</label>
                        <input type="file" name="foto[]" class="form-control" accept="image/*" multiple>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold py-2 rounded-pill shadow-sm">Actualizar Habitación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>