<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_hotel = $_GET['id'];
    $uuid_propietario = $_SESSION['user_uuid'];

    $sql = "SELECT * FROM catalogo WHERE id_catalogo = '$id_hotel' AND propietario_uuid = '$uuid_propietario'";
    $resultado = mysqli_query($conexion, $sql);
    $hotel = mysqli_fetch_assoc($resultado);

    if (!$hotel) {
        die("Hotel no encontrado o no tienes permiso.");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id_hotel'];
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $desc = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $precio = $_POST['precio'];

    $sql_update = "UPDATE catalogo SET 
                   nombre = '$nombre', 
                   descripcion = '$desc', 
                   precio = '$precio' 
                   WHERE id_catalogo = '$id'";

    if (mysqli_query($conexion, $sql_update)) {
        header("Location: propietario_dashboard.php?mensaje=editado");
        exit();
    } else {
        echo "Error al actualizar: " . mysqli_error($conexion);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Hotel - 5TO-TRAVEL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow border-0">
                   <div class="card-header bg-info text-white py-3">
                      <h5 class="mb-0 fw-bold">Modificar Hotel: <?php echo htmlspecialchars($hotel['nombre']); ?></h5>
                        </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="id_hotel" value="<?php echo $hotel['id_catalogo']; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre</label>
                                <input type="text" name="nombre" class="form-control" 
                                       value="<?php echo htmlspecialchars($hotel['nombre']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="4"><?php echo htmlspecialchars($hotel['descripcion']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Precio por noche</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="precio" class="form-control" 
                                           value="<?php echo $hotel['precio']; ?>" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-info text-white fw-bold w-100">Actualizar Datos</button>
                                <a href="propietario_dashboard.php" class="btn btn-light">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>