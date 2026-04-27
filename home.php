<?php
session_start();
include("config/config.php"); 

$query = "SELECT c.id_catalogo, c.uuid, c.nombre, i.url_imagen
          FROM catalogo c
          LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
          GROUP BY c.id_catalogo
          ORDER BY RAND()
          LIMIT 12";

$resultado_hoteles = mysqli_query($config, $query);

$catalogo_hoteles = array();

while($fila = mysqli_fetch_assoc($resultado_hoteles)) {
    $catalogo_hoteles[] = array(
        "id" => $fila['id_catalogo'],
        "uuid" => $fila['uuid'],
        "nombre" => $fila['nombre'],
        "imagen" => $fila['url_imagen'] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945'
    );
}

$entrada = isset($_GET['entrada']) ? $_GET['entrada'] : '';
$salida = isset($_GET['salida']) ? $_GET['salida'] : '';

$hoy = date('Y-m-d');
if (!empty($entrada) && $entrada < $hoy) {
    $entrada = $hoy; 
    $error_fecha = "La fecha de entrada no puede ser anterior a hoy.";
}

if (!empty($entrada) && !empty($salida)) {
    if ($salida <= $entrada) {
        $mañana = date('Y-m-d', strtotime($entrada . ' +1 day'));
        $salida = $mañana;
        $error_fecha = "La fecha de salida debe ser posterior a la entrada.";
    }
}

if (!empty($entrada)) {
    $fecha_min_salida = date('Y-m-d', strtotime($entrada . ' +1 day'));
} else {
    $fecha_min_salida = date('Y-m-d', strtotime('+1 day'));
}

$query_auto = "SELECT name FROM  cities 
        UNION SELECT name FROM states 
        UNION SELECT name FROM countries  
        ORDER BY name ASC";
$res_auto = mysqli_query($config, $query_auto);        

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="styles/styles.css">
<link rel="stylesheet" href="styles/footer-pages.css">

<title>Booking | Home</title>
</head>

<body>

<header class="main-header">
<div class="glass-nav">
    <a href="index.php" class="logo">
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

<section class="hero-section"style="
height:520px;
background: url('https://images.unsplash.com/photo-1551882547-ff40c63fe5fa') center/cover no-repeat;
position:relative;
display:flex;
align-items:center;
justify-content:center;
text-align:center;
padding-top:100px;
">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Encuentra tu próximo <span class="text-gradient">destino ideal</span></h1>
        <p>Reserva hoteles, casas y experiencias únicas en todo el mundo.</p>
        
        <form action="busqueda-info.php" method="GET">
          <div class="smart-search">
              <div class="search-field">
                  <span class="label">UBICACIÓN</span>
                  <input type="text" name="ubicacion" list="destinos_list" placeholder="¿A dónde quieres ir? " autocomplete="on">

                  <datalist id="destinos_list">
                    <?php while($row = mysqli_fetch_assoc($res_auto)): ?>
                    <option value="<?php echo htmlspecialchars($row['name']); ?>"></option>
                    <?php endwhile; ?>
                  </datalist>
              </div>

              <div class="divider"></div> 
              
              <div class="search-field">
                  <span class="label">ENTRADA</span>
                  <input type="date" name="entrada" value="<?php echo $entrada; ?>" min="<?php echo date('Y-m-d'); ?>">
              </div>

              <div class="divider"></div> 
              
              <div class="search-field">
                  <span class="label">SALIDA</span>
                  <input type="date" name="salida" value="<?php echo $salida; ?>" min="<?php echo $fecha_min_salida ?>"required >
              </div>

              <div class="divider"></div> 
              
              <div class="search-field">
                  <span class="label">PERSONAS</span>
                  <input type="number" name="personas" placeholder="¿Cuántos?" min="0" max="20">
              </div>

              <button class="search-btn">
                  <svg width="20" height="20" fill="white" viewBox="0 0 16 16">
                      <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                  </svg>
              </button>
          </div>
        </form>
    </div>
</section>

<section class="ofertas-section py-5">
<div class="container">

<div class="row text-center mb-5">
    <div class="col-md-4">
        
</div>

<!-- 🔥 HOTELES -->
<div class="row g-4">
<?php foreach ($catalogo_hoteles as $hotel): ?>
  <div class="col-md-3">
    <div class="hotel-card">
      
      <a href="lugares-info.php?uuid=<?php echo $hotel['uuid']; ?>">
        <img src="<?php echo $hotel['imagen']; ?>" class="img-fluid">
      </a>

      <div class="hotel-info">
        <h6><?php echo $hotel['nombre']; ?></h6>
      </div>

    </div>
  </div>
<?php endforeach; ?>




</div>






</div>
</section>

<footer class="footer">
    <div class="footer-container">

        <!-- Soporte -->
        <div class="footer-col">
            <h4>Soporte</h4>
            <ul>
                <li><a href="footer/centro-ayuda.php">Centro de ayuda</a></li>
                <li><a href="footer/seguridad.php">Información de seguridad</a></li>
                <li><a href="footer/cancelacion.php">Opciones de cancelación</a></li>
            </ul>
        </div>

        <!-- Compañía -->
        <div class="footer-col">
            <h4>Compañía</h4>
            <ul>
                <li><a href="footer/sobre-nosotros.php">Sobre nosotros</a></li>
                <li><a href="footer/privacidad.php">Política de privacidad</a></li>
                <li><a href="footer/blog.php">Blog de la comunidad</a></li>
                <li><a href="footer/terminos.php">Términos de servicio</a></li>
            </ul>
        </div>

        <!-- Contacto -->
        <div class="footer-col">
            <h4>Contacto</h4>
            <ul>
                <li><a href="footer/faq.php">Preguntas frecuentes</a></li>
                <li><a href="footer/contacto.php">Ponte en contacto</a></li>
                <li><a href="footer/patrocinadores.php">Patrocinadores</a></li>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <p>© <?php echo date("Y"); ?> BookingEngineering</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>