<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: hoteles.php");
    exit();
}

$id_hotel = mysqli_real_escape_string($conexion, $_GET['id']);

$res_hotel = mysqli_query($conexion, "SELECT nombre FROM catalogo WHERE id_catalogo = '$id_hotel'");
$hotel_info = mysqli_fetch_assoc($res_hotel);

if (!$hotel_info) {
    header("Location: hoteles.php");
    exit();
}

$where_clauses = ["ch.id_catalogo = '$id_hotel'"];

if (isset($_GET['capacidad']) && !empty($_GET['capacidad'])) {
    $capacidad = mysqli_real_escape_string($conexion, $_GET['capacidad']);
    $where_clauses[] = "ch.capacidad = '$capacidad'";
}

if (isset($_GET['precio_range']) && !empty($_GET['precio_range'])) {
    $rango = $_GET['precio_range'];
    
    if ($rango == '1') {
        $where_clauses[] = "ch.precio <= 3000";
    } elseif ($rango == '2') {
        $where_clauses[] = "ch.precio BETWEEN 3001 AND 6000";
    } elseif ($rango == '3') {
        $where_clauses[] = "ch.precio BETWEEN 6001 AND 9000";
    } elseif ($rango == '4') {
        $where_clauses[] = "ch.precio BETWEEN 9001 AND 10000";
    } elseif ($rango == '5') {
        $where_clauses[] = "ch.precio > 10000";
    }
}

$where_sql = " WHERE " . implode(" AND ", $where_clauses);

$query = "SELECT ch.* FROM cat_catalogo_habitacion ch $where_sql ORDER BY ch.precio ASC";
$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    die("Error en la consulta SQL: " . mysqli_error($conexion));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brooking | Habitaciones de <?php echo $hotel_info['nombre']; ?></title>
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
            <a href="../auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <h2 class="fw-bold m-0">Habitaciones: <span class="text-primary"><?php echo $hotel_info['nombre']; ?></span></h2>
                <a href="add_habitacion.php?id_hotel=<?php echo $id_hotel; ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-plus-lg"></i> Agregar Habitación
                </a>
            </div>
            <a href="hoteles.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-arrow-left"></i> Volver a la lista
            </a>
        </div>

        <div class="card mb-4 border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
                <form method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="id" value="<?php echo $id_hotel; ?>">
                    
                    <div class="col-md-5">
                        <label class="small fw-bold text-muted ms-2">CAPACIDAD</label>
                        <select name="capacidad" class="form-select rounded-pill">
                            <option value="">Cualquiera</option>
                            <option value="1" <?php echo (isset($_GET['capacidad']) && $_GET['capacidad'] == '1') ? 'selected' : ''; ?>>1 Persona</option>
                            <option value="2" <?php echo (isset($_GET['capacidad']) && $_GET['capacidad'] == '2') ? 'selected' : ''; ?>>2 Personas</option>
                            <option value="4" <?php echo (isset($_GET['capacidad']) && $_GET['capacidad'] == '4') ? 'selected' : ''; ?>>4 Personas</option>
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="small fw-bold text-muted ms-2">RANGO DE PRECIO</label>
                        <select name="precio_range" class="form-select rounded-pill">
                            <option value="">Todos los precios</option>
                            <option value="1" <?php echo (isset($_GET['precio_range']) && $_GET['precio_range'] == '1') ? 'selected' : ''; ?>>$3,000 o menos</option>
                            <option value="2" <?php echo (isset($_GET['precio_range']) && $_GET['precio_range'] == '2') ? 'selected' : ''; ?>>$3,001 - $6,000</option>
                            <option value="3" <?php echo (isset($_GET['precio_range']) && $_GET['precio_range'] == '3') ? 'selected' : ''; ?>>$6,001 - $9,000</option>
                            <option value="4" <?php echo (isset($_GET['precio_range']) && $_GET['precio_range'] == '4') ? 'selected' : ''; ?>>$9,001 - $10,000</option>
                            <option value="5" <?php echo (isset($_GET['precio_range']) && $_GET['precio_range'] == '5') ? 'selected' : ''; ?>>$10,000 o más</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark w-100 rounded-pill">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card stat-card shadow-sm border-0 rounded-3">
            <div class="card-body p-4"> 
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Descripción de Habitación</th>
                                <th class="text-center">Capacidad</th>
                                <th class="text-center">Precio</th>
                                <th class="text-center pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($resultado) > 0): ?>
                                <?php while($hab = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo $hab['nombre']; ?></div>
                                        <div class="text-muted small"><?php echo $hab['descripcion']; ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border rounded-pill">
                                            <i class="bi bi-people"></i> <?php echo $hab['capacidad']; ?> paxs.
                                        </span>
                                    </td>
                                    <td class="text-center text-success fw-bold">
                                        $<?php echo number_format($hab['precio'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="delete_habitacion.php?name=<?php echo urlencode($hab['nombre']); ?>&id_hotel=<?php echo $id_hotel; ?>" 
                                           class="btn btn-sm text-danger" onclick="return confirm('¿Borrar?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <a href="edit_habitacion.php?name=<?php echo urlencode($hab['nombre']); ?>" class="btn btn-sm text-primary ms-1">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">No se encontraron habitaciones.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>