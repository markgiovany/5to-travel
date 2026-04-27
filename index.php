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
        header("Location: index.php?error_fecha=1&entrada=$entrada&salida=$salida");
        exit();
    }
}

if (!empty($entrada)) {
    $fecha_min_salida = date('Y-m-d', strtotime($entrada . ' +1 day'));
} else {
    $fecha_min_salida = date('Y-m-d', strtotime('+1 day'));
}

if (isset($error_fecha)): ?>
    <div style="color: #ff4d4d; background: rgba(255, 77, 77, 0.1); padding: 10px; border-radius: 8px; font-size: 0.85rem; margin-top: 10px; text-align: center;">
        <i class="bi bi-exclamation-circle"></i> <?php echo $error_fecha; ?>
    </div>
<?php endif; 

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

        <a href="centro_de_ayuda.php">Centro de ayuda</a>

        <a href="Login.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 d-flex align-items-center gap-2">
            <i class="bi bi-person-circle"></i> Login
        </a>
    </div>
</div>
</header>

<section class="d-flex align-items-center justify-content-center text-center" 
         style="height:300px; padding-top:100px;">
    
    <div class="hero-content container">
        <h1 style="color:#000;">Encuentra tu próximo <span class="text-gradient">destino ideal</span></h1>
        
        
        <form action="busqueda-info.php" method="GET">
          <div class="smart-search d-flex align-items-center mx-auto">

              <div class="search-field">
                  <span class="label">UBICACIÓN</span>
                  <input type="text" name="ubicacion" list="destinos_list" placeholder="¿A dónde quieres ir?" autocomplete="on">

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
                  <input type="date" name="salida" value="<?php echo $salida; ?>" min="<?php echo $fecha_min_salida ?>" required>
              </div>

              <div class="divider"></div> 
              
              <div class="search-field">
                  <span class="label">PERSONAS</span>
                  <input type="number" name="personas" placeholder="¿Cuántos?" min="1" max="20">
              </div>

              <button class="search-btn">
                  <i class="bi bi-search"></i>
              </button>

          </div>
        </form>
    </div>

</section>

<section class="ofertas-section py-5">
<div class="container">



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

<footer class="py-4 border-top mt-5 bg-white text-center">
    <div class="container">
        <p class="text-muted mb-0 small">
            &copy; 2026 <strong>BookingEngineering</strong>. Todos los derechos reservados.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>