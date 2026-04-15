<?php
session_start();
include("config/config.php"); 

$ubicacion = isset($_GET['ubicacion']) ? $_GET['ubicacion'] : '';
$personas  = isset($_GET['personas'])  ? $_GET['personas']  : '';

$sql = "SELECT c.*, ciu.nombre_ciudad, pais.nombre_pais, est.nombre_estado
        FROM catalogo c
        INNER JOIN cat_ubicacion u ON c.id_ubicacion = u.id_ubicacion
        INNER JOIN cat_ciudad ciu ON u.id_ciudad = ciu.id_ciudad
        INNER JOIN cat_estado est ON ciu.id_estado = est.id_estado
        INNER JOIN cat_pais pais ON est.id_pais = pais.id_pais
        INNER JOIN cat_imagen img On c.id_catalogo = img.id_catalogo
        WHERE 1=1";

if (!empty($ubicacion)) {
    $ubi_safe = mysqli_real_escape_string($config, $ubicacion);
    $sql .= " AND (ciu.nombre_ciudad LIKE '%$ubi_safe%' 
                OR est.nombre_estado LIKE '%$ubi_safe%' 
                OR pais.nombre_pais LIKE '%$ubi_safe%' 
                OR c.nombre LIKE '%$ubi_safe%')";
}

if (!empty($personas)) {
    $pers_safe = (int)$personas;
    $sql .= " AND c.disponibilidad >= $pers_safe";
}

$resultado = mysqli_query($config, $sql);

if(!$resultado){
    die("Error en la consulta: " . mysqli_error($config));
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookingEngineer | Resultados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles/busqueda-resultados.css ">
</head>
<body>

<div class="container-fluid px-lg-5 py-5">
    <div class="mb-5">
        <h2 class="fw-bold text-dark">Resultados en <?php echo !empty($ubicacion) ? htmlspecialchars($ubicacion) : 'todos los destinos'; ?></h2>
        <p class="text-muted">Explora las mejores opciones disponibles para tu viaje</p>
            <a href="home.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3" style="justify-content: space-between">
                <i class="bi bi-arrow-return-left"></i> Regresar
            </a>
    </div>

   <div class="row g-4">
    <?php 
    if (mysqli_num_rows($resultado) > 0) {
        while ($row = mysqli_fetch_assoc($resultado)) { 
    ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="#" class="hotel-card-link">
                <article class="hotel-card shadow-sm">
                    <div class="image-box">
                        <span class="badge-tag">Recomendado</span>
                        <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&q=80&w=600" alt="Hotel">
                        <label class="fav-checkbox" onclick="event.stopPropagation();">
                            <input type="checkbox" hidden>
                            <i class="bi bi-heart-fill"></i>
                        </label>
                    </div>
                    <div class="info-box">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="category">Resort de Lujo</span>
                            <span class="rating"><i class="bi bi-star-fill text-warning"></i> 4.9</span>
                        </div>
                        <h3 class="hotel-title"><?php echo htmlspecialchars($row['nombre']); ?></h3>
                        <p class="location"><i class="bi bi-geo-alt"></i><?php echo htmlspecialchars($row['nombre_ciudad']); ?></p>
                        <div class="footer-card">
                            <div class="price-data">
                                <span class="old-p"><?php echo number_format($row['precio'] * 1.2, 0); ?></span>
                                <span class="new-p"><?php echo number_format($row['precio'], 0); ?> <small>MXN</small></span>
                            </div>
                            <span class="btn-fake">Detalles</span>
                        </div>
                    </div>
                </article>
            </a>
        </div>
    <?php 
        }
    } 
    else {
    $query_sugerencias = "SELECT c.nombre, c.precio, ciu.nombre_ciudad, c.id_catalogo 
                      FROM catalogo c
                      INNER JOIN cat_ubicacion u ON c.id_ubicacion = u.id_ubicacion
                      INNER JOIN cat_ciudad ciu ON u.id_ciudad = ciu.id_ciudad
                      ORDER BY RAND() 
                      LIMIT 12";
    $res_sugerencias = mysqli_query($config, $query_sugerencias);    
    ?> 
        <div class='col-12 mt-2 text-center'>
            <a href= 'home.php' style='color: black'><i class='bi bi-search' style='font-size: 2rem;'></i></a>
            <h2 class='mt-2'>No encontramos lo que buscas</h2>
            <p class='text-muted'>Intenta con palabras clave diferentes.</p>
        </div>
            
    <div class='container mt-5'>
        <h3 class='section-title text-center'>Sugerencias para tu próximo viaje</h3>

    </div>
           
    <?php 
   
    while ($sugerencias = mysqli_fetch_assoc($res_sugerencias)) { 
    ?>
                <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="#" class="hotel-card-link">
                <article class="hotel-card shadow-sm">
                    <div class="image-box">
                        <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&q=80&w=600" alt="Hotel">
                        <label class="fav-checkbox" onclick="event.stopPropagation();">
                            <input type="checkbox" hidden>
                            <i class="bi bi-heart-fill"></i>
                        </label>
                    </div>
                    <div class="info-box">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="category">Resort de Lujo</span>
                            <span class="rating"><i class="bi bi-star-fill text-warning"></i> 4.9</span>
                        </div>
                        <h3 class="hotel-title"><?php echo htmlspecialchars($sugerencias['nombre']); ?></h3>
                        <p class="location"><i class="bi bi-geo-alt"></i><?php echo htmlspecialchars($sugerencias['nombre_ciudad']); ?></p>
                        <div class="footer-card">
                            <div class="price-data">
                                <span class="old-p"><?php echo number_format($sugerencias['precio'] * 1.2, 0); ?></span>
                                <span class="new-p"><?php echo number_format($sugerencias['precio'], 0); ?> <small>MXN</small></span>
                            </div>
                            <span class="btn-fake">Detalles</span>
                        </div>
                    </div>
                </article>
            </a>
        </div>
    <?php 
    } 
    ?>
</div>
";      
   
<?php 
    } 
?>
</div>
</div>

</body>
</html>