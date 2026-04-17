<?php
session_start();
include("config/config.php"); 

$query = "SELECT c.nombre, i.url_imagen 
          FROM catalogo c
         LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
          ORDER BY RAND()
          LIMIT 8";

$resultado_hoteles = mysqli_query($config, $query);

$catalogo_hoteles = array();

while($fila = mysqli_fetch_assoc($resultado_hoteles)) {
    $catalogo_hoteles[$fila['nombre']] = array(
        "imagen" => $fila['url_imagen'] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
     <link rel="stylesheet" href="styles/styles.css">
    <title>Booking | Home</title>
</head>
<body>
<header class="main-header">
<div class="glass-nav">
    <a href="index.html" class="logo">
        <img src="imagenes/brooking.png" alt="Logo">
    </a>
    <div class="nav-links">
        <a href="#">Destinos</a>
        <a href="catalogo.php">Catálogo</a>
        <!-- BOTÓN LOGIN AJUSTADO POR LOS CAMBIOS QUE MANDO EL PROFE-->
        <a href="Login.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-2">
            <i class="bi bi-person-circle"></i> Login
        </a>

    </div>
</div>
</header>

<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Encuentra tu próximo <span class="text-gradient">destino ideal</span></h1>
        <p>Reserva hoteles, casas y experiencias únicas en todo el mundo.</p>
        
        <div class="smart-search">
            <div class="search-field">
                <span class="label">UBICACIÓN</span>
                <input type="text" placeholder="¿A dónde quieres ir?">
            </div>

            <div class="divider"></div> 
            
            <div class="search-field">
                <span class="label">ENTRADA</span>
                <input type="date">
            </div>

            <div class="divider"></div> 
            
            <div class="search-field">
                <span class="label">SALIDA</span>
                <input type="date">
            </div>

            <div class="divider"></div> 
            
            <div class="search-field">
                <span class="label">PERSONAS</span>
                <input type="text" placeholder="¿Cuántos?">
            </div>

            <button class="search-btn" onclick="location.href='busqueda-resultado.html'">
                <svg width="20" height="20" fill="white" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                </svg>
            </button>
        </div>
    </div>
</section>

<section class="ofertas-section py-5">
  <div class="container">

    <div class="row text-center mb-5">
      <div class="col-md-4">
        <div class="beneficio-card">
          <i class="bi bi-tag"></i>
          <h5>Ofertas exclusivas</h5>
          <p>Encuentra las mejores promociones y precios exclusivos.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="beneficio-card">
          <i class="bi bi-shield-check"></i>
          <h5>Reserva segura</h5>
          <p>Tus datos están protegidos y tu reservación es 100% confiable.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="beneficio-card">
          <i class="bi bi-headset"></i>
          <h5>Atención 24/7</h5>
          <p>Disponible para ayudarte en cualquier momento.</p>
        </div>
      </div>
    </div>

    <div class="row g-4">
    <?php foreach ($catalogo_hoteles as $nombre => $datos): ?>
      <div class="col-md-3">
        <div class="hotel-card">
          <a href="lugares-info.html">
            <img src="<?php echo $datos['imagen']; ?>" class="img-fluid">
          </a>
          <div class="hotel-info">
            <h6><?php echo $nombre; ?></h6>
            <p><?php /* echo number_format($datos['precio'], 2); */ ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  </div>
</section>
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