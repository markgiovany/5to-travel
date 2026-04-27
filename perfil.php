<?php
session_start();
include("config/config.php");

if (!isset($_SESSION['user_uuid'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_uuid'];

/* --- LÓGICA PARA REGISTRAR VISTO RECIENTE --- */
if (isset($_GET['id'])) {
    $id_view = mysqli_real_escape_string($config, $_GET['id']);
    $fecha_actual = date("Y-m-d H:i:s");
    mysqli_query($config, "INSERT INTO vistos_recientes (user_id, id_catalogo, fecha) 
                           VALUES ('$user_id', '$id_view', '$fecha_actual') 
                           ON DUPLICATE KEY UPDATE fecha = '$fecha_actual'");
}

/* 1. CONSULTA: DATOS DEL USUARIO */
$query_user = "SELECT u.first_name, u.last_name, e.email, t.telefono 
                FROM usr_users u 
                LEFT JOIN usr_emails e ON u.uuid = e.user_uuid 
                LEFT JOIN usr_telefonos t ON u.uuid = t.user_uuid 
                WHERE u.uuid = '$user_id'";
$user = mysqli_fetch_assoc(mysqli_query($config, $query_user));

/* 2. CONSULTA: VISTOS RECIENTES */
$result_recientes = mysqli_query($config, "
    SELECT c.id_catalogo, c.nombre, c.descripcion,
    (SELECT MIN(precio) FROM cat_catalogo_habitacion WHERE id_catalogo = c.id_catalogo) as precio_min, 
    i.url_imagen 
    FROM vistos_recientes v
    JOIN catalogo c ON v.id_catalogo = c.id_catalogo
    LEFT JOIN (
        SELECT id_catalogo, MAX(url_imagen) as url_imagen 
        FROM cat_imagen 
        GROUP BY id_catalogo
    ) i ON c.id_catalogo = i.id_catalogo
    WHERE v.user_id = '$user_id'
    GROUP BY c.id_catalogo
    ORDER BY v.fecha DESC LIMIT 4
");

/* 3. CONSULTA: HISTORIAL */
$result_historial = mysqli_query($config, "
    SELECT 
        r.id_reserva, 
        c.nombre as hotel_nombre, 
        ch.nombre as nombre_habitacion, 
        ch.precio, 
        i.url_imagen, 
        r.fecha_entrada, 
        r.fecha_salida
    FROM res_reserva r
    JOIN cat_catalogo_habitacion ch ON r.id_habitacion = ch.id_habitacion
    JOIN catalogo c ON ch.id_catalogo = c.id_catalogo
    LEFT JOIN (
        SELECT id_catalogo, MAX(url_imagen) as url_imagen 
        FROM cat_imagen 
        GROUP BY id_catalogo
    ) i ON c.id_catalogo = i.id_catalogo
    WHERE r.user_uuid = '$user_id'
    GROUP BY r.id_reserva 
    ORDER BY r.fecha_entrada DESC
");

/* 4. CONSULTA: MÉTODOS DE PAGO */
$result_pago = mysqli_query($config, "SELECT * FROM usr_billetera WHERE user_uuid = '$user_id' AND id_status = 1");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario - 5to Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root { --accent-blue: #0ea5e9; --main-bg: #0f2027; }
        body { font-family: 'Inter', sans-serif; background: #f4f7f6; }
        .sidebar { height: 100vh; padding: 25px; background: #ffffff !important; border-right: 1px solid #e5e7eb; position: fixed; width: 280px; z-index: 100; }
        .sidebar .nav-link { width: 100%; margin-bottom: 10px; border-radius: 12px; border: none; background: transparent; color: #64748b; padding: 12px 20px; font-weight: 600; text-align: left; transition: 0.3s; }
        .sidebar .nav-link:hover { background: #f8fafc; color: var(--accent-blue); }
        .sidebar .nav-link.active { background: var(--accent-blue); color: white; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25); }
        .main-content { margin-left: 280px; padding: 40px; background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); min-height: 100vh; }
        .card-profile { border-radius: 24px; background: white; box-shadow: 0 20px 40px rgba(0,0,0,0.3); border: none; padding: 35px; }
        .item-row { background: #ffffff; border-radius: 16px; padding: 18px; margin-bottom: 15px; border: 1px solid #f1f5f9; transition: 0.3s; display: flex; align-items: center; }
        .item-row:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .profile-img { width: 120px; height: 120px; border-radius: 50%; border: 5px solid var(--accent-blue); object-fit: cover; box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        .form-control { border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px; background: #fcfcfc; }
        .table-habitaciones { font-size: 0.85rem; border-radius: 10px; overflow: hidden; }
        .table-habitaciones thead { background: #f8fafc; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="text-center mb-5">
            <h3 class="fw-bold text-primary">PERFIL</h3>
            <small class="text-muted">Tus Datos</small>
        </div>
        <button class="nav-link active" onclick="mostrar('perfil', this)"><i class="bi bi-person me-2"></i> Mi Perfil</button>
        <button class="nav-link" onclick="mostrar('recientes', this)"><i class="bi bi-eye me-2"></i> Recientes</button>
        <button class="nav-link" onclick="mostrar('pagos', this)"><i class="bi bi-credit-card me-2"></i> Métodos de Pago</button>
        <button class="nav-link" onclick="mostrar('historial', this)"><i class="bi bi-calendar-check me-2"></i> Mis Reservas</button>
        <div class="mt-auto pt-5">
            <hr>
            <a href="home.php" class="btn btn-outline-danger w-100 rounded-pill fw-bold">Salir al Inicio</a>
        </div>
    </nav>

    <main class="main-content">
        <section id="perfil" class="seccion-content">
            <div class="card-profile text-center mx-auto" style="max-width: 600px;">
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="profile-img mb-4">
                <h4 class="fw-bold mb-4"><?php echo $user['first_name']." ".$user['last_name']; ?></h4>
                <form id="formUser" action="auth/actualizar_usuario.php" method="POST" class="text-start">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-bold">Nombre</label><input type="text" name="nombre" class="form-control" value="<?php echo $user['first_name']; ?>" disabled required></div>
                        <div class="col-md-6"><label class="form-label small fw-bold">Apellido</label><input type="text" name="apellido" class="form-control" value="<?php echo $user['last_name']; ?>" disabled required></div>
                        <div class="col-12"><label class="form-label small fw-bold">Correo Electrónico</label><input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" disabled required></div>
                        <div class="col-12"><label class="form-label small fw-bold">Teléfono</label><input type="tel" name="telefono" class="form-control" value="<?php echo $user['telefono']; ?>" disabled required minlength="10" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')"></div>
                    </div>
                    <div class="mt-4 pt-2">
                        <button type="button" id="btnEdit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill" onclick="habilitarEdicion()">Editar mi información</button>
                        <div id="btnSave" style="display:none;" class="row g-2">
                            <div class="col-9"><button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-pill">Guardar Cambios</button></div>
                            <div class="col-3"><button type="button" class="btn btn-light w-100 py-3 rounded-pill" onclick="location.reload()">X</button></div>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <section id="pagos" class="seccion-content" style="display:none;">
            <div class="card-profile">
                <h4 class="mb-4 fw-bold">Gestión de Pagos</h4>
                <form id="formPago" action="auth/agregar_pago.php" method="POST" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="titular" class="form-control" placeholder="Nombre en la tarjeta" required>
                        </div>
                        <div class="col-md-6">
                            <input type="tel" id="numTarjeta" name="numero" class="form-control" placeholder="Número de Tarjeta (16 dígitos)" maxlength="16" required oninput="validarNumeros(this)">
                        </div>
                        <div class="col-md-4">
                            <input type="tel" id="expiracion" name="expiracion" class="form-control" placeholder="MM/AA" maxlength="5" required oninput="formatearFecha(this)">
                        </div>
                        <div class="col-md-4">
                            <input type="tel" id="cvv" name="cvv" class="form-control" placeholder="CVV" maxlength="3" required oninput="validarNumeros(this)">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" id="btnAnadir" class="btn btn-primary w-100 h-100 rounded-pill fw-bold" disabled>Añadir</button>
                        </div>
                    </div>
                </form>
                <hr class="my-4">
                <?php while($p = mysqli_fetch_assoc($result_pago)): ?>
                    <div class="item-row justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3"><i class="bi bi-wallet2 text-primary"></i></div>
                            <div><span class="d-block fw-bold"><?php echo $p['nombre_titular']; ?></span><small>•••• <?php echo substr($p['datos_encriptados'], -4); ?></small></div>
                        </div>
                        <a href="auth/eliminar_pago.php?p_id=<?php echo $p['id_metodo_guardado']; ?>&u_id=<?php echo $user_id; ?>" class="btn btn-sm btn-outline-danger">Eliminar</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section id="recientes" class="seccion-content" style="display:none;">
            <div class="card-profile">
                <h4 class="mb-4 fw-bold">Vistos recientemente</h4>
                <div class="row g-3">
                    <?php if(mysqli_num_rows($result_recientes) > 0): ?>
                        <?php while($r = mysqli_fetch_assoc($result_recientes)): 
                            $id_h = $r['id_catalogo'];
                            $q_habs = mysqli_query($config, "SELECT nombre, capacidad, precio, COUNT(*) as cantidad 
                                                             FROM cat_catalogo_habitacion 
                                                             WHERE id_catalogo = '$id_h' 
                                                             GROUP BY nombre, capacidad, precio");
                            $habitaciones = [];
                            while($hb = mysqli_fetch_assoc($q_habs)) { $habitaciones[] = $hb; }
                        ?>
                            <div class="col-md-6">
                                <div class="item-row" style="cursor:pointer;" 
                                     onclick='verFichaHotel(<?php echo json_encode($r["nombre"]); ?>, <?php echo json_encode($r["url_imagen"]); ?>, <?php echo json_encode($r["descripcion"]); ?>, <?php echo json_encode($habitaciones); ?>)'>
                                    <img src="<?php echo !empty($r['url_imagen']) ? $r['url_imagen'] : 'imagenes/placeholder.jpg'; ?>" width="80" height="80" class="rounded-4 me-3" style="object-fit: cover;">
                                    <div><h6 class="mb-1 fw-bold"><?php echo $r['nombre']; ?></h6><span class="badge bg-success bg-opacity-10 text-success">$<?php echo number_format($r['precio_min'], 2); ?></span></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center w-100">Aún no has visto ningún destino.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="historial" class="seccion-content" style="display:none;">
            <div class="card-profile">
                <h4 class="mb-4 fw-bold">Mi Historial de Viajes</h4>
                <?php while($h = mysqli_fetch_assoc($result_historial)): ?>
                    <div class="item-row justify-content-between" style="cursor:pointer;" 
                         onclick="verDetalleReserva('<?php echo addslashes($h['hotel_nombre']); ?>', '<?php echo $h['fecha_entrada']; ?>', '<?php echo $h['fecha_salida']; ?>', '<?php echo number_format($h['precio'], 2); ?>', '<?php echo $h['url_imagen']; ?>', '<?php echo $h['nombre_habitacion']; ?>')">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo !empty($h['url_imagen']) ? $h['url_imagen'] : 'imagenes/placeholder.jpg'; ?>" width="100" height="75" class="rounded-3 me-3" style="object-fit: cover;">
                            <div>
                                <h6 class="mb-1 fw-bold text-primary"><?php echo $h['hotel_nombre']; ?></h6>
                                <small class="text-muted d-block"><?php echo $h['nombre_habitacion']; ?></small>
                                <small class="text-muted"><?php echo $h['fecha_entrada']; ?></small>
                            </div>
                        </div>
                        <span class="badge rounded-pill bg-primary">Completado</span>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </main>

    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                <div id="mImg" style="height: 250px; background-size: cover; background-position: center;"></div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h3 id="mNombre" class="fw-bold text-primary"></h3>
                        <p id="mUbicacion" class="text-muted small"><i class="bi bi-geo-alt"></i> <span>Ubicación registrada</span></p>
                    </div>
                    <div class="row mb-4 text-center bg-light p-3 rounded-4">
                        <div class="col-6 border-end">
                            <small class="text-muted d-block">Fecha Entrada</small>
                            <strong id="mCheckIn">--</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Fecha Salida</small>
                            <strong id="mCheckOut">--</strong>
                        </div>
                    </div>
                    <h6 class="fw-bold" id="mTituloDesc">Descripción</h6>
                    <p id="mDescripcion" class="text-muted small mb-4"></p>
                    <h6 class="fw-bold mb-3" id="mTituloHab">Habitaciones</h6>
                    <div class="table-responsive">
                        <table class="table table-habitaciones border">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Capacidad</th>
                                    <th>Disponibilidad</th>
                                    <th>Precio Unitario</th>
                                </tr>
                            </thead>
                            <tbody id="mHabitacionesBody"></tbody>
                        </table>
                    </div>
                    <button class="btn btn-dark w-100 rounded-pill mt-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- LOGICA DE NAVEGACION ---
        function mostrar(id, btn) {
            document.querySelectorAll('.seccion-content').forEach(s => s.style.display = 'none');
            document.getElementById(id).style.display = 'block';
            document.querySelectorAll('.nav-link').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        function habilitarEdicion() {
            document.querySelectorAll("#formUser input").forEach(input => input.disabled = false);
            document.getElementById("btnEdit").style.display = "none";
            document.getElementById("btnSave").style.display = "flex";
        }

        // --- VALIDACIONES DE TARJETA ---
        function validarNumeros(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            validarFormularioPago();
        }

        function formatearFecha(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value.length >= 2) {
                input.value = input.value.slice(0, 2) + '/' + input.value.slice(2, 4);
            }
            // Salto automático al CVV tras poner los 2 dígitos del mes y 2 del año
            if (input.value.length === 5) {
                document.getElementById('cvv').focus();
            }
            validarFormularioPago();
        }

        function validarFormularioPago() {
            const num = document.getElementById('numTarjeta').value.length === 16;
            const exp = document.getElementById('expiracion').value.length === 5;
            const cvv = document.getElementById('cvv').value.length === 3;
            document.getElementById('btnAnadir').disabled = !(num && exp && cvv);
        }

        // --- LOGICA DE MODALES ---
        function verFichaHotel(nombre, imagen, desc, habs) {
            document.getElementById('mNombre').innerText = nombre;
            document.getElementById('mImg').style.backgroundImage = "url('" + (imagen || 'imagenes/placeholder.jpg') + "')";
            document.getElementById('mDescripcion').innerText = desc || 'Sin descripción adicional.';
            document.getElementById('mTituloHab').innerText = "Habitaciones Disponibles";
            const body = document.getElementById('mHabitacionesBody');
            body.innerHTML = '';
            if(habs && habs.length > 0) {
                habs.forEach(h => {
                    body.innerHTML += `
                        <tr>
                            <td><strong>${h.nombre}</strong></td>
                            <td>${h.capacidad} pers.</td>
                            <td><span class="badge bg-primary rounded-pill">${h.cantidad} disponibles</span></td>
                            <td class="text-success fw-bold">MXN$ ${parseFloat(h.precio).toLocaleString()}</td>
                        </tr>`;
                });
            }
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        }

        function verDetalleReserva(hotel, entrada, salida, precio, imagen, tipoHab) {
            document.getElementById('mNombre').innerText = hotel;
            document.getElementById('mImg').style.backgroundImage = "url('" + (imagen || 'imagenes/placeholder.jpg') + "')";
            document.getElementById('mCheckIn').innerText = entrada;
            document.getElementById('mCheckOut').innerText = salida;
            document.getElementById('mDescripcion').innerText = "Reserva confirmada.";
            document.getElementById('mTituloHab').innerText = "Habitación Reservada";
            document.getElementById('mHabitacionesBody').innerHTML = `
                <tr>
                    <td><strong>${tipoHab}</strong></td>
                    <td>Estándar</td>
                    <td><span class="badge bg-secondary rounded-pill">1 reservada</span></td>
                    <td class="text-primary fw-bold">$${precio}</td>
                </tr>`;
            new bootstrap.Modal(document.getElementById('modalDetalle')).show();
        }
    </script>
</body>
</html>