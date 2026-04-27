<?php
session_start();
include("config/config.php"); 

$uuid_hotel = isset($_GET['uuid']) ? mysqli_real_escape_string($config, $_GET['uuid']) : '';
$url_regresar = isset($_SESSION['user_uuid']) ? 'home.php' : 'index.php';

if (!empty($uuid_hotel)) {
    $sql_detalle = "SELECT c.*,  u.direccion, ciu.name AS nombre_ciudad, est.name AS nombre_estado, pais.name AS nombre_pais, i.url_imagen
                    FROM catalogo c
                    LEFT JOIN cat_ubicacion u ON c.id_ubicacion = u.id_ubicacion
                    LEFT JOIN cities ciu ON u.city_id = ciu.id
                    LEFT JOIN states est ON ciu.state_id = est.id
                    LEFT JOIN countries pais ON est.country_id = pais.id
                    LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
                    WHERE c.uuid = '$uuid_hotel'";
    
    $res_detalle = mysqli_query($config, $sql_detalle);
    $hotel = mysqli_fetch_assoc($res_detalle);

} else {
    header("Location: home.php");
    exit();
}

if ($hotel) {
    // Guardamos el ID numérico en una variable para las consultas de abajo
    $id_hotel = $hotel['id_catalogo']; 
    
    // Ahora las consultas que usen $id_hotel en las líneas 124 y 125 funcionarán
}
$query_imgs = "SELECT url_imagen 
            FROM cat_imagen 
            WHERE id_catalogo = $id_hotel 
            ORDER BY id_imagen ASC 
            LIMIT 3";

$res_imgs = mysqli_query($config, $query_imgs);
// esto llama a las imagenes 
$imagenes = [];
while($img = mysqli_fetch_assoc($res_imgs)){
    $imagenes[] = $img['url_imagen'];
}


if ($id_hotel > 0 && isset($_SESSION['user_uuid'])) {
    $user_id = $_SESSION['user_uuid'];
    $fecha_actual = date("Y-m-d H:i:s");

    $query_visto = "INSERT INTO vistos_recientes (user_id, id_catalogo, fecha) 
                    VALUES ('$user_id', '$id_hotel', '$fecha_actual') 
                    ON DUPLICATE KEY UPDATE fecha = '$fecha_actual'";
    
    mysqli_query($config, $query_visto);
}


?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="stylesheet" href="styles/lugares-info.css">
    <title>PAGINA INFO</title> 
</head>
<body>
    
<header class="main-header">
    <div class="glass-nav container-fluid px-lg-5 d-flex justify-content-between align-items-center py-3 bg-white shadow-sm fixed-top">
        <!-- Lógica: Si hay sesión iniciada va a home.php, si no, al index -->
<?php 
    $enlace_logo = isset($_SESSION['user_uuid']) ? 'home.php' : 'index.php'; 
?>
<a href="<?= $enlace_logo; ?>" class="logo">
    <img src="imagenes/brooking.png" alt="Logo" width="140">
</a>
        <div class="nav-links d-flex align-items-center gap-3">
            <a href="catalogo.php" class="text-decoration-none text-dark fw-medium small">Catálogo</a>
            <a href="centro_de_ayuda.php" class="text-decoration-none text-dark fw-medium small">Centro de ayuda</a>
            <?php if (isset($_SESSION['user_uuid'])): ?>
                <div class="dropdown d-inline-block">
                    <div class="user-pill d-flex align-items-center gap-2 border rounded-pill px-2 py-1" data-bs-toggle="dropdown" role="button">
                        <i class="bi bi-list text-dark"></i>
                        <div class="user-avatar bg-light rounded-circle p-1"><i class="bi bi-person-fill text-secondary"></i></div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><a class="dropdown-item fw-bold" href="perfil.php">Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="favoritos.php">Favoritos</a></li>
                        <li><a class="dropdown-item text-danger" href="auth/logout.php">Cerrar sesión</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle"></i> Login
                </a>
            <?php endif; ?>
        </div>
        </div>
    </div>
</header>


<section class="hero-lugar">
    <div class="container">
        <div class="row g-3">

            <!-- Imagen principal -->
            <div class="col-lg-8">
                <img 
                    src="<?php echo $imagenes[0] ?? 'https://images.unsplash.com/photo-1590490360182-c33d57733427'; ?>" 
                    class="img-fluid gallery-main" 
                    alt="Principal">
            </div>

            <!-- Imágenes secundarias -->
            <div class="col-lg-4 d-flex flex-column justify-content-between">

                <?php if(isset($imagenes[1])): ?>
                    <img src="<?php echo $imagenes[1]; ?>" class="img-fluid gallery-sub mb-2" alt="Sub 1">
                <?php endif; ?>

                <?php if(isset($imagenes[2])): ?>
                    <img src="<?php echo $imagenes[2]; ?>" class="img-fluid gallery-sub" alt="Sub 2">
                <?php endif; ?>

            </div>

        </div>
    </div>
</section>

    <section class="datos-lugar">
        <div class="container">
            <div class="titulo-lugar">
                <h1><?php echo htmlspecialchars($hotel['nombre']); ?></h1>
            </div>

            <div class="row info-extra">
                <div class="col-md-7">
                    <div class="descripcion-lugar">
                        <h2>Conoce más</h2>
                        <p><?php echo htmlspecialchars($hotel['descripcion']); ?></p>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="mapa-card">
                        <h4>Ubicación</h4>
                        <p><?php echo htmlspecialchars($hotel['direccion'])?></p>
                        <p><?php echo htmlspecialchars($hotel['nombre_pais'])?>, <?php echo htmlspecialchars($hotel['nombre_estado'])?>, <?php echo htmlspecialchars($hotel['nombre_ciudad'])?></p>
                    </div>
                </div>
            </div>

            <div class="horarios-lugar">
                <h3>Información de llegada</h3>
                <div class="d-flex gap-4">
                    <div class="horario-item">
                        <strong>Check-in:</strong> 15:00 - 16:00
                    </div>
                    <div class="horario-item">
                        <strong>Check-out:</strong> 13:00
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php 
    $query_hab = "SELECT h.*, i.url_imagen 
    FROM cat_catalogo_habitacion h 
    LEFT JOIN cat_imagen i on h.id_habitacion = i.id_habitacion 
    WHERE h.id_catalogo = $id_hotel AND h.id_status = 1
    AND h.disponibilidad > 0
    GROUP BY h.nombre";
    
    $res_hab = mysqli_query($config, $query_hab);
    ?>
    <div class="container">
    <?php if(mysqli_num_rows($res_hab) > 0): ?>
        
        <div class="row fw-bold mb-3 d-none d-lg-flex border-bottom pb-2">
            <div class="col-lg-3">TIPO DE HABITACIÓN</div>
            <div class="col-lg-4 text-center">DESCRIPCIÓN</div>
            <div class="col-lg-5 text-center">PRECIO</div>
        </div>

        <?php while($hab = mysqli_fetch_assoc($res_hab)): ?>
            <div class="row mb-4 border rounded shadow-sm bg-white overflow-hidden">
                <div class="col-lg-3 p-0 border-end">
                    <div class="tipo-habitaciones">
                        <img src="<?php echo !empty($hab['url_imagen']) ? $hab['url_imagen'] : 'https://images.unsplash.com/photo-1590490360182-c33d57733427'; ?>" class="img-fluid w-100" style="height: 160px; object-fit: cover;">
                        <div class="p-2">
                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($hab['nombre']); ?></h6>
                            <p class="small text-muted mb-3">Capacidad: <?php echo $hab['capacidad']; ?> pers.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 p-4 border-end bg-light-subtle">
                    <p class="text-muted small">
                        <?php echo htmlspecialchars($hab['descripcion'] ?? 'Sin descripción disponible.'); ?>
                    </p>
                </div>

                <div class="col-lg-5 p-4 d-flex flex-column justify-content-center align-items-end">
                    <div class="text-end mb-3">
                        <h3 class="fw-bold mb-0">MXN$ <?php echo number_format($hab['precio'], 2); ?></h3>
                    </div>
                        <?php if (isset($_SESSION['user_uuid'])): ?>
                            <a href="reservation.hmtl?uuid=<?php echo $hab['uuid']; ?>" class="btn btn-info text-white">Reservar</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-info text-white">Reservar</a>
                        <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>

    <?php else: ?>
        
        <div class="row">
            <div class="col-12">
                <div class="alert alert-light border shadow-sm p-5 text-center rounded-4">
                    <i class="bi bi-door-closed text-muted" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 fw-bold">No hay habitaciones disponibles</h4>
                    <p class="text-muted">Lo sentimos, este establecimiento no tiene habitaciones registradas para reservar en línea actualmente.</p>
                    <a href="home.php" class="btn btn-outline-primary btn-sm rounded-pill mt-2">
                        <i class="bi bi-arrow-left"></i> Volver a buscar
                    </a>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>
    
    <footer class="main-footer">

        <div class="footer-grid">
            <div class="footer-column">
                <h3>Soporte</h3>
                <ul>
                    <li><a href="#">Centro de ayuda</a></li>
                    <li><a href="#">Información de seguridad</a></li>
                    <li><a href="#">Opciones de cancelación</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Compañía</h3>
                <ul>
                    <li><a href="#">Sobre nosotros</a></li>
                    <li><a href="#">Política de privacidad</a></li>
                    <li><a href="#">Blog de la Comunidad</a></li>
                    <li><a href="#">Términos de servicio</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Contacto</h3>
                <ul>
                    <li><a href="#">Preguntas frecuentes</a></li>
                    <li><a href="#">Ponte en contacto</a></li>
                    <li><a href="#">Patrocinadores</a></li>
                </ul>
            </div>

            <div class="footer-column">
            <h3>Redes Sociales</h3>
                <div class="social-icons">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-twitter-x"></i></a>
                    <a href="#"><i class="bi bi-tiktok"></i></a>
                    <a href="#"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
        </div>
 
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>