<?php
session_start();
include("../config/conexion.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$where_clauses = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conexion, $_GET['search']);
    $where_clauses[] = "(c.nombre LIKE '%$search%' OR u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%')";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Mantenemos tu conteo profesional
$query = "SELECT c.id_catalogo, c.nombre, c.descripcion, u.first_name, u.last_name,
          (SELECT COUNT(*) FROM cat_catalogo_habitacion ch WHERE ch.id_catalogo = c.id_catalogo) as total_hab
          FROM catalogo c
          LEFT JOIN usr_users u ON c.propietario_uuid = u.uuid 
          $where_sql
          ORDER BY c.id_catalogo DESC";

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
    <title>Brooking | Gestión de Hoteles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../styles/admin_dashboard.css">
    <style>
        /* Un pequeño detalle para que sepa que es clickeable */
        .hotel-link {
            text-decoration: none;
            transition: 0.2s;
        }
        .hotel-link:hover {
            opacity: 0.7;
        }
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
            <li><a href="reservas.php" class="nav-link"><i class="bi bi-calendar-check"></i> Reservas</a></li>
        </ul>
        <div class="p-3 border-top">
            <a href="../auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <h2 class="fw-bold m-0">Gestión de Hoteles</h2>
                <a href="add_hotel.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-plus-lg"></i> Agregar Hotel
                </a>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3 text-muted">Admin <strong><?php echo $_SESSION['first_name'] ?? 'Admin'; ?></strong></span>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['first_name'] ?? 'Admin'; ?>&background=6f42c1&color=fff" class="rounded-circle" width="40">
            </div>
        </div>

        <div class="card mb-4 border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-10">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 rounded-end-pill py-2" 
                                   placeholder="Buscar por hotel o dueño..." 
                                   value="<?php echo $_GET['search'] ?? ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark w-100 rounded-pill py-2">Filtrar</button>
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
                                <th>Nombre del Hotel</th>
                                <th>Propietario</th>
                                <th class="text-center">Habitaciones</th>
                                <th class="text-center pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($hotel = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td>
                                    <a href="habitaciones.php?id=<?php echo $hotel['id_catalogo']; ?>" class="hotel-link">
                                        <div class="fw-bold text-primary"><?php echo $hotel['nombre']; ?></div>
                                        <div class="text-muted small"><?php echo substr($hotel['descripcion'], 0, 50); ?>...</div>
                                    </a>
                                </td>
                                <td>
                                    <div class="small fw-bold">
                                        <?php echo (!empty($hotel['first_name'])) ? $hotel['first_name'] . " " . $hotel['last_name'] : '<span class="text-muted italic">Sin asignar</span>'; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="habitaciones.php?id=<?php echo $hotel['id_catalogo']; ?>" class="text-decoration-none">
                                        <span class="badge bg-light text-dark border rounded-pill px-3 fw-normal">
                                            <i class="bi bi-door-closed me-1"></i> <?php echo $hotel['total_hab']; ?> habs.
                                        </span>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="delete_hotel.php?id=<?php echo $hotel['id_catalogo']; ?>" class="btn btn-sm text-danger" onclick="return confirm('¿Borrar este hotel?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <a href="edit_hotel.php?id=<?php echo $hotel['id_catalogo']; ?>" class="btn btn-sm text-primary ms-1">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
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