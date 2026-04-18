<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['user_uuid']) || $_SESSION['role'] !== 'propietario') {
    header("Location: ../auth/login.php");
    exit();
}

$propietario_uuid = $_SESSION['user_uuid'];
$nombre_usuario = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : "Esteban";

/* HOTELS + IMAGE */
$sql_hoteles = "
SELECT c.*,
(
    SELECT url_imagen 
    FROM cat_imagen ci 
    WHERE ci.id_catalogo = c.id_catalogo 
    AND ci.status = 'active'
    LIMIT 1
) AS imagen
FROM catalogo c
WHERE c.propietario_uuid = '$propietario_uuid'
ORDER BY c.id_catalogo DESC
";

$res_hoteles = mysqli_query($config, $sql_hoteles);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Propietario</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
:root { --blue-dark: #4b62f4; --sidebar-width: 250px; }

body { background-color:#f4f6f9; display:flex; min-height:100vh; overflow-x:hidden; }

.sidebar {
    width: var(--sidebar-width);
    background: white;
    border-right: 1px solid #dee2e6;
    position: fixed;
    height: 100%;
    z-index: 1000;
}

.sidebar .nav-link {
    color: #333;
    padding: 12px 20px;
    font-weight: 500;
}

.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    background: #e9ecef;
    color: var(--blue-dark);
    border-left: 4px solid var(--blue-dark);
}

.main-content {
    margin-left: var(--sidebar-width);
    flex-grow: 1;
    padding: 20px;
    width: 100%;
}

.top-bar {
    background: var(--blue-dark);
    color: white;
    margin: -20px -20px 20px -20px;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Estilos extra para que el modal combine con Agregar Hotel */
.modal-content { border-radius: 15px !important; overflow: hidden; border: none; }
.modal-header { background-color: var(--blue-dark) !important; color: white; }
.badge-custom { background-color: var(--blue-dark); color: white; border-radius: 50px; padding: 5px 15px; }
</style>
</head>

<body>

<div class="sidebar d-flex flex-column p-2">
    <div class="text-center py-3">
        <img src="../imagenes/brooking.png" width="160">
    </div>

    <ul class="nav nav-pills flex-column mb-auto">
        <li><a href="propietario_dashboard.php" class="nav-link active">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a></li>

        <li><a href="mis_reservaciones.php" class="nav-link">
            <i class="bi bi-calendar-check me-2"></i> Reservaciones
        </a></li>

        <li><a href="mis_pagos.php" class="nav-link">
            <i class="bi bi-cash-coin me-2"></i> Pagos
        </a></li>
    </ul>

    <hr>

    <div class="p-2">
        <a href="../auth/logout.php" class="btn btn-danger w-100">
            <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
        </a>
    </div>
</div>

<div class="main-content">

<div class="top-bar shadow-sm">
    <h5 class="mb-0">Panel de Control</h5>
    <span>Bienvenido, <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong></span>
</div>

<div class="card shadow-sm border-0">

<div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
    <h5 class="mb-0 fw-bold">Listado de Hoteles</h5>

    <div>
        <a href="agregar_hotel.php" class="btn btn-primary btn-sm rounded-pill px-3">
            + Agregar Nuevo Hotel
        </a>

        <a href="agregar_habitacion.php" class="btn btn-success btn-sm rounded-pill px-3 ms-2">
            + Agregar Habitación
        </a>
    </div>
</div>

<div class="card-body table-responsive">

<table class="table table-hover align-middle">

<thead class="table-light">
<tr>
    <th>Imagen</th>
    <th>Nombre</th>
    <th>Descripción</th>
    <th>Tipos</th>
    <th>Precio</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($res_hoteles)): ?>

<?php
$id = $row['id_catalogo'];

/* TIPOS - CORREGIDO nombre_tipo */
$sql_tipos = "
SELECT t.nombre_tipo 
FROM cat_catalogo_habitacion ch
INNER JOIN cat_tipo t ON t.id_tipo = ch.id_tipo
WHERE ch.id_catalogo = '$id'
";

$res_tipos = mysqli_query($config, $sql_tipos);
$res_tipos_modal = mysqli_query($config, $sql_tipos);

/* IMÁGENES */
$sql_imgs = "
SELECT url_imagen 
FROM cat_imagen 
WHERE id_catalogo = '$id' 
AND status = 'active'
";

$res_imgs = mysqli_query($config, $sql_imgs);
?>

<tr style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#modal<?php echo $id; ?>">

<td>
<?php if(!empty($row['imagen'])): ?>
    <img src="../<?php echo $row['imagen']; ?>" width="50" height="50" style="object-fit:cover;border-radius:6px;">
<?php else: ?>
    <div style="width:50px;height:50px;background:#ccc;border-radius:6px;"></div>
<?php endif; ?>
</td>

<td class="fw-bold"><?php echo htmlspecialchars($row['nombre']); ?></td>

<td class="text-muted small">
<?php echo htmlspecialchars(substr($row['descripcion'],0,80)); ?>...
</td>

<td>
<?php while($tipo = mysqli_fetch_assoc($res_tipos)): ?>
    <span class="badge bg-primary me-1"><?php echo htmlspecialchars($tipo['nombre_tipo']); ?></span>
<?php endwhile; ?>
</td>

<td>
<?php if(!empty($row['precio_min']) && !empty($row['precio_max'])): ?>
    <span class="badge bg-success">
        $<?php echo number_format($row['precio_min']); ?> - $<?php echo number_format($row['precio_max']); ?>
    </span>
<?php else: ?>
    <span class="text-muted small">Sin precio</span>
<?php endif; ?>
</td>

<td>
    <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-warning btn-sm">Editar</a>
    <a href="eliminar.php?id=<?php echo $id; ?>" class="btn btn-danger btn-sm">Borrar</a>
</td>

</tr>

<div class="modal fade" id="modal<?php echo $id; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-building me-2"></i> <?php echo htmlspecialchars($row['nombre']); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 text-dark">
                
                <div class="mb-4 p-3 bg-light rounded-3 border-start border-4 border-primary">
                    <h6 class="fw-bold text-primary mb-2"><i class="bi bi-info-circle me-2"></i>Descripción</h6>
                    <p class="mb-0 text-muted" style="line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($row['descripcion'])); ?>
                    </p>
                </div>

                <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-images me-2"></i>Galería de Fotos</h6>
                <div class="row g-2 mb-4">
                    <?php if(mysqli_num_rows($res_imgs) > 0): ?>
                        <?php while($img = mysqli_fetch_assoc($res_imgs)): ?>
                            <div class="col-4 col-md-3">
                                <img src="../<?php echo $img['url_imagen']; ?>" 
                                     class="img-fluid rounded shadow-sm border"
                                     style="height:100px; width:100%; object-fit:cover;">
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-3 bg-light rounded">
                            <span class="text-muted small">No hay imágenes disponibles</span>
                        </div>
                    <?php endif; ?>
                </div>

                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-tag me-2"></i>Categorías Asignadas</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php if(mysqli_num_rows($res_tipos_modal) > 0): ?>
                        <?php while($tipo = mysqli_fetch_assoc($res_tipos_modal)): ?>
                            <span class="badge badge-custom">
                                <?php echo htmlspecialchars($tipo['nombre_tipo']); ?>
                            </span>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <span class="text-muted small">Sin categorías asignadas</span>
                    <?php endif; ?>
                </div>

            </div>

            <div class="modal-footer border-0 bg-light mt-2">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cerrar</button>
                <a href="editar.php?id=<?php echo $id; ?>" class="btn btn-primary px-4 rounded-pill">
                    <i class="bi bi-pencil me-1"></i> Editar Información
                </a>
            </div>

        </div>
    </div>
</div>

<?php endwhile; ?>

</tbody>
</table>

</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>