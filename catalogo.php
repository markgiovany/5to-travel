<?php
session_start(); 
include("config/config.php"); 

/**
 * 1. PROCESAMIENTO DE FILTROS
 */
$min = isset($_GET['min']) && $_GET['min'] !== '' ? floatval($_GET['min']) : 0;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? floatval($_GET['max']) : 999999;
$stars = isset($_GET['estrellas']) ? intval($_GET['estrellas']) : 0;

$pais_id = isset($_GET['pais']) ? intval($_GET['pais']) : 0;
$estado_id = isset($_GET['estado']) ? intval($_GET['estado']) : 0;
$ciudad_id = isset($_GET['ciudad']) ? intval($_GET['ciudad']) : 0;

$filtros_activos = ($min > 0 || $max < 999999 || $stars > 0 || $pais_id > 0 || $estado_id > 0 || $ciudad_id > 0) ? 1 : 0;

/**
 * 2. CARGAR SELECTORES (Dinámicos según selección)
 */
$countries_res = mysqli_query($config, "SELECT id, name FROM countries ORDER BY name ASC");

// Solo cargar estados si ya se seleccionó un país
$states_res = ($pais_id > 0) 
    ? mysqli_query($config, "SELECT id, name FROM states WHERE country_id = $pais_id ORDER BY name ASC") 
    : null;

// Solo cargar ciudades si ya se seleccionó un estado
$cities_res = ($estado_id > 0) 
    ? mysqli_query($config, "SELECT id, name FROM cities WHERE state_id = $estado_id ORDER BY name ASC") 
    : null;
 
$query = "SELECT 
            c.id_catalogo, c.nombre, 
            u.direccion AS ubicacion_real,
            (SELECT MIN(h.precio) FROM cat_catalogo_habitacion h WHERE h.id_catalogo = c.id_catalogo) AS precio_min,
            (SELECT i.url_imagen FROM cat_imagen i WHERE i.id_catalogo = c.id_catalogo LIMIT 1) AS url_imagen,
            (SELECT AVG(ca.estrellas) FROM calif_hoteles ca WHERE ca.id_hotel = c.id_catalogo) AS promedio_estrellas
          FROM catalogo c
          LEFT JOIN cat_ubicacion u ON c.id_ubicacion = u.id_ubicacion
          WHERE c.id_status = (SELECT id_status FROM status WHERE nombre IN ('Active', 'Activo') LIMIT 1)";

if ($pais_id > 0) $query .= " AND u.country_id = $pais_id";
if ($estado_id > 0) $query .= " AND u.state_id = $estado_id";
if ($ciudad_id > 0) $query .= " AND u.city_id = $ciudad_id";

if ($min > 0) $query .= " AND EXISTS (SELECT 1 FROM cat_catalogo_habitacion h WHERE h.id_catalogo = c.id_catalogo AND h.precio >= $min)";
if ($max < 999999) $query .= " AND EXISTS (SELECT 1 FROM cat_catalogo_habitacion h WHERE h.id_catalogo = c.id_catalogo AND h.precio <= $max)";

$query .= " HAVING 1=1";
if ($stars > 0) $query .= " AND (promedio_estrellas >= $stars OR promedio_estrellas IS NULL)";

$query .= " LIMIT 20";
$resultado = mysqli_query($config, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>BookingEngineer | Catálogo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        body { background-color: #fcfcfc; }
        .hotel-card { border-radius: 16px; overflow: hidden; transition: 0.3s; background: #fff; border: 1px solid #eee; height: 100%; }
        .hotel-card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0,0,0,0.06); }
        .image-box { height: 180px; width: 100%; }
        .image-box img { width: 100%; height: 100%; object-fit: cover; }
        .fav-checkbox input:checked + i { color: #FF385C !important; }
        .fav-icon { color: white; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5)); cursor: pointer; }
        
        /* Botón de Filtros Estilo Pro */
        .btn-filter-toggle {
            background: white; border: 1px solid #ddd; border-radius: 12px;
            padding: 10px 20px; font-weight: 600; font-size: 14px; transition: 0.2s;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-filter-toggle:hover { background: #f8f9fa; border-color: #222; }

        /* Offcanvas Personalizado (A la izquierda) */
        .offcanvas-start { width: 350px !important; border-right: none; border-radius: 0 20px 20px 0; }
    </style>
</head>
<body>

<header class="main-header">
    <div class="glass-nav">
        <?php 
    $enlace_logo = isset($_SESSION['user_uuid']) ? 'home.php' : 'index.php'; 
?>
<a href="<?= $enlace_logo; ?>" class="logo">
    <img src="imagenes/brooking.png" alt="Logo" width="140">
</a>
        <div class="nav-links d-flex align-items-center gap-3">
            <a href="favoritos.php" class="text-decoration-none text-dark fw-medium small">
                <i class="bi bi-heart me-1"></i> Favoritos
            </a>
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
</header>

<div style="height: 110px;"></div>

<div class="container-fluid px-lg-5 py-4">
    <!-- Título y Botón Alineados -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-dark mb-1">Catálogo de Hoteles</h2>
            <p class="text-muted small mb-0">Encuentra tu próximo destino con nosotros.</p>
        </div>
        <!-- BOTÓN QUE ABRE EL PANEL DESDE LA IZQUIERDA -->
        <button class="btn-filter-toggle shadow-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#filtrosPanel">
            <i class="bi bi-sliders"></i> Filtros 
            <?php if($filtros_activos): ?><span class="badge bg-dark rounded-circle" style="font-size: 10px;">!</span><?php endif; ?>
        </button>
    </div>

    <!-- PANEL LATERAL (OFFCANVAS) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="filtrosPanel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold">Filtros Avanzados</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-4">
            <form action="catalogo.php" method="GET">
                <h6 class="fw-bold mb-3 small text-uppercase text-muted">Ubicación</h6>
                <div class="mb-4">
                    <select name="pais" id="pais" class="form-select mb-2 rounded-3" onchange="cargarDependientes('state', this.value)">
    <option value="0">País</option>
    <?php 
    mysqli_data_seek($countries_res, 0); 
    while($p = mysqli_fetch_assoc($countries_res)): ?>
        <option value="<?= $p['id'] ?>" <?= $pais_id == $p['id'] ? 'selected' : '' ?>><?= $p['name'] ?></option>
    <?php endwhile; ?>
</select>

<select name="estado" id="estado" class="form-select mb-2 rounded-3" onchange="cargarDependientes('city', this.value)">
    <option value="0">Estado</option>
    <?php if ($pais_id > 0): 
        $res = mysqli_query($config, "SELECT id, name FROM states WHERE country_id = $pais_id ORDER BY name ASC");
        while($e = mysqli_fetch_assoc($res)): ?>
            <option value="<?= $e['id'] ?>" <?= $estado_id == $e['id'] ? 'selected' : '' ?>><?= $e['name'] ?></option>
        <?php endwhile; 
    endif; ?>
</select>

<select name="ciudad" id="ciudad" class="form-select rounded-3">
    <option value="0">Ciudad</option>
    <?php if ($estado_id > 0): 
        $res = mysqli_query($config, "SELECT id, name FROM cities WHERE state_id = $estado_id ORDER BY name ASC");
        while($c = mysqli_fetch_assoc($res)): ?>
            <option value="<?= $c['id'] ?>" <?= $ciudad_id == $c['id'] ? 'selected' : '' ?>><?= $c['name'] ?></option>
        <?php endwhile; 
    endif; ?>
</select>
                </div>

                <hr class="my-4 opacity-25">

                <h6 class="fw-bold mb-3 small text-uppercase text-muted">Presupuesto Máximo</h6>
                <div class="input-group mb-5">
                    <span class="input-group-text bg-white border-end-0 rounded-start-3">$</span>
                    <input type="number" name="max" class="form-control border-start-0 rounded-end-3" placeholder="Ej. 5000" value="<?= $max < 999999 ? $max : '' ?>">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-dark py-3 rounded-4 fw-bold">Ver resultados</button>
                    <a href="catalogo.php" class="btn btn-link text-muted small">Borrar filtros</a>
                </div>
            </form>
        </div>
    </div>

    <!-- GRID DE HOTELES (SIEMPRE 4 COLUMNAS) -->
    <main>
        <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4">
            <?php while($hotel = mysqli_fetch_assoc($resultado)): 
                $rating = $hotel['promedio_estrellas'] ? number_format($hotel['promedio_estrellas'], 1) : "Nuevo";
            ?>
            <div class="col">
                <article class="hotel-card shadow-sm d-flex flex-column">
                    <div class="image-box position-relative">
                        <img src="<?= !empty($hotel['url_imagen']) ? $hotel['url_imagen'] : 'imagenes/placeholder.jpg'; ?>" alt="Hotel">
                        <label class="fav-checkbox position-absolute top-0 end-0 m-3">
                            <input type="checkbox" id="fav-<?= $hotel['id_catalogo']; ?>" hidden onchange="toggleFavorito(<?= $hotel['id_catalogo']; ?>)">
                            <i class="bi bi-heart-fill fs-5 fav-icon"></i>
                        </label>
                    </div>
                    <div class="p-3">
                        <h6 class="fw-bold mb-1 text-truncate small"><?= htmlspecialchars($hotel['nombre']); ?></h6>
                        <p class="text-muted mb-2 text-truncate" style="font-size: 11px;">
                            <i class="bi bi-geo-alt me-1 text-danger"></i><?= htmlspecialchars($hotel['ubicacion_real'] ?? 'Sin ubicación'); ?>
                        </p>
                        <div class="small text-muted mb-2">
    <i class="bi bi-star-fill text-warning"></i> 
    <?= $hotel['promedio_estrellas'] ? number_format($hotel['promedio_estrellas'], 1) : "0.0" ?>
</div>
                        <div class="d-flex justify-content-between align-items-center mt-3 border-top pt-3">
                            <span class="fw-bold fs-6 text-dark">$<?= number_format($hotel['precio_min'], 0); ?> <small class="text-muted" style="font-size: 10px;">MXN</small></span>
                            <a href="lugares-info.php?id=<?= $hotel['id_catalogo']; ?>" class="text-dark"><i class="bi bi-arrow-right-circle-fill fs-4 opacity-75"></i></a>
                        </div>
                    </div>
                </article>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
</div>

<footer class="py-4 border-top mt-5 bg-white text-center">
    <div class="container">
        <p class="text-muted mb-0 small">
            &copy; 2026 <strong>BookingEngineering</strong>. Todos los derechos reservados.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleFavorito(idHotel) {
    let favoritos = JSON.parse(localStorage.getItem('mis_favoritos')) || [];
    if (favoritos.includes(idHotel)) {
        favoritos = favoritos.filter(fav => fav !== idHotel);
    } else {
        favoritos.push(idHotel);
    }
    localStorage.setItem('mis_favoritos', JSON.stringify(favoritos));
}

document.addEventListener("DOMContentLoaded", () => {
    let favoritosLocales = JSON.parse(localStorage.getItem('mis_favoritos')) || [];
    favoritosLocales.forEach(id => {
        let input = document.getElementById(`fav-${id}`);
        if(input) input.checked = true;
    });
});
</script>
</body>
<script>
function cargarDependientes(type, id) {
    const targetId = (type === 'state') ? 'estado' : 'ciudad';
    const targetSelect = document.getElementById(targetId);
    
    // esto hace que si cambio el pais se me resetea la ciudad y estado
    if(type === 'state') {
        document.getElementById('ciudad').innerHTML = '<option value="0">Ciudad</option>';
    }

    // Petición AJAX al archivo nuevo
    fetch(`get_locations.php?type=${type}&id=${id}`)
        .then(response => response.json())
        .then(data => {
            targetSelect.innerHTML = `<option value="0">${type === 'state' ? 'Estado' : 'Ciudad'}</option>`;
            data.forEach(item => {
                targetSelect.innerHTML += `<option value="${item.id}">${item.name}</option>`;
            });
        })
        .catch(error => console.error('Error:', error));
}
</script>
</html>