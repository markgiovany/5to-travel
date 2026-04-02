<?php
session_start();
include("config/conexion.php"); 
 
if (!isset($_SESSION['user_uuid'])) {
    header("Location: index.php");
    exit();
}

$query = "SELECT c.nombre, c.precio, i.url_imagen 
          FROM catalogo c
          LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo";

$resultado_hoteles = mysqli_query($conexion, $query);

$catalogo_hoteles = array();

while($fila = mysqli_fetch_assoc($resultado_hoteles)) {
    $catalogo_hoteles[$fila['nombre']] = array(
        "precio" => $fila['precio'],
        "imagen" => $fila['url_imagen'] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945'
    );
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
     <link rel="stylesheet" href="styles/styles.css">
    <title>Document</title>
</head>
<body>
    <header class="main-header">
    <div class="glass-nav">
        <a href="index.html" class="logo">
            <img src="imagenes/brooking.png" alt="Logo">
        </a>
        <div class="nav-links">
            <a href="#">Destinos</a>
            <a href="#">Ofertas</a>
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
    <li><a class="dropdown-item" href="auth/logout.php">Cerrar sesión</a></li>
    
    <li><hr class="dropdown-divider"></li>
    
    <li><a class="dropdown-item" href="#">Centro de ayuda</a></li>
    </ul>
    </div>
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
        <span class="label">Ubicación</span>
        <input type="text" placeholder="¿A dónde quieres ir?">
    </div>

    <div class="divider"></div> <div class="search-field">
        <span class="label">Entrada</span>
        <input type="date">
    </div>

    <div class="divider"></div> <div class="search-field">
        <span class="label">Salida</span>
        <input type="date">
    </div>

    <div class="divider"></div> <div class="search-field">
        <span class="label">Personas</span>
        <input type="text" placeholder="¿Cuántos?">
    </div>
<a href="busqueda-resultado.html">
            <button class="search-btn">
                <svg width="20" height="20" fill="white" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
            </button>
            </a>
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
    <?php 
    foreach ($catalogo_hoteles as $nombre => $datos): 
    ?>
      <div class="col-md-3">
        <div class="hotel-card">
          <a href="lugares-info.html">
            <img src="<?php echo $datos['imagen']; ?>" class="img-fluid">
          </a>
          <div class="hotel-info">
            <h6><?php echo $nombre; ?></h6>
            <p>$<?php echo number_format($datos['precio'], 2); ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
</div>
</div>
</section>
<section class="destinos-section py-5">
  <div class="container text-center text-white">
    <h2 class="fw-bold">Destinos Populares</h2>
    <p class="mb-5 opacity-75">Viaja por el Mundo, con confianza</p>

    <div id="carouselDestinos" class="carousel slide" data-bs-ride="false">
      <div class="carousel-inner">
        
        <div class="carousel-item active">
          <div class="d-flex justify-content-center gap-3">
            <div class="destino-item">
              <div class="img-circle">
              <a href="card-info.html"><img src="https://images.unsplash.com/photo-1511739001486-6bfe10ce785f?w=400" alt="Torre Eiffel"></a>
              </div>
              <h6>Torre Eiffel</h6> 
              <span>356 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1772473492334-e64515e7-00e3-4b48-a12f-acdf16318a82.webp" alt="Machu Picchu"></a></div>
              <h6>Machu Picchu</h6>
              <span>210 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://images.unsplash.com/photo-1508804185872-d7badad00f7d?w=400" alt="La Gran Muralla"></a></div>
              <h6>La Gran Muralla</h6>
              <span>180 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1772473524243-45f5f268-06af-443c-9f71-a2c7b5ba5277.webp" alt="E. Libertad"></a></div>
              <h6>E. Libertad</h6>
              <span>420 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1772473343990-2373e245-301d-498f-a6ff-118417fdaf33.webp" alt="Taj Mahal"></a></div>
              <h6>Taj Mahal</h6>
              <span>170 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1772474078580-dc9e55e5-c13d-4cc2-a5a0-a0c28ef16b70.webp" alt="Rio de janeiro"></a></div>
              <h6>Rio de janeiro</h6>
              <span>120 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1772474103850-ec8dabb8-832a-4118-8b4d-44d2e458aa1e.webp" alt="Praga"></a></div>
              <h6>Praga</h6>
              <span>100 Tours</span>
            </div>
          </div>
        </div>

        <div class="carousel-item">
          <div class="d-flex justify-content-center gap-3">
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?w=400" alt="Praga"></a></div>
              <h6>Sidney</h6>
              <span>180 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=400" alt="Coliseo"></a></div>
              <h6>Coliseo</h6>
              <span>500 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://images.unsplash.com/photo-1474044159687-1ee9f3a51722?w=400" alt="Gran Cañón"></a></div>
              <h6>Gran Cañón</h6>
              <span>120 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=400" alt="Santorini"></a></div>
              <h6>Santorini</h6>
              <span>310 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=400" alt="Londres"></a></div>
              <h6>Londres</h6>
              <span>450 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1772474173366-94f580f8-a3fc-4028-b270-ca849fdee764.webp" alt="Estambul"></a></div>
              <h6>Estambul</h6>
              <span>150 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1772474216033-1e41c5b3-aa04-4f46-b315-a5063f1305ff.webp" alt="Lisboa"></a></div>
              <h6>Lisboa</h6>
              <span>423 Tours</span>
            </div>
          </div>
        </div>

        <div class="carousel-item">
          <div class="d-flex justify-content-center gap-3">
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1773024724252-ef2e7748-d437-4b65-8123-8cbd138d3641.webp" alt="Praga"></a></div>
              <h6>Praga</h6>
              <span>100 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://images.unsplash.com/photo-1503177119275-0aa32b3a9368?w=400" alt="Praga"></a></div>
              <h6>Giza</h6>
              <span>100 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1772473391793-c662922e-7025-4750-8686-1e31699ac68a.webp" alt="Venecia"></a></div>
              <h6>Venecia</h6>
              <span>260 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1772473423551-4c1abca1-fe1b-4526-96ae-daf23eede645.webp" alt="Petra"></a></div>
              <h6>Petra</h6>
              <span>115 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=400" alt="Bali"></a></div>
              <h6>Bali</h6>
              <span>200 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1772474263686-6c9265f6-4ed6-4c57-95da-55fbd5576bf7.webp" alt="Atenaz"></a></div>
              <h6>Atenaz</h6>
              <span>320 Tours</span>
            </div>
            <div class="destino-item">
              <div class="img-circle"><a href="card-info.html"><img src="https://image2url.com/r2/default/images/1772474346570-2f1be8f6-2481-4c86-b38b-b59a39c6ef54.webp" alt="Kioto"></a></div>
              <h6>Kioto</h6>
              <span>210 Tours</span>
            </div>
          </div>
        </div>

      </div>

      <button class="carousel-control-prev" type="button" data-bs-target="#carouselDestinos" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselDestinos" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
      </button>
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