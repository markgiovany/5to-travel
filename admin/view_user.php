<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$uuid = isset($_GET['u']) ? mysqli_real_escape_string($config, $_GET['u']) : null;

if (!$uuid) {
    header("Location: users.php");
    exit();
}

$query = "SELECT u.*, l.role, l.id_status, l.suspension_reason, e.email, t.telefono,
          (SELECT COUNT(*) FROM catalogo WHERE propietario_uuid = u.uuid) as total_hoteles
          FROM usr_users u
          LEFT JOIN usr_emails e ON u.uuid = e.user_uuid 
          LEFT JOIN usr_users_login l ON u.uuid = l.user_uuid
          LEFT JOIN usr_telefonos t ON u.uuid = t.user_uuid
          WHERE u.uuid = '$uuid'";

$res = mysqli_query($config, $query);
$user = mysqli_fetch_assoc($res);

if (!$user) {
    header("Location: users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brooking | Detalle de Usuario</title>
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
            <li><a href="users.php" class="nav-link active"><i class="bi bi-people"></i> Usuarios</a></li>
            <li><a href="hoteles.php" class="nav-link"><i class="bi bi-building"></i> Hoteles</a></li>
            <li><a href="reservaciones.php" class="nav-link"><i class="bi bi-calendar-check"></i> Reservaciones</a></li>
        </ul>
        <div class="p-3 border-top">
            <a href="../auth/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold m-0">Información del Usuario</h2>
            <a href="users.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="bi bi-arrow-left"></i> Volver a la lista
            </a>
        </div>

        <div class="card stat-card shadow-sm border-0 col-md-8 mx-auto rounded-3 mt-4">
            <div class="card-body p-4">
                
                <div class="d-flex align-items-center mb-4">
                    <img src="https://ui-avatars.com/api/?name=<?php echo $user['first_name']; ?>&background=random" class="rounded-circle me-3" width="60">
                    <div>
                        <h4 class="mb-0 fw-bold"><?php echo $user['first_name'] . " " . $user['last_name']; ?></h4>
                        <span class="badge bg-light text-dark border px-3 rounded-pill text-capitalize">
                            <?php echo $user['role']; ?>
                        </span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">CORREO ELECTRÓNICO</label>
                        <p class="form-control-plaintext border-bottom"><?php echo $user['email'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">TELÉFONO</label>
                        <p class="form-control-plaintext border-bottom"><?php echo $user['telefono'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">FECHA DE REGISTRO</label>
                        <p class="form-control-plaintext border-bottom"><?php echo date('d M, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small fw-bold">ÚLTIMA ACTUALIZACIÓN</label>
                        <p class="form-control-plaintext border-bottom"><?php echo !empty($user['updated_at']) ? date('d M, Y H:i', strtotime($user['updated_at'])) : 'N/A'; ?></p>
                    </div>
                </div>

                <!-- Info específica según ROL -->
                <?php if($user['role'] == 'user'): ?>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold"><i class="bi bi-credit-card me-2"></i>MÉTODOS DE PAGO</label>
                        <div class="p-3 bg-light rounded border text-muted italic">No hay métodos de pago agregados.</div>
                    </div>
                <?php elseif($user['role'] == 'propietario'): ?>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold"><i class="bi bi-building me-2"></i>PROPIEDADES</label>
                        <div class="p-3 bg-light rounded border">
                            Este propietario tiene <strong><?php echo $user['total_hoteles']; ?></strong> hoteles registrados.
                        </div>
                    </div>
                <?php endif; ?>

                <hr class="my-4">

                <!-- Sección de Suspensión armonizada -->
                <div class="p-4 rounded-3 <?php echo ($user['id_status'] == 4) ? 'bg-light border-danger border' : 'bg-light border'; ?>">
                    <h5 class="fw-bold mb-3">Gestión de Estado</h5>
                    
                    <?php if($user['id_status'] != 4): ?>
                        <form action="status_user.php?u=<?php echo $uuid; ?>&to=4" method="POST">
                            <input type="hidden" name="from_profile" value="1">
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">MOTIVO DE LA SUSPENSIÓN</label>
                                <textarea name="reason" class="form-control" rows="2" required placeholder="Escriba el motivo aquí..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning fw-bold px-4 rounded-pill">
                                <i class="bi-lock"></i> SUSPENDER USUARIO
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger border-0 shadow-sm">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Usuario Suspendido:</strong> <?php echo $user['suspension_reason']; ?>
                        </div>
                        <form action="status_user.php?u=<?php echo $uuid; ?>&to=1" method="POST">
                            <input type="hidden" name="from_profile" value="1">
                            <button type="submit" class="btn btn-success fw-bold px-4 rounded-pill">
                                <i class="bi bi-check-circle me-2"></i>REACTIVAR USUARIO
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="mt-4 text-center">
                    <a href="edit_user.php?u=<?php echo $uuid; ?>" class="btn btn-link text-primary text-decoration-none fw-bold">
                        <i class="bi bi-pencil-square me-1"></i> Editar información básica
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>