<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$propietario_uuid = $_SESSION['user_uuid'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $precio = mysqli_real_escape_string($conexion, $_POST['precio']);

    $sql = "INSERT INTO catalogo (propietario_uuid, nombre, descripcion, precio) 
            VALUES ('$propietario_uuid', '$nombre', '$descripcion', '$precio')";

    if (mysqli_query($conexion, $sql)) {
        header("Location: propietario_dashboard.php?mensaje=agregado");
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
    <title>Agregar Hotel - BookingEngineer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card { border-radius: 10px; }
        .btn { border-radius: 8px; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-building-add me-2"></i>Registrar Nuevo Hotel</h5>
                </div>
                <div class="card-body p-4">
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="agregar_hotel.php">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Hotel</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Hotel Paraíso" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción Corta</label>
                            <textarea name="descripcion" class="form-control" rows="4" placeholder="Cuéntanos sobre el hotel..." required></textarea>
                        </div>

                          <div class="mb-3">
                            <label class="form-label fw-bold">Precio por Noche ($)</label>
                            <input type="number" name="precio" class="form-control" placeholder="0.00" step="0.01" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Guardar Hotel</button>
                            <a href="propietario_dashboard.php" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>