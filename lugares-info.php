<?php
session_start();
include("config/config.php"); 

$id_hotel = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_hotel > 0) {
    $sql_detalle = "SELECT c.*,  u.direccion, ciu.name AS nombre_ciudad, est.name AS nombre_estado, pais.name AS nombre_pais, i.url_imagen
                    FROM catalogo c
                    LEFT JOIN cat_ubicacion u ON c.id_ubicacion = u.id_ubicacion
                    LEFT JOIN cities ciu ON u.city_id = ciu.id
                    LEFT JOIN states est ON ciu.state_id = est.id
                    LEFT JOIN countries pais ON est.country_id = pais.id
                    LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
                    WHERE c.id_catalogo = $id_hotel";
    
    $res_detalle = mysqli_query($config, $sql_detalle);
    $hotel = mysqli_fetch_assoc($res_detalle);

} else {
    header("Location: home.php");
    exit();
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
        <div class="glass-nav">
            <a href="home.php" class="logo">
                <img src="imagenes/brooking.png" alt="Logo">
            </a>
            <div class="nav-links">
                <a href="catalogo.php">Catálogo</a>
                <a href="favoritos.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="bi bi-heart-fill"></i> Mis Favoritos
                </a>
                <div class="dropdown">
                    <div class="user-pill" data-bs-toggle="dropdown" aria-expanded="false" role="button">
                        <i class="bi bi-list"></i>
                        <div class="user-avatar">
                            <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="display: block; fill: #717171; height: 30px; width: 30px;">
                                <path d="m16 .7c-8.437 0-15.3 6.863-15.3 15.3s6.863 15.3 15.3 15.3 15.3-6.863 15.3-15.3-6.863-15.3-15.3-15.3zm0 28c-4.021 0-7.605-1.884-9.933-4.81a12.425 12.425 0 0 1 2.245-2.903l.445-.4c1.886-1.637 4.191-2.487 7.243-2.487s5.357.85 7.243 2.487l.445.4a12.425 12.425 0 0 1 2.245 2.903c-2.328 2.926-5.912 4.81-9.933 4.81zm9.328-7.387c-.012-.02-.023-.04-.035-.06a10.428 10.428 0 0 0-6.191-3.653c1.789-1.344 2.898-3.411 2.898-5.7 0-3.97-3.23-7.2-7.2-7.2s-7.2 3.23-7.2 7.2c0 2.289 1.109 4.356 2.898 5.7a10.428 10.428 0 0 0-6.191 3.653c-.012.02-.023.04-.035.06a13.31 13.31 0 0 1-2.573-7.913c0-7.345 5.955-13.3 13.3-13.3s13.3 5.955 13.3 13.3c0 2.924-1.01 5.614-2.711 7.913z"></path>
                            </svg>
                        </div>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><a class="dropdown-item" href="perfil.php">Mi perfil</a></li>    
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="centro_de_ayuda.php">Centro de ayuda</a></li>
                        <li><a class="dropdown-item" href="auth/logout.php">Cerrar sesión</a></li>
                    </ul>

                </div>
            </div>
        </div>
    </header>


    <section class="hero-lugar">
        <div class="container">
            <div class="row g-3"> <div class="col-lg-8">
                    <img src="<?php echo !empty($hotel['url_imagen']) ? $hotel['url_imagen'] : 'https://images.unsplash.com/photo-1590490360182-c33d57733427'; ?>" class="img-fluid gallery-main" alt="Principal">
                </div>
                <div class="col-lg-4 d-flex flex-column justify-content-between">
                    <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427" class="img-fluid gallery-sub" alt="Sub 1">
                    <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427" class="img-fluid gallery-sub" alt="Sub 2">
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
    $query_hab = "SELECT h.*, i.url_imagen FROM cat_catalogo_habitacion h LEFT JOIN cat_imagen i on h.id_habitacion = i.id_habitacion WHERE h.id_catalogo = $id_hotel AND h.id_status = 1";
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
                    <a href="reservation.php?id_hab=<?php echo $hab['id_habitacion']; ?>" class="btn btn-info text-white">Reservar</a>
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