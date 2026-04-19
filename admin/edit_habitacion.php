<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";
use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// 1. OBTENER DATOS DE LA HABITACIÓN
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

    $id_hab_int = $habitacion['id_habitacion'];
    $id_hotel_db = $habitacion['id_catalogo'];
} else {
    header("Location: hoteles.php");
    exit();
}

$res_tipos = mysqli_query($config, "SELECT * FROM cat_tipo ORDER BY nombre ASC");
// Consulta de estados permitidos: Activo(1), Inactivo(2), Mantenimiento(7)
$res_status_opciones = mysqli_query($config, "SELECT id_status, nombre FROM status WHERE id_status IN (1, 2, 7)");

// ACTUALIZACIÓN
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_hab = mysqli_real_escape_string($config, $_POST['nombre_hab']);
    $descripcion_hab = mysqli_real_escape_string($config, $_POST['descripcion_hab']);
    $capacidad = mysqli_real_escape_string($config, $_POST['capacidad']);
    $precio = mysqli_real_escape_string($config, $_POST['precio']);
    // SE ELIMINÓ LA VARIABLE $disponibilidad
    $tipo = mysqli_real_escape_string($config, $_POST['tipo']);
    $id_status = intval($_POST['id_status']); 
    $hotel_uuid = mysqli_real_escape_string($config, $_POST['hotel_uuid']);

    mysqli_begin_transaction($config);

    try {
        // borrado pa' fotos
        if (isset($_POST['borrar_fotos'])) {
            foreach ($_POST['borrar_fotos'] as $id_img_borrar) {
                $id_img_borrar = intval($id_img_borrar);
                mysqli_query($config, "UPDATE cat_imagen SET 
                    id_status = (SELECT id_status FROM status WHERE nombre = 'Inactivo' LIMIT 1) 
                    WHERE id_imagen = $id_img_borrar");
            }
        }

        // Actualización de datos principales (SE QUITÓ disponibilidad = '$disponibilidad')
        mysqli_query($config, "UPDATE cat_catalogo_habitacion SET 
                        nombre = '$nombre_hab', 
                        descripcion = '$descripcion_hab',
                        capacidad = '$capacidad',
                        precio = '$precio',
                        id_tipo = '$tipo',
                        id_status = '$id_status' 
                        WHERE id_habitacion = '$id_hab_int'");

        if (isset($_FILES['foto']) && !empty($_FILES['foto']['name'][0])) {
            $upload = new UploadApi();
            foreach ($_FILES['foto']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['foto']['error'][$key] == 0) {
                    $file_hash = md5_file($tmp_name);
                    $check_img = mysqli_query($config, "SELECT url_imagen FROM cat_imagen WHERE hash_archivo = '$file_hash' LIMIT 1");
                    
                    if (mysqli_num_rows($check_img) > 0) {
                        $url_final = mysqli_fetch_assoc($check_img)['url_imagen'];
                    } else {
                        $resultado_cloud = $upload->upload($tmp_name, ['folder' => 'brooking_habitaciones']);
                        $url_final = $resultado_cloud['secure_url'];
                    }

                    mysqli_query($config, "INSERT INTO cat_imagen (id_catalogo, id_habitacion, url_imagen, hash_archivo, id_status) 
                        VALUES ('$id_hotel_db', '$id_hab_int', '$url_final', '$file_hash', 
                        (SELECT id_status FROM status WHERE nombre = 'Activo' LIMIT 1))");
                }
            }
        }

        mysqli_commit($config);
        header("Location: habitaciones.php?u=$hotel_uuid&msg=updated");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($config);
        $error = "Error: " . $e->getMessage();
    }
}

$res_fotos = mysqli_query($config, "SELECT i.* FROM cat_imagen i 
    JOIN status s ON i.id_status = s.id_status 
    WHERE i.id_habitacion = '$id_hab_int' AND s.nombre = 'Activo'");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Brooking | Editar Habitación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
    <style>
        .foto-item.marked-delete { opacity: 0.3; filter: grayscale(1); border: 2px solid #dc3545; border-radius: 8px; }
        .delete-overlay { cursor: pointer; transition: 0.2s; background: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; z-index: 10; }
        .delete-overlay:hover { background: #dc3545; color: white !important; }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    <div class="sidebar d-flex flex-column shadow-sm">
        <div class="p-4 text-center"><img src="../imagenes/brooking.png" alt="Logo" width="140"></div>
        <ul class="nav flex-column mb-auto">
            <li><a href="admin_dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="bi bi-people"></i> Usuarios</a></li>
            <li><a href="hoteles.php" class="nav-link active"><i class="bi bi-building"></i> Hoteles</a></li>
            <li><a href="reservaciones.php" class="nav-link"><i class="bi bi-calendar-check"></i> Reservaciones</a></li>
        </ul>
        <div class="p-3 border-top">
            <a href="../auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold m-0">Editar Habitación</h2>
            </div>
            <a href="habitaciones.php?u=<?php echo $habitacion['hotel_uuid']; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left"></i> Volver a la lista
            </a>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger shadow-sm border-0 mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 col-md-8 mx-auto rounded-4">
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="uuid_hab" value="<?php echo $habitacion['uuid']; ?>">
                    <input type="hidden" name="id_habitacion" value="<?php echo $habitacion['id_habitacion']; ?>">
                    <input type="hidden" name="hotel_uuid" value="<?php echo $habitacion['hotel_uuid']; ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">NOMBRE</label>
                        <input type="text" name="nombre_hab" class="form-control rounded-pill" value="<?php echo $habitacion['nombre']; ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">CATEGORÍA</label>
                            <select name="tipo" class="form-select rounded-pill" required>
                                <?php mysqli_data_seek($res_tipos, 0); while($t = mysqli_fetch_assoc($res_tipos)): ?>
                                    <option value="<?php echo $t['id_tipo']; ?>" <?php echo ($habitacion['id_tipo'] == $t['id_tipo']) ? 'selected' : ''; ?>>
                                        <?php echo $t['nombre']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">CAPACIDAD (PAXS)</label>
                            <input type="number" name="capacidad" class="form-control rounded-pill" value="<?php echo $habitacion['capacidad']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">DESCRIPCIÓN</label>
                        <textarea name="descripcion_hab" class="form-control rounded-3" rows="3"><?php echo $habitacion['descripcion']; ?></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">PRECIO ($)</label>
                            <input type="number" step="0.01" name="precio" class="form-control rounded-pill" value="<?php echo $habitacion['precio']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">ESTADO OPERATIVO</label>
                            <select name="id_status" class="form-select rounded-pill">
                                <?php mysqli_data_seek($res_status_opciones, 0); while($st = mysqli_fetch_assoc($res_status_opciones)): ?>
                                    <option value="<?= $st['id_status'] ?>" <?= ($habitacion['id_status'] == $st['id_status']) ? 'selected' : '' ?>>
                                        <?= $st['nombre'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted mb-3">GALERÍA ACTUAL</label>
                        <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded-3 border">
                            <?php if(mysqli_num_rows($res_fotos) > 0): ?>
                                <?php while ($img = mysqli_fetch_assoc($res_fotos)): ?>
                                    <div class="position-relative foto-item" id="foto_<?= $img['id_imagen'] ?>">
                                        <img src="<?= $img['url_imagen'] ?>" class="rounded shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                        <label class="position-absolute top-0 end-0 mt-1 me-1 delete-overlay">
                                            <input type="checkbox" name="borrar_fotos[]" value="<?= $img['id_imagen'] ?>" class="d-none" onchange="marcarBorrado(<?= $img['id_imagen'] ?>, this)">
                                            <i class="bi bi-x small fw-bold"></i>
                                        </label>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted small m-0">No hay fotos activas.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-primary small fw-bold">AÑADIR NUEVAS IMÁGENES</label>
                        <input type="file" name="foto[]" class="form-control rounded-pill" accept="image/*" multiple>
                    </div>

                    <div class="d-flex gap-3">
                        <a href="habitaciones.php?u=<?php echo $habitacion['hotel_uuid']; ?>" class="btn btn-light w-50 fw-bold py-2 rounded-pill border text-muted">CANCELAR</a>
                        <button type="submit" class="btn btn-primary w-50 fw-bold py-2 rounded-pill shadow-sm">ACTUALIZAR HABITACIÓN</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function marcarBorrado(id, checkbox) {
    const div = document.getElementById('foto_' + id);
    checkbox.checked ? div.classList.add('marked-delete') : div.classList.remove('marked-delete');
}
</script>

</body>
</html>