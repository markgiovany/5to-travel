<?php 
session_start();
include("config/config.php"); 
if (!isset($_SESSION['user_uuid'])) {
    header("Location: index.php");
    exit();
}

$query_faq = "SELECT pregunta, respuesta FROM hc_preguntas_frecuentes WHERE status = 'Activo'";
$resultado_faq = mysqli_query($config, $query_faq);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
         <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<header class="main-header">
    <div class="glass-nav">
        <a href="home.php" class="logo">
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
      <li><a class="dropdown-item" href="perfil.php">Perfil</a></li>
    <li><a class="dropdown-item" href="auth/logout.php">Cerrar sesión</a></li>
    
    <li><hr class="dropdown-divider"></li>
    
    <li><a class="dropdown-item" href="centro_de_ayuda.php">Centro de ayuda</a></li>
    </ul>
    </div>
        </div>
    </div>
</header>
    
<section class="preguntas_frecuentes mt-5 pt-5 mb-5">
    <div class="container">
        <h1 class="text-center mb-4">Centro de Ayuda</h1>
        <div class="accordion" id="accordionExample">
<?php 
            if ($resultado_faq && mysqli_num_rows($resultado_faq) > 0) {
                $contador = 1;
                while ($fila = mysqli_fetch_assoc($resultado_faq)) {
                    $id_collapse = "elemento" . $contador;
            ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $id_collapse; ?>" aria-expanded="false" aria-controls="<?php echo $id_collapse; ?>">
                                <?php echo $fila['pregunta']; ?>
                            </button>
                        </h2>
                        <div id="<?php echo $id_collapse; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <?php echo $fila['respuesta']; ?>
                            </div>
                        </div>
                    </div>
            <?php
                    $contador++;
                }
            } else {
                echo "<p class='text-center'>No hay preguntas frecuentes disponibles en este momento.</p>";
            }
            ?>
            
        </div>
    </div>
</section>


  <footer class="main-footer">
  <div class="footer-grid">
    <div class="footer-column">
      <h3>Soporte</h3>
      <ul>
        <li><a href="centro_de_ayuda.php">Centro de ayuda</a></li>
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