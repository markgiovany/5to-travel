<?php
session_start();
include("config/config.php");

// Verificación de sesión
if (!isset($_SESSION['user_uuid'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_uuid'];

/* 1. CONSULTA: DATOS DEL USUARIO */
$query_user = "SELECT u.first_name, u.last_name, e.email, t.telefono 
               FROM usr_users u 
               LEFT JOIN usr_emails e ON u.uuid = e.user_uuid 
               LEFT JOIN usr_telefonos t ON u.uuid = t.user_uuid 
               WHERE u.uuid = '$user_id'";
$user = mysqli_fetch_assoc(mysqli_query($config, $query_user));

/* 2. CONSULTA: VISTOS RECIENTES */
$result_recientes = mysqli_query($config, "
    SELECT c.nombre, ch.precio, i.url_imagen 
    FROM vistos_recientes v
    JOIN catalogo c ON v.id_catalogo = c.id_catalogo
    JOIN cat_catalogo_habitacion ch ON c.id_catalogo = ch.id_catalogo
    LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
    WHERE v.user_id = '$user_id'
    GROUP BY c.id_catalogo
    ORDER BY v.fecha DESC LIMIT 4
");

/* 3. CONSULTA: HISTORIAL DE RESERVAS */
$result_historial = mysqli_query($config, "
    SELECT c.nombre, ch.precio, i.url_imagen, r.fecha_entrada, r.fecha_salida
    FROM res_reserva r
    JOIN cat_catalogo_habitacion ch ON r.id_habitacion = ch.id_habitacion
    JOIN catalogo c ON ch.id_catalogo = c.id_catalogo
    LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
    WHERE r.user_uuid = '$user_id'
    ORDER BY r.fecha_entrada DESC
");

/* 4. CONSULTA: MÉTODOS DE PAGO (Solo activos: id_status = 1) */
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
        
        /* Sidebar Estética */
        .sidebar { height: 100vh; padding: 25px; background: #ffffff !important; border-right: 1px solid #e5e7eb; position: fixed; width: 280px; z-index: 100; }
        .sidebar .nav-link { width: 100%; margin-bottom: 10px; border-radius: 12px; border: none; background: transparent; color: #64748b; padding: 12px 20px; font-weight: 600; text-align: left; transition: 0.3s; }
        .sidebar .nav-link:hover { background: #f8fafc; color: var(--accent-blue); }
        .sidebar .nav-link.active { background: var(--accent-blue); color: white; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25); }
        
        /* Contenedor Principal */
        .main-content { margin-left: 280px; padding: 40px; background: linear-gradient(135deg, #0f2027, #203a43, #2c5364); min-height: 100vh; }
        .card-profile { border-radius: 24px; background: white; box-shadow: 0 20px 40px rgba(0,0,0,0.3); border: none; padding: 35px; }
        
        /* Listas y Filas */
        .item-row { background: #ffffff; border-radius: 16px; padding: 18px; margin-bottom: 15px; border: 1px solid #f1f5f9; transition: 0.3s; display: flex; align-items: center; }
        .item-row:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        
        /* Elementos de Perfil */
        .profile-img { width: 120px; height: 120px; border-radius: 50%; border: 5px solid var(--accent-blue); object-fit: cover; box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        .form-control, .form-select { border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px; background: #fcfcfc; }
        .form-control:focus { border-color: var(--accent-blue); box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1); background: white; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="text-center mb-5">
            <h3 class="fw-bold text-primary">5to Travel</h3>
            <small class="text-muted">Panel de Usuario</small>
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
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo $user['first_name']; ?>" disabled required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Apellido</label>
                            <input type="text" name="apellido" class="form-control" value="<?php echo $user['last_name']; ?>" disabled required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" disabled required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Teléfono (10 dígitos)</label>
                            <input type="text" name="telefono" class="form-control" value="<?php echo $user['telefono']; ?>" 
                                   disabled required maxlength="10" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-2">
                        <button type="button" id="btnEdit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm" onclick="habilitarEdicion()">
                            <i class="bi bi-pencil-square me-2"></i>Editar mi información
                        </button>
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
                <h4 class="mb-4 fw-bold"><i class="bi bi-credit-card-2-back me-2 text-primary"></i>Gestión de Pagos</h4>
                
                <div class="mb-5">
                    <?php if(mysqli_num_rows($result_pago) > 0): ?>
                        <?php while($p = mysqli_fetch_assoc($result_pago)): ?>
                            <div class="item-row shadow-sm justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3">
                                        <i class="bi bi-wallet2 text-primary h4 mb-0"></i>
                                    </div>
                                    <div>
                                        <span class="d-block fw-bold"><?php echo $p['nombre_titular']; ?></span>
                                        <small class="text-muted">Tarjeta: •••• <?php echo substr($p['datos_encriptados'], -4); ?></small>
                                    </div>
                                </div>
                                <a href="auth/eliminar_pago.php?id=<?php echo $p['id_metodo_guardado']; ?>" class="btn btn-outline-danger btn-sm px-4 rounded-pill">Suspender</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted"><p>No tienes tarjetas activas guardadas.</p></div>
                    <?php endif; ?>
                </div>

                <div class="bg-light p-4 rounded-4 border">
                    <h5 class="mb-4 fw-bold">Vincular Nueva Tarjeta</h5>
                    <form action="auth/agregar_pago.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Nombre del Titular</label>
                                <input type="text" name="titular" class="form-control" placeholder="Nombre completo" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tipo</label>
                                <select name="id_metodo_pago" class="form-select">
                                    <option value="1">Visa</option>
                                    <option value="2">MasterCard</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Número de la Tarjeta</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                    <input type="text" name="numero" class="form-control border-start-0" 
                                           placeholder="16 dígitos numéricos" required maxlength="16"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 16);">
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <button class="btn btn-dark w-100 py-3 fw-bold rounded-pill shadow">
                                    Guardar y Activar Tarjeta
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section id="recientes" class="seccion-content" style="display:none;">
            <div class="card-profile">
                <h4 class="mb-4 fw-bold">Vistos recientemente</h4>
                <div class="row g-3">
                <?php while($r = mysqli_fetch_assoc($result_recientes)): ?>
                    <div class="col-md-6">
                        <div class="item-row">
                            <img src="<?php echo $r['url_imagen']; ?>" width="80" height="80" class="rounded-4 me-3" style="object-fit: cover;">
                            <div>
                                <h6 class="mb-1 fw-bold"><?php echo $r['nombre']; ?></h6>
                                <span class="badge bg-success bg-opacity-10 text-success fs-6">$<?php echo number_format($r['precio'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>
            </div>
        </section>

        <section id="historial" class="seccion-content" style="display:none;">
            <div class="card-profile">
                <h4 class="mb-4 fw-bold">Mi Historial de Viajes</h4>
                <?php while($h = mysqli_fetch_assoc($result_historial)): ?>
                    <div class="item-row justify-content-between">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo $h['url_imagen']; ?>" width="100" height="75" class="rounded-3 me-3" style="object-fit: cover;">
                            <div>
                                <h6 class="mb-1 fw-bold text-primary"><?php echo $h['nombre']; ?></h6>
                                <small class="text-muted d-block mb-1"><i class="bi bi-calendar3 me-1"></i> <?php echo $h['fecha_entrada']; ?> - <?php echo $h['fecha_salida']; ?></small>
                                <span class="fw-bold">$<?php echo number_format($h['precio'], 2); ?></span>
                            </div>
                        </div>
                        <span class="badge rounded-pill bg-primary px-3 py-2">Completado</span>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

    </main>

    <script>
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
    </script>
</body>
</html>