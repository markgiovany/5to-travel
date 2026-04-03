<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id_hotel'])) {
    header("Location: hoteles.php");
    exit();
}

$id_hotel = mysqli_real_escape_string($conexion, $_GET['id_hotel']);

$res_h = mysqli_query($conexion, "SELECT nombre FROM catalogo WHERE id_catalogo = '$id_hotel'");
$hotel = mysqli_fetch_assoc($res_h);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $precio = mysqli_real_escape_string($conexion, $_POST['precio']);
    $capacidad = mysqli_real_escape_string($conexion, $_POST['capacidad']);
    $disponibilidad = mysqli_real_escape_string($conexion, $_POST['disponibilidad']);

    $query = "INSERT INTO cat_catalogo_habitacion 
              (id_catalogo, nombre, descripcion, precio, capacidad, disponibilidad, status, id_tipo, id_grupo) 
              VALUES 
              ('$id_hotel', '$nombre', '$descripcion', '$precio', '$capacidad', '$disponibilidad', 'active', 1, 1)";

    if (mysqli_query($conexion, $query)) {
        header("Location: habitaciones.php?id=$id_hotel&msg=added");
        exit();
    } else {
        $error = "Error al guardar: " . mysqli_error($conexion);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Habitación | <?php echo $hotel['nombre']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
</head>
<body class="bg-light p-5">

<div class="container">
    <div class="col-md-6 mx-auto card shadow-sm border-0 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0">Nueva Habitación</h4>
            <span class="badge bg-primary-subtle text-primary"><?php echo $hotel['nombre']; ?></span>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger small"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold small text-muted">NOMBRE / TIPO</label>
                <input type="text" name="nombre" class="form-control rounded-pill" placeholder="Ej. Suite Presidencial" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small text-muted">DESCRIPCIÓN</label>
                <textarea name="descripcion" class="form-control" rows="2" placeholder="¿Qué incluye? (Vista al mar, AC, etc.)"></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-muted">PRECIO X NOCHE</label>
                    <input type="number" name="precio" class="form-control rounded-pill" step="0.01" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small text-muted">CAPACIDAD (PERS)</label>
                    <input type="number" name="capacidad" class="form-control rounded-pill" min="1" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small text-muted">CANTIDAD DE ESTAS HABITACIONES</label>
                <input type="number" name="disponibilidad" class="form-control rounded-pill" placeholder="¿Cuántas de este tipo hay?" required>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary fw-bold rounded-pill">Guardar Habitación</button>
                <a href="habitaciones.php?id=<?php echo $id_hotel; ?>" class="btn btn-link text-muted text-decoration-none small">Cancelar</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>