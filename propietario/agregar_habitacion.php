<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$uuid = $_SESSION['user_uuid'];

/* =========================
   HOTELES DEL PROPIETARIO
========================= */
$hoteles = mysqli_query($config, "
    SELECT id_catalogo, nombre 
    FROM catalogo 
    WHERE propietario_uuid='$uuid'
");

/* =========================
   HOTEL SELECCIONADO
========================= */
$hotel_id = isset($_GET['hotel']) ? (int)$_GET['hotel'] : 0;

/* =========================
   TIPOS DEL HOTEL
========================= */
$tipos_disponibles = null;

if ($hotel_id > 0) {
    // 🔥 CORREGIDO: t.nombre por t.nombre_tipo
    $tipos_disponibles = mysqli_query($config, "
        SELECT t.id_tipo, t.nombre_tipo
        FROM cat_tipo t
        INNER JOIN cat_catalogo_habitacion ch 
            ON ch.id_tipo = t.id_tipo
        WHERE ch.id_catalogo = '$hotel_id'
    ");
}

/* =========================
   GUARDAR HABITACIÓN
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_catalogo = (int)$_POST['id_catalogo'];
    $numero = mysqli_real_escape_string($config, $_POST['numero']);
    $precio = mysqli_real_escape_string($config, $_POST['precio']);
    $estado = mysqli_real_escape_string($config, $_POST['estado']);
    $id_tipo = (int)$_POST['tipo'];

    /* VALIDACIÓN: el tipo debe existir en ese hotel */
    $valida = mysqli_query($config, "
        SELECT 1 
        FROM cat_catalogo_habitacion 
        WHERE id_catalogo='$id_catalogo' 
        AND id_tipo='$id_tipo'
        LIMIT 1
    ");

    if (mysqli_num_rows($valida) == 0) {
        die("❌ Este tipo NO está asignado a este hotel");
    }

    /* 🏨 INSERT HABITACIÓN */
    mysqli_query($config, "
        INSERT INTO res_habitacion 
        (id_catalogo, numero, precio, estado, id_tipo)
        VALUES 
        ('$id_catalogo','$numero','$precio','$estado','$id_tipo')
    ");

    header("Location: propietario_dashboard.php?mensaje=habitacion_agregada");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agregar Habitación | Panel Propietario</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    :root { --blue-dark: #4b62f4; }
    body { background-color: #f4f7f6; }
    .card { border: none; border-radius: 15px; }
    .btn-primary { background-color: var(--blue-dark); border: none; }
    .form-label { font-weight: 600; color: #444; }
</style>
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            
            <div class="mb-4 d-flex align-items-center">
                <a href="propietario_dashboard.php" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="mb-0 fw-bold">Nueva Habitación</h3>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">

                    <form method="POST">

                        <div class="mb-4">
                            <label class="form-label text-primary"><i class="bi bi-building me-2"></i>Selecciona el Hotel</label>
                            <select name="id_catalogo" class="form-select form-select-lg" required onchange="location.href='?hotel='+this.value">
                                <option value="">Elegir establecimiento...</option>
                                <?php while($h = mysqli_fetch_assoc($hoteles)): ?>
                                    <option value="<?= $h['id_catalogo'] ?>"
                                        <?= ($hotel_id == $h['id_catalogo']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($h['nombre']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="form-text">Al seleccionar un hotel, se cargarán sus tipos de habitación disponibles.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Número o Identificador</label>
                            <input type="text" name="numero" class="form-control" placeholder="Ej: Hab. 101, Suite A..." required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Precio por noche</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" name="precio" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Estado Inicial</label>
                            <select name="estado" class="form-select">
                                <option value="Disponible">🟢 Disponible</option>
                                <option value="No disponible">🔴 No disponible</option>
                            </select>
                        </div>

                        <div class="mb-5">
                            <label class="form-label text-primary"><i class="bi bi-tag me-2"></i>Categoría de Habitación</label>
                            <select name="tipo" class="form-select" required>
                                <option value="">-- Seleccione categoría --</option>
                                <?php if($tipos_disponibles && mysqli_num_rows($tipos_disponibles) > 0): ?>
                                    <?php while($t = mysqli_fetch_assoc($tipos_disponibles)): ?>
                                        <option value="<?= $t['id_tipo'] ?>">
                                            <?= htmlspecialchars($t['nombre_tipo']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option disabled>Selecciona un hotel primero</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-lg fw-bold rounded-pill">
                                <i class="bi bi-check-circle me-2"></i>Registrar Habitación
                            </button>
                            <a href="propietario_dashboard.php" class="btn btn-light rounded-pill">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>