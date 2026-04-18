<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$uuid_propietario = $_SESSION['user_uuid'];

if (isset($_GET['id'])) {
    $id_hotel = mysqli_real_escape_string($config, $_GET['id']);

    $sql = "SELECT * FROM catalogo 
            WHERE id_catalogo = '$id_hotel' 
            AND propietario_uuid = '$uuid_propietario'";

    $resultado = mysqli_query($config, $sql);
    $hotel = mysqli_fetch_assoc($resultado);

    if (!$hotel) die("Hotel no encontrado");

    // 🔥 CORREGIDO: Traer todos los tipos disponibles
    $sql_all_tipos = "SELECT * FROM cat_tipo";
    $res_all_tipos = mysqli_query($config, $sql_all_tipos);

    // Obtener los tipos que ya tiene este hotel
    $sql_tipos_actuales = "SELECT id_tipo FROM cat_catalogo_habitacion WHERE id_catalogo = '$id_hotel'";
    $res_tipos_actuales = mysqli_query($config, $sql_tipos_actuales);

    $tipos_seleccionados = [];
    while($t = mysqli_fetch_assoc($res_tipos_actuales)) {
        $tipos_seleccionados[] = $t['id_tipo'];
    }

    $sql_imgs = "SELECT url_imagen FROM cat_imagen WHERE id_catalogo = '$id_hotel' AND status = 'active'";
    $res_imgs = mysqli_query($config, $sql_imgs);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id_hotel'];
    $nombre = mysqli_real_escape_string($config, $_POST['nombre']);
    $desc = mysqli_real_escape_string($config, $_POST['descripcion']);
    $precio_min = (float)$_POST['precio_min'];
    $precio_max = (float)$_POST['precio_max'];

    // 1. Actualizar datos básicos
    $sql_update = "UPDATE catalogo SET 
                   nombre='$nombre',
                   descripcion='$desc',
                   precio_min='$precio_min',
                   precio_max='$precio_max'
                   WHERE id_catalogo='$id'
                   AND propietario_uuid='$uuid_propietario'";

    mysqli_query($config, $sql_update);

    // 2. Actualizar tipos (Borrar y volver a insertar)
    mysqli_query($config, "DELETE FROM cat_catalogo_habitacion WHERE id_catalogo = '$id'");
    $tipos = $_POST['tipos'] ?? [];

    foreach ($tipos as $id_tipo) {
        $id_tipo = (int)$id_tipo;
        // 🔥 CORREGIDO: Quitamos 'uuid' para evitar el error de columna desconocida
        mysqli_query($config, "INSERT INTO cat_catalogo_habitacion (id_catalogo, id_tipo) VALUES ('$id', '$id_tipo')");
    }

    // 3. Subir nuevas imágenes si hay
    if (!empty($_FILES['imagenes']['name'][0])) {
        $carpeta = "../imagenes/";
        if (!file_exists($carpeta)) { mkdir($carpeta, 0777, true); }

        foreach ($_FILES['imagenes']['name'] as $i => $name_original) {
            $tmp = $_FILES['imagenes']['tmp_name'][$i];
            if ($_FILES['imagenes']['error'][$i] === 0) {
                $ext = pathinfo($name_original, PATHINFO_EXTENSION);
                $nuevo_nombre = uniqid("hotel_", true) . "." . $ext;
                if (move_uploaded_file($tmp, $carpeta . $nuevo_nombre)) {
                    $ruta_bd = "imagenes/" . $nuevo_nombre;
                    mysqli_query($config, "INSERT INTO cat_imagen (id_catalogo, url_imagen, status) VALUES ('$id', '$ruta_bd', 'active')");
                }
            }
        }
    }

    header("Location: propietario_dashboard.php?mensaje=editado");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Establecimiento</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    :root { --blue-dark: #4b62f4; }
    body { background-color: #f4f6f9; }
    .card { border-radius: 15px; border: none; }
    .btn-primary { background-color: var(--blue-dark); border: none; }
    .img-prev { height: 120px; object-fit: cover; border-radius: 10px; }
    .delete-btn { background: rgba(220, 53, 69, 0.9); color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; text-decoration: none; }
</style>
</head>

<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center mb-4">
                <a href="propietario_dashboard.php" class="btn btn-light rounded-circle me-3 shadow-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0">Editar Establecimiento</h3>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-primary">Información General</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_hotel" value="<?php echo $hotel['id_catalogo']; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Hotel</label>
                            <input class="form-control form-control-lg" name="nombre" value="<?php echo htmlspecialchars($hotel['nombre']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="4" required><?php echo htmlspecialchars($hotel['descripcion']); ?></textarea>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Precio Mínimo</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" name="precio_min" value="<?php echo $hotel['precio_min']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Precio Máximo</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" name="precio_max" value="<?php echo $hotel['precio_max']; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Tipos de habitación</label>
                            <div class="row g-3 bg-light p-3 rounded-3">
                                <?php while($tipo = mysqli_fetch_assoc($res_all_tipos)): ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="tipos[]" 
                                               value="<?php echo $tipo['id_tipo']; ?>" id="tipo<?php echo $tipo['id_tipo']; ?>"
                                               <?php echo in_array($tipo['id_tipo'], $tipos_seleccionados) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="tipo<?php echo $tipo['id_tipo']; ?>">
                                            <?php echo htmlspecialchars($tipo['nombre_tipo']); ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Galería Actual</label>
                            <div class="row g-2">
                                <?php while($img = mysqli_fetch_assoc($res_imgs)): ?>
                                <div class="col-3 position-relative">
                                    <img src="../<?php echo $img['url_imagen']; ?>" class="img-fluid img-prev border">
                                    <a href="eliminar_imagen.php?img=<?php echo urlencode($img['url_imagen']); ?>&id=<?php echo $hotel['id_catalogo']; ?>" 
                                       class="delete-btn position-absolute top-0 end-0 m-1 shadow-sm" title="Eliminar foto">
                                        <i class="bi bi-x"></i>
                                    </a>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Agregar nuevas fotos</label>
                            <input type="file" name="imagenes[]" multiple class="form-control" id="files" accept="image/*">
                        </div>

                        <div class="row preview mb-4" id="preview"></div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-lg fw-bold rounded-pill">Guardar Cambios</button>
                            <a href="propietario_dashboard.php" class="btn btn-light rounded-pill">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('files').addEventListener('change', function(e){
    let preview = document.getElementById('preview');
    preview.innerHTML = "";
    for (let file of e.target.files) {
        let reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML += `
                <div class="col-3 mb-2">
                    <img src="${e.target.result}" class="img-fluid rounded border shadow-sm" style="height:80px; width:100%; object-fit:cover;">
                </div>`;
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>