<?php
session_start();
include("../config/config.php");
require_once "../config/cloudinary_config.php";
use Cloudinary\Api\Upload\UploadApi;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['u'])) {
    $uuid_hotel = mysqli_real_escape_string($config, $_GET['u']);

    $query = "SELECT c.*, u.country_id, u.state_id, u.city_id, u.direccion 
              FROM catalogo c
              LEFT JOIN cat_ubicacion u ON c.id_ubicacion = u.id_ubicacion
              WHERE c.uuid = '$uuid_hotel' LIMIT 1";
    
    $res = mysqli_query($config, $query);
    $hotel = mysqli_fetch_assoc($res);

    if (!$hotel) { header("Location: hoteles.php"); exit(); }
}

$res_propietarios = mysqli_query($config, "SELECT u.uuid, u.first_name, u.last_name FROM usr_users u INNER JOIN usr_users_login l ON u.uuid = l.user_uuid WHERE l.role IN ('propietario', 'admin')");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_hotel = mysqli_real_escape_string($config, $_POST['id_catalogo']);
    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($config, $_POST['descripcion']);
    $propietario_uuid = mysqli_real_escape_string($config, $_POST['propietario_uuid']);
    
    $id_pais = intval($_POST['id_pais']);
    $id_estado = intval($_POST['id_estado']);
    $id_ciudad = intval($_POST['id_ciudad']);
    $dir_texto = mysqli_real_escape_string($config, $_POST['ubicacion_texto']);
    $id_ub_actual = $hotel['id_ubicacion'];

    mysqli_begin_transaction($config);

    try {
        if (isset($_POST['borrar_fotos'])) {
            foreach ($_POST['borrar_fotos'] as $id_img_borrar) {
                $id_img_borrar = intval($id_img_borrar);
                mysqli_query($config, "UPDATE cat_imagen SET 
                    id_status = (SELECT id_status FROM status WHERE nombre = 'Inactivo' LIMIT 1) 
                    WHERE id_imagen = $id_img_borrar");
            }
        }

        mysqli_query($config, "UPDATE cat_ubicacion SET country_id=$id_pais, state_id=$id_estado, city_id=$id_ciudad, direccion='$dir_texto' WHERE id_ubicacion=$id_ub_actual");
        mysqli_query($config, "UPDATE catalogo SET nombre='$nombre', descripcion='$descripcion', propietario_uuid='$propietario_uuid' WHERE id_catalogo='$id_hotel'");

        if (isset($_FILES['foto'])) {
            $upload = new UploadApi();
            foreach ($_FILES['foto']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['foto']['error'][$key] == 0) {
                    $res_cloud = $upload->upload($tmp_name, ['folder' => 'brooking_hoteles']);
                    $url = $res_cloud['secure_url'];
                    mysqli_query($config, "INSERT INTO cat_imagen (id_catalogo, url_imagen, id_status) 
                        VALUES ('$id_hotel', '$url', (SELECT id_status FROM status WHERE nombre = 'Activo' LIMIT 1))");
                }
            }
        }

        mysqli_commit($config);
        header("Location: hoteles.php?msg=updated"); exit();
    } catch (Exception $e) {
        mysqli_rollback($config);
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Brooking | Editar Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
    <style>
        .foto-item.marked-delete { opacity: 0.3; filter: grayscale(1); border: 2px solid #dc3545; }
        .delete-overlay { cursor: pointer; transition: 0.2s; background: white; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .delete-overlay:hover { background: #dc3545; color: white !important; }
        #btn-mas-fotos { display: none; } /* Oculto por defecto */
    </style>
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
            <a href="../auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold m-0">Editar Hotel</h2>
            <a href="hoteles.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-arrow-left"></i> Volver a la lista
            </a>
        </div>

        <div class="card shadow-sm border-0 col-md-8 mx-auto rounded-4">
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_catalogo" value="<?= $hotel['id_catalogo'] ?>">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">NOMBRE DEL HOTEL</label>
                        <input type="text" name="nombre" class="form-control" value="<?= $hotel['nombre'] ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">PAÍS</label>
                            <select id="pais" name="id_pais" class="form-select" required>
                                <?php 
                                $res_p = mysqli_query($config, "SELECT id, name FROM countries ORDER BY name ASC");
                                while($p = mysqli_fetch_assoc($res_p)): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($hotel['country_id'] == $p['id']) ? 'selected' : '' ?>><?= $p['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">ESTADO</label>
                            <select id="estado" name="id_estado" class="form-select" required></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">CIUDAD</label>
                            <select id="ciudad" name="id_ciudad" class="form-select" required></select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">DIRECCIÓN</label>
                        <input type="text" name="ubicacion_texto" class="form-control" value="<?= $hotel['direccion'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">DESCRIPCIÓN</label>
                        <textarea name="descripcion" class="form-control" rows="3"><?= $hotel['descripcion'] ?></textarea>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-3">GALERÍA ACTUAL</label>
                        <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded-3 border">
                            <?php
                            $q_imgs = mysqli_query($config, "SELECT i.* FROM cat_imagen i 
                                                             JOIN status s ON i.id_status = s.id_status 
                                                             WHERE i.id_catalogo = '{$hotel['id_catalogo']}' 
                                                             AND s.nombre = 'Activo'");
                            while ($img = mysqli_fetch_assoc($q_imgs)): ?>
                                <div class="position-relative foto-item" id="foto_<?= $img['id_imagen'] ?>">
                                    <img src="<?= $img['url_imagen'] ?>" class="rounded shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                    <label class="position-absolute top-0 end-0 mt-1 me-1 delete-overlay">
                                        <input type="checkbox" name="borrar_fotos[]" value="<?= $img['id_imagen'] ?>" class="d-none" onchange="marcarBorrado(<?= $img['id_imagen'] ?>, this)">
                                        <i class="bi bi-x small fw-bold"></i>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div id="contenedor-fotos" class="mb-3">
                        <label class="form-label text-primary small fw-bold">SUBIR NUEVAS IMÁGENES</label>
                        <div class="input-group mb-2">
                            <input type="file" id="primer-input-foto" name="foto[]" class="form-control" accept="image/*" multiple onchange="verificarArchivos(this)">
                        </div>
                    </div>

                    <button type="button" id="btn-mas-fotos" class="btn btn-sm btn-link text-decoration-none p-0 mb-4" onclick="agregarInputFoto()">
                        <i class="bi bi-plus-circle-fill"></i> Añadir más campos de selección
                    </button>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">DUEÑO DEL HOTEL</label>
                        <select name="propietario_uuid" class="form-select">
                            <?php mysqli_data_seek($res_propietarios, 0); while($prop = mysqli_fetch_assoc($res_propietarios)): ?>
                                <option value="<?= $prop['uuid'] ?>" <?= ($hotel['propietario_uuid'] == $prop['uuid']) ? 'selected' : '' ?>>
                                    <?= $prop['first_name'] . ' ' . $prop['last_name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="d-flex gap-3">

            <a href="hoteles.php" class="btn btn-light w-50 fw-bold py-2 rounded-pill border shadow-sm text-muted">
            CANCELAR
            </a>
            
            <button type="submit" class="btn btn-primary w-50 fw-bold py-2 rounded-pill shadow-sm">
        ACTUALIZAR INFORMACIÓN
            </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Marcar fotos para desactivar
function marcarBorrado(id, checkbox) {
    const div = document.getElementById('foto_' + id);
    checkbox.checked ? div.classList.add('marked-delete') : div.classList.remove('marked-delete');
}

// Lógica del botón inteligente
function verificarArchivos(input) {
    const btn = document.getElementById('btn-mas-fotos');
    if (input.files.length > 0) {
        btn.style.display = 'inline-block'; // Mostrar si hay archivos
    } else {
        btn.style.display = 'none'; // Ocultar si se quitan
    }
}

function agregarInputFoto() {
    const contenedor = document.getElementById('contenedor-fotos');
    const nuevoDiv = document.createElement('div');
    nuevoDiv.className = 'input-group mb-2 animate__animated animate__fadeIn';
    nuevoDiv.innerHTML = '<input type="file" name="foto[]" class="form-control" accept="image/*" multiple>';
    contenedor.appendChild(nuevoDiv);
}

// Ubicación Fetch
async function cargarUbicacion(id, tipo, selectDestino, idPreseleccionado = null) {
    if(!id) return;
    const body = tipo === 'estado' ? 'pais_id=' + id : 'estado_id=' + id;
    const res = await fetch('../auth/get_locations.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    });
    const data = await res.text();
    selectDestino.innerHTML = data;
    if(idPreseleccionado) selectDestino.value = idPreseleccionado;
}

const paisSel = document.getElementById('pais');
const estadoSel = document.getElementById('estado');
const ciudadSel = document.getElementById('ciudad');

paisSel.addEventListener('change', () => {
    cargarUbicacion(paisSel.value, 'estado', estadoSel);
    ciudadSel.innerHTML = '<option>Selecciona un estado</option>';
});
estadoSel.addEventListener('change', () => cargarUbicacion(estadoSel.value, 'ciudad', ciudadSel));

window.addEventListener('load', async () => {
    await cargarUbicacion(paisSel.value, 'estado', estadoSel, '<?= $hotel['state_id'] ?>');
    await cargarUbicacion('<?= $hotel['state_id'] ?>', 'ciudad', ciudadSel, '<?= $hotel['city_id'] ?>');
});
</script>

</body>
</html>