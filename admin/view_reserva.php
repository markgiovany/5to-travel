<?php
session_start();
include("../config/config.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin' || !isset($_GET['uuid'])) {
    header("Location: reservaciones.php"); 
    exit();
}

$uuid_url = mysqli_real_escape_string($config, $_GET['uuid']);

$query = "SELECT r.*, s.nombre as estado_nombre, u.first_name, u.last_name, e.email, t.telefono,
          c.nombre as hotel_nombre, ub.direccion, h.nombre as habitacion_nombre, h.precio, h.capacidad,
          p.monto as monto_pagado, p.fecha_pago, b.id_metodo_pago
          FROM res_reserva r
          INNER JOIN usr_users u ON r.user_uuid = u.uuid
          LEFT JOIN usr_emails e ON u.uuid = e.user_uuid
          LEFT JOIN usr_telefonos t ON u.uuid = t.user_uuid
          INNER JOIN catalogo c ON r.id_catalogo = c.id_catalogo
          LEFT JOIN cat_ubicacion ub ON c.id_ubicacion = ub.id_ubicacion
          INNER JOIN cat_catalogo_habitacion h ON r.id_habitacion = h.id_habitacion
          INNER JOIN status s ON r.id_status = s.id_status
          LEFT JOIN res_registro_pago p ON r.id_reserva = p.id_reserva
          LEFT JOIN usr_billetera b ON u.uuid = b.user_uuid AND b.metod_predeterminado = 1
          WHERE r.uuid_reserva = '$uuid_url'"; // <--- Filtro por el nombre correcto de columna

$res = mysqli_query($config, $query);
$data = mysqli_fetch_assoc($res);

if (!$data) { 
    die("Error: No se encontró información para el UUID: " . htmlspecialchars($uuid_url)); 
}

$ref = "BK-" . str_pad($data['id_reserva'], 7, "0", STR_PAD_LEFT);

$status_clean = mb_strtolower(trim($data['estado_nombre']), 'UTF-8');
$color_dot = '#6c757d'; // Default gris
if (str_contains($status_clean, 'confirm')) { $color_dot = '#198754'; } // Verde
elseif (str_contains($status_clean, 'revis') || str_contains($status_clean, 'pendien')) { $color_dot = '#ffc107'; } // Amarillo
elseif (str_contains($status_clean, 'cancel')) { $color_dot = '#dc3545'; } // Rojo

$email_body = "Reserva: $ref%0D%0ACliente: " . urlencode($data['first_name'] . " " . $data['last_name']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva <?php echo $ref; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 15px; }
        .header-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .title-group { display: flex; align-items: center; gap: 12px; }
        .badge-status {
            background-color: transparent !important;
            border-color: #dee2e6 !important;
            display: inline-flex; 
            align-items: center;
            padding: 6px 14px; 
            font-size: 0.85rem;
            align-self: center;
            margin-bottom: -4px;
        }
        @media print {
            .no-print { display: none !important; }
            body { background-color: white !important; }
            .card { box-shadow: none !important; border: 1px solid #eee !important; }
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="header-container">
        <div class="title-group">
            <h2 class="fw-bold m-0 text-dark">Reserva <?php echo $ref; ?></h2>
            <div class="badge border rounded-pill text-dark fw-normal badge-status bg-white shadow-sm no-print">
                <i class="bi bi-circle-fill me-2" style="color: <?php echo $color_dot; ?>; font-size: 0.5rem;"></i>
                <?php echo htmlspecialchars($data['estado_nombre']); ?>
            </div>
        </div>
        <a href="reservaciones.php" class="btn btn-sm btn-outline-secondary rounded-pill px-4 no-print shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver a la lista
        </a>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda: Información Detallada -->
        <div class="col-lg-8">
            <!-- Card: Cliente -->
            <div class="card shadow-sm p-4 mb-4">
                <h6 class="text-muted fw-bold text-uppercase mb-4 small">Información del Cliente</h6>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="text-muted small d-block">Pasajero</label>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['first_name'] . " " . $data['last_name']); ?></span>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="text-muted small d-block">E-mail</label>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['email']); ?></span>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small d-block">Teléfono de contacto</label>
                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($data['telefono'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Card: Alojamiento -->
            <div class="card shadow-sm p-4">
                <h6 class="text-muted fw-bold text-uppercase mb-4 small">Detalle del Alojamiento</h6>
                <h4 class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($data['hotel_nombre']); ?></h4>
                <p class="text-muted small mb-4"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($data['direccion']); ?></p>
                
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="bg-light p-3 rounded-3" style="min-width: 250px;">
                        <span class="fw-bold d-block text-dark"><?php echo htmlspecialchars($data['habitacion_nombre']); ?></span>
                        <span class="text-muted small">Reservación para <strong class="text-dark"><?php echo (int)$data['cantidad_personas']; ?></strong> personas</span>
                    </div>
                    <div class="d-flex gap-3 text-center">
                        <div class="border rounded-3 p-2 px-3 bg-white shadow-sm">
                            <label class="text-muted d-block small" style="font-size: 0.65rem;">ENTRADA</label>
                            <span class="fw-bold text-primary"><?php echo date('d M, Y', strtotime($data['fecha_entrada'])); ?></span>
                        </div>
                        <div class="border rounded-3 p-2 px-3 bg-white shadow-sm">
                            <label class="text-muted d-block small" style="font-size: 0.65rem;">SALIDA</label>
                            <span class="fw-bold text-primary"><?php echo date('d M, Y', strtotime($data['fecha_salida'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Pago y Acciones -->
        <div class="col-lg-4">
            <div class="card shadow-sm p-4 mb-4">
                <h6 class="text-muted fw-bold text-uppercase mb-4 small">Resumen de Pago</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Monto total:</span>
                    <span class="fw-bold text-dark h5 mb-0">$<?php echo number_format($data['monto_pagado'] ?? 0, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-muted small">Método:</span>
                    <span class="small fw-medium text-dark">Tarjeta de Crédito</span>
                </div>
                <hr class="my-3">
                <div class="text-center py-2">
                    <p class="text-muted small mb-1">Estado del Pago</p>
                    <?php if ($data['monto_pagado'] > 0): ?>
                        <h3 class="fw-bold text-success mb-1">PAGADO</h3>
                        <div class="text-muted small">
                            <i class="bi bi-calendar-event me-1"></i><?php echo date('d/m/Y', strtotime($data['fecha_pago'])); ?><br>
                            <i class="bi bi-clock me-1"></i><?php echo date('H:i \h\s', strtotime($data['fecha_pago'])); ?>
                        </div>
                    <?php else: ?>
                        <h3 class="fw-bold text-warning mb-1">PENDIENTE</h3>
                        <small class="text-muted">Esperando confirmación del banco</small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="no-print d-grid gap-2">
                <button onclick="window.print()" class="btn btn-outline-dark rounded-pill py-2 shadow-sm">
                    <i class="bi bi-printer me-2"></i> Imprimir Voucher
                </button>
                <a href="mailto:<?php echo htmlspecialchars($data['email']); ?>?subject=Reserva <?php echo $ref; ?>&body=<?php echo $email_body; ?>" class="btn btn-primary rounded-pill py-2 shadow-sm">
                    <i class="bi bi-envelope me-2"></i> Contactar Cliente
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>