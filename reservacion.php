<?php
session_start();
include("config/config.php");

if (!isset($_SESSION['user_uuid'])) {
    header("Location: login.php");
    exit();
}

$user_uuid = $_SESSION['user_uuid'];
$id_habitacion_uuid = isset($_GET['id_habitacion']) ? mysqli_real_escape_string($config, $_GET['id_habitacion']) : null;

// Verificar billetera
$query_pago = "SELECT id_metodo_guardado FROM usr_billetera WHERE user_uuid = '$user_uuid' AND id_status = 1 LIMIT 1";
$res_pago = mysqli_query($config, $query_pago);
$tiene_metodo_pago = mysqli_num_rows($res_pago) > 0;

// Info Habitación
$query_info = "SELECT h.*, c.nombre as hotel_nombre, img.url_imagen 
               FROM cat_catalogo_habitacion h
               INNER JOIN catalogo c ON h.id_catalogo = c.id_catalogo
               LEFT JOIN cat_imagen img ON h.id_habitacion = img.id_habitacion
               WHERE h.uuid = '$id_habitacion_uuid' LIMIT 1";

$res_info = mysqli_query($config, $query_info);
$habitacion = mysqli_fetch_assoc($res_info);

if (!$habitacion) { die("Error: Habitación no encontrada."); }

$query_user = "SELECT u.first_name, u.last_name, e.email, t.telefono 
               FROM usr_users u
               LEFT JOIN usr_emails e ON u.uuid = e.user_uuid
               LEFT JOIN usr_telefonos t ON u.uuid = t.user_uuid
               WHERE u.uuid = '$user_uuid' LIMIT 1";
$res_user = mysqli_query($config, $query_user);
$user_data = mysqli_fetch_assoc($res_user);

$hoy = date('Y-m-d');

$fecha_entrada = $_GET['fecha_entrada'] ?? $hoy;
$fecha_salida  = $_GET['fecha_salida'] ?? date('Y-m-d', strtotime('+1 day'));
$adultos       = (int)($_GET['adultos'] ?? 1);
$ninos         = (int)($_GET['ninos'] ?? 0);
$total_personas = $adultos + $ninos;

$f1 = new DateTime($fecha_entrada);
$f2 = new DateTime($fecha_salida);
$noches = $f1->diff($f2)->days ?: 1;

$capacidad_max = (int)$habitacion['capacidad']; 
$cargo_extra = 0;

if ($total_personas > $capacidad_max) {
    $personas_excedentes = $total_personas - $capacidad_max;
    $cargo_extra = $personas_excedentes * 500 * $noches;
}

$total_hospedaje = $habitacion['precio'] * $noches;
$total_estancia  = $total_hospedaje + $cargo_extra;

$precio_base = $total_estancia / 1.16; 
$iva_desglosado = $total_estancia - $precio_base;

$nombre_completo = trim(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? ''));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva - BookingEngineer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .cursor-pointer { cursor: pointer; }
        #payment-warning { display: none; }
        .input-edit { border: 1px solid #dee2e6 !important; background-color: #fff !important; }
        input:invalid { border-color: #dc3545 !important; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <form id="updateFilters" method="GET" style="display:none;">
        <input type="hidden" name="id_habitacion" value="<?= $id_habitacion_uuid ?>">
        <input type="hidden" name="fecha_entrada" id="get_entrada">
        <input type="hidden" name="fecha_salida" id="get_salida">
        <input type="hidden" name="adultos" id="get_adultos">
        <input type="hidden" name="ninos" id="get_ninos">
    </form>

    <div class="row g-4">
        <section class="col-lg-7">
            <form action="auth/procesar_pago.php" method="POST" id="reservaForm" class="bg-white p-4 rounded-4 shadow-sm border">
                <input type="hidden" name="id_habitacion" value="<?= $habitacion['id_habitacion'] ?>">
                <input type="hidden" name="id_catalogo" value="<?= $habitacion['id_catalogo'] ?>">
                <input type="hidden" name="fecha_entrada" value="<?= $fecha_entrada ?>">
                <input type="hidden" name="fecha_salida" value="<?= $fecha_salida ?>">
                <input type="hidden" name="cantidad_personas" value="<?= $total_personas ?>">
                <input type="hidden" name="total_pago" value="<?= $total_estancia ?>">

                <div class="mb-4">
                    <h2 class="h4 fw-bold mb-3">1. Confirmación de reserva</h2>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="small text-muted">Entrada</label>
                            <input type="date" class="form-control rounded-pill input-edit" id="view_entrada" 
                                   value="<?= $fecha_entrada ?>" min="<?= $hoy ?>" onchange="recalcular()">
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">Salida</label>
                            <input type="date" class="form-control rounded-pill input-edit" id="view_salida" 
                                   value="<?= $fecha_salida ?>" min="<?= date('Y-m-d', strtotime($fecha_entrada . ' +1 day')) ?>" onchange="recalcular()">
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">Total Huéspedes</label>
                            <input type="number" class="form-control rounded-pill input-edit" id="view_adultos" value="<?= $total_personas ?>" min="1" onchange="recalcular()">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h2 class="h4 fw-bold mb-3">2. Datos de Facturación</h2>
                    <input type="text" name="nombre" class="form-control rounded-pill mb-3" value="<?= htmlspecialchars($nombre_completo) ?>" required placeholder="Nombre completo">
                    
                    <input type="email" name="email" class="form-control rounded-pill mb-3" 
                           value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required placeholder="correo@ejemplo.com">
                    
                    <input type="tel" id="inputTel" name="telefono" class="form-control rounded-pill mb-3" 
                           value="<?= htmlspecialchars($user_data['telefono'] ?? '') ?>" 
                           required maxlength="10" pattern="[0-9]{10}" 
                           oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                           title="Deben ser exactamente 10 dígitos numéricos" placeholder="Teléfono a 10 dígitos">
                </div>

                <div class="mb-4">
                    <h2 class="h4 fw-bold mb-3">3. Método de Pago</h2>
                    <div class="d-flex gap-3">
                        <label class="border p-3 rounded-4 flex-fill text-center cursor-pointer">
                            <input type="radio" name="metodo" value="1" id="radioTarjeta" checked onchange="validarMetodo()"> <span>Tarjeta</span>
                        </label>
                        <label class="border p-3 rounded-4 flex-fill text-center cursor-pointer">
                            <input type="radio" name="metodo" value="2" id="radioEfectivo" onchange="validarMetodo()"> <span>Efectivo</span>
                        </label>
                    </div>
                    <div id="payment-warning" class="alert alert-warning mt-3 rounded-4 small">
                        Sin datos de pago asociados, ve a <a href="perfil.php">"Mi perfil"</a> para agregar uno.
                    </div>
                </div>

                <button type="submit" id="btnConfirmar" class="btn btn-primary w-100 py-3 rounded-pill fw-bold">
                    Pagar MXN$ <?= number_format($total_estancia, 2) ?>
                </button>
            </form>
        </section>

        <aside class="col-lg-5">
            <div class="bg-white p-4 rounded-4 shadow-sm border">
                <h3 class="h5 fw-bold mb-3">Resumen de Estancia</h3>
                <?php $img_src = (!empty($habitacion['url_imagen'])) ? $habitacion['url_imagen'] : 'imagenes/generica_habitacion.jpg'; ?>
                <img src="<?= $img_src ?>" class="img-fluid rounded-4 mb-3 w-100" style="height: 200px; object-fit: cover;" onerror="this.src='https://totalplanning.cat/wp-content/uploads/2023/11/Image_not_available.png'">
                
                <p class="mb-1 text-primary fw-bold"><?= htmlspecialchars($habitacion['hotel_nombre']) ?></p>
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between small"><span>Noches:</span><span><?= $noches ?></span></div>
                    <div class="d-flex justify-content-between small"><span>Subtotal:</span><span>$<?= number_format($total_hospedaje, 2) ?></span></div>
                    <?php if ($cargo_extra > 0): ?>
                        <div class="d-flex justify-content-between small text-danger"><span>Cargo extra:</span><span>$<?= number_format($cargo_extra, 2) ?></span></div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between small text-info"><span>IVA (16%):</span><span>$<?= number_format($iva_desglosado, 2) ?></span></div>
                    <hr>
                    <div class="d-flex justify-content-between h4 fw-bold text-primary"><span>Total:</span><span>$<?= number_format($total_estancia, 2) ?></span></div>
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
    const tieneMetodo = <?= json_encode($tiene_metodo_pago) ?>;
    
    function recalcular() {
        const entrada = document.getElementById('view_entrada').value;
        const salida = document.getElementById('view_salida').value;
        
        if(salida <= entrada) {
            alert("La fecha de salida debe ser posterior a la de entrada");
            return;
        }

        document.getElementById('get_entrada').value = entrada;
        document.getElementById('get_salida').value = salida;
        document.getElementById('get_adultos').value = document.getElementById('view_adultos').value;
        document.getElementById('get_ninos').value = 0; 
        document.getElementById('updateFilters').submit();
    }

    function validarMetodo() {
        const radioTarjeta = document.getElementById('radioTarjeta');
        const btn = document.getElementById('btnConfirmar');
        const warning = document.getElementById('payment-warning');
        if (radioTarjeta.checked && !tieneMetodo) {
            btn.disabled = true;
            warning.style.display = 'block';
        } else {
            btn.disabled = false;
            warning.style.display = 'none';
        }
    }

    // Validación extra antes de enviar
    document.getElementById('reservaForm').onsubmit = function(e) {
        const tel = document.getElementById('inputTel').value;
        if(tel.length !== 10) {
            alert("El teléfono debe tener exactamente 10 dígitos.");
            return false;
        }
    };

    window.onload = validarMetodo;
</script>
</body>
</html>