<?php
session_start();
include("config/conexion.php");

if (!isset($_SESSION['user_uuid'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_uuid'];

/* DATOS USUARIO */
$query_user = "
SELECT u.first_name, u.last_name, e.email, t.telefono
FROM usr_users u
LEFT JOIN usr_emails e ON u.uuid = e.user_uuid
LEFT JOIN usr_telefonos t ON u.uuid = t.user_uuid
WHERE u.uuid = '$user_id'
";
$user = mysqli_fetch_assoc(mysqli_query($conexion, $query_user));

/* RECIENTES */
$result_recientes = mysqli_query($conexion, "
SELECT c.nombre, c.precio, i.url_imagen 
FROM vistos_recientes v
JOIN catalogo c ON v.id_catalogo = c.id_catalogo
LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
WHERE v.user_id = '$user_id'
ORDER BY v.fecha DESC LIMIT 4
");

/* HISTORIAL */
$result_historial = mysqli_query($conexion, "
SELECT c.nombre, c.precio, i.url_imagen, r.fecha_entrada, r.fecha_salida
FROM res_reserva r
JOIN res_habitacion h ON r.id_habitacion = h.id_habitacion
JOIN catalogo c ON h.id_catalogo = c.id_catalogo
LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
WHERE r.user_uuid = '$user_id'
");

/* PAGOS */
$result_pago = mysqli_query($conexion, "
SELECT * FROM metodos_pago WHERE user_id = '$user_id'
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perfil</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

/* SIDEBAR */
.sidebar {
    height: 100vh;
    padding: 20px;
    background: #f1f5f9 !important;
    border-right: 1px solid #ddd;
}

/* BOTONES */
.sidebar button {
    width: 100%;
    margin-bottom: 12px;
    border-radius: 10px;
    border: none;
    background: #e2e8f0;
    color: #333;
    padding: 10px;
    font-weight: 500;
    transition: 0.3s;
}

.sidebar button:hover {
    background: #cbd5e1;
}

.sidebar button.active {
    background: #0ea5e9;
    color: white;
}

/* TARJETAS */
.card {
    border-radius: 15px;
    background: white;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

/* PERFIL */
.profile-img {
    width: 100px;
    border-radius: 50%;
}

</style>

</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 sidebar" style="background:#f1f5f9 !important;">
            <h4>Mi Panel</h4>

            <button class="btn active" onclick="mostrar('perfil', this)">Mi Perfil</button>
            <button class="btn" onclick="mostrar('recientes', this)">Vistos Recientes</button>
            <button class="btn" onclick="mostrar('pagos', this)">Métodos de Pago</button>
            <button class="btn" onclick="mostrar('historial', this)">Historial</button>

            <a href="home.php" class="btn btn-danger mt-3">Salir</a>
        </div>

        <!-- CONTENIDO DERECHO -->
        <div class="col-md-9 content" style="background: linear-gradient(135deg, #0f2027, #203a43, #2c5364) !important; min-height:100vh; padding:30px;">

            <!-- PERFIL -->
            <div id="perfil" class="seccion">
                <div class="card p-4 text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="profile-img mb-3">

                    <h4><?php echo $user['first_name']." ".$user['last_name']; ?></h4>

                    <form id="formUsuario" action="auth/actualizar_usuario.php" method="POST">

                        <input type="text" name="nombre" class="form-control mb-2" value="<?php echo $user['first_name']; ?>" disabled>
                        <input type="text" name="apellido" class="form-control mb-2" value="<?php echo $user['last_name']; ?>" disabled>
                        <input type="email" name="email" class="form-control mb-2" value="<?php echo $user['email']; ?>" disabled>
                        <input type="text" name="telefono" class="form-control mb-2" value="<?php echo $user['telefono']; ?>" disabled>

                        <button type="button" class="btn btn-primary" onclick="editar()">Editar</button>

                        <div id="botonesGuardar" style="display:none;">
                            <button type="submit" class="btn btn-success mt-2">Guardar</button>
                            <button type="button" class="btn btn-secondary mt-2" onclick="cancelar()">Cancelar</button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- RECIENTES -->
            <div id="recientes" class="seccion" style="display:none;">
                <div class="card p-4">
                    <h4>Vistos recientes</h4>

                    <?php while($r = mysqli_fetch_assoc($result_recientes)): ?>
                        <div class="d-flex mb-3">
                            <img src="<?php echo $r['url_imagen']; ?>" width="100" class="rounded me-3">
                            <div>
                                <h6><?php echo $r['nombre']; ?></h6>
                                <span>$<?php echo number_format($r['precio'],2); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>

                </div>
            </div>

            <!-- PAGOS -->
            <div id="pagos" class="seccion" style="display:none;">
                <div class="card p-4">
                    <h4>Métodos de pago</h4>

                    <?php while($p = mysqli_fetch_assoc($result_pago)): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?php echo $p['tipo']; ?> ****<?php echo substr($p['numero'], -4); ?></span>

                            <a href="auth/eliminar_pago.php?id=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm">
                                Eliminar
                            </a>
                        </div>
                    <?php endwhile; ?>

                    <hr>

                    <form action="auth/agregar_pago.php" method="POST">
                        <input type="text" name="tipo" class="form-control mb-2" placeholder="Tipo">
                        <input type="text" name="numero" class="form-control mb-2" placeholder="Número">
                        <button class="btn btn-primary">Agregar</button>
                    </form>

                </div>
            </div>

            <!-- HISTORIAL -->
            <div id="historial" class="seccion" style="display:none;">
                <div class="card p-4">
                    <h4>Historial</h4>

                    <?php while($h = mysqli_fetch_assoc($result_historial)): ?>
                        <div class="d-flex mb-3">
                            <img src="<?php echo $h['url_imagen']; ?>" width="100" class="rounded me-3">
                            <div>
                                <h6><?php echo $h['nombre']; ?></h6>
                                <small><?php echo $h['fecha_entrada']." → ".$h['fecha_salida']; ?></small><br>
                                <span>$<?php echo number_format($h['precio'],2); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>

                </div>
            </div>

        </div>

    </div>
</div>

<script>
function mostrar(seccion, boton) {
    document.querySelectorAll('.seccion').forEach(div => div.style.display = 'none');
    document.getElementById(seccion).style.display = 'block';

    document.querySelectorAll('.sidebar button').forEach(btn => btn.classList.remove('active'));
    boton.classList.add('active');
}

function editar() {
    document.querySelectorAll("#formUsuario input").forEach(i => i.disabled = false);
    document.getElementById("botonesGuardar").style.display = "block";
}

function cancelar() {
    location.reload();
}
</script>

</body>
</html>