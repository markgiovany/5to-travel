<?php
session_start();
include("config/config.php");

// 1. Recogemos los favoritos de la sesión (PHP)
$favs_array = isset($_SESSION['favoritos']) ? $_SESSION['favoritos'] : [];

// 2. Si el usuario viene de JS con más IDs (vía URL), los combinamos
if (isset($_GET['local_ids'])) {
    $local_ids = explode(',', $_GET['local_ids']);
    $favs_array = array_unique(array_merge($favs_array, $local_ids));
}

$ids_favs = !empty($favs_array) ? implode(',', array_map('intval', $favs_array)) : '0';

$query = "SELECT 
            c.id_catalogo, c.nombre, c.descripcion, 
            u.direccion AS ubicacion_real,
            (SELECT MIN(h.precio) FROM cat_catalogo_habitacion h WHERE h.id_catalogo = c.id_catalogo) AS precio_min,
            (SELECT i.url_imagen FROM cat_imagen i WHERE i.id_catalogo = c.id_catalogo LIMIT 1) AS url_imagen
          FROM catalogo c
          LEFT JOIN cat_ubicacion u ON c.id_ubicacion = u.id_ubicacion
          WHERE c.id_catalogo IN ($ids_favs)";

$resultado = mysqli_query($config, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos | BookingEngineer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles/styles.css"> 
    <link rel="stylesheet" href="styles/catalogo.css">
    <link rel="stylesheet" href="styles/footer-pages.css">
    <style>
        body { background-color: #fcfcfc; }
        .fav-checkbox input:checked + i { color: #ff385c !important; }
        .hotel-card { border-radius: 16px; overflow: hidden; transition: 0.3s; background: #fff; border: 1px solid #eee; height: 100%; }
        .image-box { height: 180px; width: 100%; position: relative; }
        .image-box img { width: 100%; height: 100%; object-fit: cover; }
        .hotel-card-link { text-decoration: none; color: inherit; }
    </style>
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
            <a href="catalogo.php" class="text-decoration-none text-dark fw-medium small">Volver al Catálogo</a>
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

<div style="height: 120px;"></div>

<div class="container-fluid px-lg-5 py-5">
    <div class="mb-5 text-center"> 
        <h2 class="fw-bold text-dark">Mis Favoritos</h2>
        <p class="text-muted">Los lugares que te han robado el corazón</p>
    </div>

    <div class="row g-4">
        <?php if(mysqli_num_rows($resultado) > 0): ?>
            <?php while($hotel = mysqli_fetch_assoc($resultado)): 
                $es_fav = 'checked'; // Si están aquí, es porque son favoritos
            ?>
            <div class="col-12 col-md-6 col-lg-4 col-xl-3" id="card-hotel-<?= $hotel['id_catalogo']; ?>">
                <article class="hotel-card shadow-sm">
                    <div class="image-box">
                        <img src="<?= $hotel['url_imagen'] ?? 'img/placeholder.jpg'; ?>" alt="<?= $hotel['nombre']; ?>">
                        <label class="fav-checkbox position-absolute top-0 end-0 m-3" style="z-index: 10;">
                            <input type="checkbox" hidden <?= $es_fav; ?> onchange="toggleFavorito(<?= $hotel['id_catalogo']; ?>)">
                            <i class="bi bi-heart-fill fs-5 text-white" style="cursor: pointer; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));"></i>
                        </label>
                    </div>
                    <a href="lugares-info.php?id=<?= $hotel['id_catalogo']; ?>" class="hotel-card-link">
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-dark border-0 small"><?= $hotel['categoria'] ?? 'Hotel'; ?></span>
                                <span class="small fw-bold text-warning"><i class="bi bi-star-fill"></i> <?= number_format(4.5, 1); ?></span>
                            </div>
                            <h6 class="fw-bold text-dark text-truncate"><?= $hotel['nombre']; ?></h6>
                            <p class="text-muted mb-3 text-truncate" style="font-size: 11px;">
                                <i class="bi bi-geo-alt text-danger"></i> <?= $hotel['ubicacion_real'] ?? 'Ubicación disponible'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-auto">
                                <div class="price-data">
                                    <?php if($hotel['precio_min']): ?>
                                        <span class="fw-bold fs-5 text-dark">$<?= number_format($hotel['precio_min'], 0); ?></span>
                                        <small class="text-muted">MXN</small>
                                    <?php else: ?>
                                        <span class="text-secondary small fw-bold">Ver disponibilidad</span>
                                    <?php endif; ?>
                                </div>
                                <i class="bi bi-arrow-right-circle-fill fs-4 text-dark opacity-75"></i>
                            </div>
                        </div>
                    </a>
                </article>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-heartbreak fs-1 text-muted"></i>
                <h5 class="mt-3">Aún no tienes favoritos</h5>
                <a href="catalogo.php" class="btn btn-dark rounded-pill px-4 mt-2">Explorar hoteles</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleFavorito(idHotel) {
    const formData = new FormData();
    formData.append('id', idHotel);

    fetch('guardar_favorito.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Opcional: Remover la card de la vista al quitar el favorito
        const card = document.getElementById('card-hotel-' + idHotel);
        if (card) {
            card.style.opacity = '0';
            setTimeout(() => card.remove(), 300);
        }
    });
}
document.addEventListener("DOMContentLoaded", () => {
    // 1. Obtener lo que tiene el navegador
    let favoritosLocales = JSON.parse(localStorage.getItem('mis_favoritos')) || [];
    
    // 2. Obtener lo que PHP ya pintó en pantalla (IDs de las cards actuales)
    let idsEnPantalla = Array.from(document.querySelectorAll('[id^="card-hotel-"]'))
                             .map(el => parseInt(el.id.replace('card-hotel-', '')));

    // 3. Verificar si falta alguno
    let faltaAlguno = favoritosLocales.some(id => !idsEnPantalla.includes(id));

    // 4. Si falta info, recargamos la página pasándole los IDs faltantes a PHP
    if (faltaAlguno && favoritosLocales.length > 0) {
        const urlParams = new URLSearchParams(window.location.search);
        if (!urlParams.has('local_ids')) {
            window.location.href = `favoritos.php?local_ids=${favoritosLocales.join(',')}`;
        }
    }
});
</script>

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