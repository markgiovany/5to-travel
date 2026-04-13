<?php
session_start(); 
include("config/config.php"); 

if (!isset($_SESSION['user_uuid'])) {
    header("Location: index.php");
    exit();
}

$query = "SELECT c.id_catalogo, c.nombre, c.descripcion, c.precio, 
                 t.nombre_tipo as categoria, u.direccion, i.url_imagen 
          FROM catalogo c
          LEFT JOIN cat_tipo t ON c.id_tipo = t.id_tipo
          LEFT JOIN cat_ubicacion u ON c.id_ubicacion = u.id_ubicacion
          LEFT JOIN cat_imagen i ON c.id_catalogo = i.id_catalogo
          LIMIT 16";

$resultado = mysqli_query($config, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookingEngineer | Catálogo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles/styles.css"> 
    <link rel="stylesheet" href="styles/catalogo.css">
    <link rel="stylesheet" href="styles/filtros.css">
</head>
<body>

<header class="main-header">
    <div class="glass-nav">
        <a href="index.php" class="logo">
            <img src="imagenes/brooking.png" alt="Logo">
        </a>
        <div class="nav-links">
            <a href="#">Destinos</a>
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
                    <li><a class="dropdown-item" href="auth/logout.php">Cerrar sesión</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Centro de ayuda</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<div style="height: 100px;"></div>

<section class="be-filter-container">
    <div class="container-fluid px-lg-5">
        <div class="d-flex align-items-center flex-wrap gap-2">
            
            <div class="dropdown">
                <button class="be-filter-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    Precio
                </button>
                <div class="dropdown-menu be-filter-menu shadow">
                    <div class="be-filter-title">Rango de precio</div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="small text-muted">Mínimo</label>
                            <input type="number" class="form-control" placeholder="$0">
                        </div>
                        <div class="col-6">
                            <label class="small text-muted">Máximo</label>
                            <input type="number" class="form-control" placeholder="$5000+">
                        </div>
                    </div>
                    <button class="be-btn-apply">Aplicar</button>
                </div>
            </div>

            <div class="dropdown">
                <button class="be-filter-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    Estrellas
                </button>
                <div class="dropdown-menu be-filter-menu shadow">
                    <div class="be-filter-title">Categoría</div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="be-star5">
                        <label class="form-check-label" for="be-star5">5 Estrellas <i class="bi bi-star-fill text-warning"></i></label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="be-star4">
                        <label class="form-check-label" for="be-star4">4 Estrellas <i class="bi bi-star-fill text-warning"></i></label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="be-star3">
                        <label class="form-check-label" for="be-star3">3 Estrellas <i class="bi bi-star-fill text-warning"></i></label>
                    </div>
                    <button class="be-btn-apply">Aplicar</button>
                </div>
            </div>

            <a href="catalogo.php" class="be-link-clear">Limpiar</a>
        </div>
    </div>
</section>

<div class="container-fluid px-lg-5 py-5">
    <div class="mb-5 text-center"> 
        <h2 class="fw-bold text-dark">Catálogo de Hoteles</h2>
        <p class="text-muted">Explora las mejores opciones disponibles para tu viaje</p>
    </div>

    <div class="row g-4">
        <?php while($hotel = mysqli_fetch_assoc($resultado)): 
            $es_fav = (isset($_SESSION['favoritos']) && in_array($hotel['id_catalogo'], $_SESSION['favoritos'])) ? 'checked' : '';
        ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <a href="lugares-info.html?id=<?php echo $hotel['id_catalogo']; ?>" class="hotel-card-link">
                <article class="hotel-card shadow-sm">
                    <div class="image-box">
                        <img src="<?php echo $hotel['url_imagen'] ?? 'img/placeholder.jpg'; ?>" alt="<?php echo $hotel['nombre']; ?>">
                        <label class="fav-checkbox" onclick="event.stopPropagation();">
                            <input type="checkbox" hidden <?php echo $es_fav; ?> onchange="toggleFavorito(<?php echo $hotel['id_catalogo']; ?>)">
                            <i class="bi bi-heart-fill"></i>
                        </label>
                    </div>
                    <div class="info-box">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="category"><?php echo $hotel['categoria'] ?? 'Hotel'; ?></span>
                            <span class="rating-simulated">
                                <i class="bi bi-star-fill text-warning"></i> 
                                <?php echo number_format(4 + (mt_rand() / mt_getrandmax()), 1); ?>
                            </span>
                        </div>
                        <h3 class="hotel-title"><?php echo $hotel['nombre']; ?></h3>
                        <p class="location text-truncate"><i class="bi bi-geo-alt"></i> <?php echo $hotel['direccion'] ?? 'Ubicación no disponible'; ?></p>
                        <div class="footer-card">
                            <div class="price-data">
                                <?php if($hotel['precio']): ?>
                                    <span class="new-p">$<?php echo number_format($hotel['precio'], 0); ?> <small>MXN</small></span>
                                <?php else: ?>
                                    <span class="new-p">Ver precio</span>
                                <?php endif; ?>
                            </div>
                            <span class="btn-fake">Detalles</span>
                        </div>
                    </div>
                </article>
            </a>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
function toggleFavorito(idHotel) {
    const formData = new FormData();
    formData.append('id', idHotel);
    fetch('guardar_favorito.php', { method: 'POST', body: formData });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>