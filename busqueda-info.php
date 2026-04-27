<?php
session_start();
include("config/config.php"); 

$url_regresar = isset($_SESSION['user_uuid']) ? 'home.php' : 'index.php';

$ubicacion = isset($_GET['ubicacion']) ? $_GET['ubicacion'] : '';
$personas  = !empty($_GET['personas']) ? (int)$_GET['personas'] : 0;

$sql = "SELECT c.*, c.uuid AS hotel_uuid, ciu.name AS nombre_ciudad, est.name AS nombre_estado, pais.name AS nombre_pais, h.precio, i.url_imagen,
        (SELECT AVG(ca.estrellas) FROM calif_hoteles ca WHERE ca.id_hotel = c.id_catalogo) AS promedio_estrellas
        FROM catalogo c
        LEFT JOIN cat_ubicacion u ON c.id_ubicacion = u.id_ubicacion
        LEFT JOIN cities ciu ON u.city_id = ciu.id
        LEFT JOIN states est ON ciu.state_id = est.id
        LEFT JOIN countries pais ON est.country_id = pais.id
        LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
        INNER JOIN cat_catalogo_habitacion h ON c.id_catalogo = h.id_catalogo
        WHERE h.disponibilidad >= 0";

if ($personas > 0) {
    $sql .= " AND h.capacidad >= $personas";
}

if (!empty($ubicacion)) {
    $ubi_safe = mysqli_real_escape_string($config, $ubicacion);
    $sql .= " AND (ciu.name LIKE '%$ubi_safe%' 
                OR est.name LIKE '%$ubi_safe%' 
                OR pais.name LIKE '%$ubi_safe%' 
                OR c.nombre LIKE '%$ubi_safe%')";
}

$entrada = $_GET['entrada'] ?? '';
$salida = $_GET['salida'] ?? '';

if (!empty($entrada) && !empty($salida)) {
    if ($salida <= $entrada) {
        header("Location: " . $url_regresar . "?error_fecha=1&entrada=$entrada&salida=$salida");
        exit(); 
    }
}

$sql .= " GROUP BY c.id_catalogo";

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
    <link rel="stylesheet" href="styles/busqueda-resultados.css">
</head>
<body>

<div class="container-fluid px-lg-5 py-5">
    <div class="mb-5">
        <h2 class="fw-bold text-dark">Resultados en <?php echo !empty($ubicacion) ? htmlspecialchars($ubicacion) : 'todos los destinos'; ?></h2>
        <p class="text-muted">Explora las mejores opciones disponibles para tu viaje</p>
        <a href="<?php echo $url_regresar; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-return-left"></i> Regresar
        </a>
    </div>

    <div class="row g-4">
    <?php 
    if (mysqli_num_rows($resultado) > 0) {
        while ($row = mysqli_fetch_assoc($resultado)) { 
    ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="lugares-info.php?uuid=<?php echo $row['hotel_uuid']; ?>" style="text-decoration: none; color:black">
                <article class="hotel-card shadow-sm">
                    <div class="image-box">
                        <span class="badge-tag">Recomendado</span>
                        <?php 
                        $imagen_url = !empty($row['url_imagen']) ? $row['url_imagen'] : 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=600'; ?>
                        <img src="<?php echo $imagen_url; ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                    </div>
                    <div class="info-box">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="category">Hospedaje</span>
                            <span class="rating">
    <i class="bi bi-star-fill text-warning"></i> 
    <?php echo $row['promedio_estrellas'] ? number_format($row['promedio_estrellas'], 1) : '0.0'; ?>
</span>
                        </div>
                        <h3 class="hotel-title"><?php echo htmlspecialchars($row['nombre']); ?></h3>
                        <p class="location"><i class="bi bi-geo-alt"></i> <?php echo !empty($row['nombre_ciudad']) ? htmlspecialchars($row['nombre_ciudad']) : 'Destino pendiente'; ?></p>
                        <div class="footer-card">
                            <div class="price-data">
                                <span class="old-p"><?php echo number_format(($row['precio'] ?? 0) * 1.2, 0); ?></span>
                                <span class="new-p"><?php echo number_format($row['precio'] ?? 0, 0); ?> <small>MXN</small></span>
                            </div>
                        </div>
                    </div>
                </article>
            </a>
        </div>
    <?php 
        }
    } else {
        $query_sugerencias = "SELECT c.nombre, h.precio, ciu.name AS nombre_ciudad, c.id_catalogo, c.uuid AS hotel_uuid, i.url_imagen 
                              FROM catalogo c
                              LEFT JOIN cat_ubicacion u ON c.id_ubicacion = u.id_ubicacion
                              LEFT JOIN cities ciu ON u.city_id = ciu.id
                              LEFT JOIN cat_catalogo_habitacion h ON c.id_catalogo = h.id_catalogo
                              LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
                              GROUP BY c.id_catalogo
                              ORDER BY RAND() LIMIT 12";
        $res_sugerencias = mysqli_query($config, $query_sugerencias);
    ?>
        <div class='col-12 text-center py-5'>
            <i class='bi bi-search' style='font-size: 3rem; color: #ccc;'></i>
            <h2 class='mt-3'>No encontramos lo que buscas</h2>
            <p class='text-muted'>Intenta con otros filtros o mira nuestras sugerencias:</p>
        </div>

        <div class="row g-4 mt-2">
            <h3 class="text-center mb-4">Sugerencias para ti</h3>
            <?php while ($sugerencias = mysqli_fetch_assoc($res_sugerencias)) { ?>

            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                <a href="lugares-info.php?uuid=<?php echo $sugerencias['hotel_uuid']; ?>" style="text-decoration: none; color:black">
                    <article class="hotel-card shadow-sm">
                        <div class="image-box">
                            <img src="<?php echo !empty($sugerencias['url_imagen']) ? $sugerencias['url_imagen'] : 'https://images.unsplash.com/photo-1590490360182-c33d57733427'; ?>" alt="Hotel">
                        </div>
                        <div class="info-box">
                            <h3 class="hotel-title"><?php echo htmlspecialchars($sugerencias['nombre']); ?></h3>
                            <p class="location"><i class="bi bi-geo-alt"></i> <?php echo !empty($sugerencias['nombre_ciudad']) ? htmlspecialchars($sugerencias['nombre_ciudad']) : 'Ubicación pendiente'; ?></p>
                            <div class="footer-card">
                                <div class="price-data">
                                    <span class="new-p"><?php echo number_format($sugerencias['precio'] ?? 0, 0); ?> <small>MXN</small></span>
                                </div>
                            </div>
                        </div>
                    </article>
                </a>
            </div>
              
            <?php } ?>
        </div>
    <?php } ?>
    </div>
</div>

</body>
</html>
