<?php 
session_start();
include("config/config.php"); 

$query_categorias = "SELECT id_categoria, nombre FROM hc_categorias";
$resultado_categorias = mysqli_query($config, $query_categorias);


$query_faq = "SELECT p.pregunta, p.respuesta, p.id_categoria
    FROM hc_preguntas_frecuentes p
    WHERE p.id_status = (SELECT id_status FROM status WHERE nombre = 'Activo' LIMIT 1)";
$resultado_faq = mysqli_query($config, $query_faq);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Ayuda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        .search-wrapper {
            position: relative;
            max-width: 600px;
            margin: 0 auto 2rem auto;
        }
        .search-wrapper .bi-search {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 1.1rem;
        }
        #buscador {
            padding-left: 2.8rem;
            border-radius: 50px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            height: 48px;
            font-size: 1rem;
        }
        #buscador:focus {
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
            border-color: #86b7fe;
            outline: none;
        }

        .nav-pills .nav-link {
            border-radius: 50px;
            padding: 0.4rem 1.2rem;
            color: #555;
            font-size: 0.9rem;
            cursor: pointer;
            margin: 0 5px;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }

        #sin-resultados {
            display: none;
            text-align: center;
            color: #888;
            padding: 2rem 0;
            font-size: 1.1rem;
        }
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
            <a href="catalogo.php" class="text-decoration-none text-dark fw-medium small">Catálogo</a>
            <a href="catalogo.php" class="text-decoration-none text-dark fw-medium small"><i class="bi bi-heart me-1"></i> Favoritos</a>
            <?php if (isset($_SESSION['user_uuid'])): ?>
                <div class="dropdown d-inline-block">
                    <div class="user-pill d-flex align-items-center gap-2 border rounded-pill px-2 py-1" data-bs-toggle="dropdown" role="button">
                        <i class="bi bi-list text-dark"></i>
                        <div class="user-avatar bg-light rounded-circle p-1"><i class="bi bi-person-fill text-secondary"></i></div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><a class="dropdown-item fw-bold" href="perfil.php">Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="centro_de_ayuda.php">Centro de ayuda</a></li>
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
    
<section class="preguntas_frecuentes mt-5 pt-5 mb-5">
    <div class="container">
        <h1 class="text-center mb-4">Centro de Ayuda</h1>
        <p class="text-center text-muted mb-4">¿En qué podemos ayudarte?</p>
        
        <div class="search-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" id="buscador" class="form-control" placeholder="Busca tu pregunta aquí...">
        </div>

        <ul class="nav nav-pills justify-content-center mb-4" id="filtro-categorias">
            <li class="nav-item">
                <button class="nav-link active" data-filter="all">Todas</button>
            </li>
            <?php
            if ($resultado_categorias && mysqli_num_rows($resultado_categorias) > 0) {
                while ($cat = mysqli_fetch_assoc($resultado_categorias)) {
                    echo '<li class="nav-item">';
                    echo '<button class="nav-link" data-filter="' . $cat['id_categoria'] . '">' . htmlspecialchars($cat['nombre']) . '</button>';
                    echo '</li>';
                }
            }
            ?>
        </ul>

        <div class="accordion" id="accordionExample">
            <?php 
            if ($resultado_faq && mysqli_num_rows($resultado_faq) > 0) {
                $contador = 1;
                while ($fila = mysqli_fetch_assoc($resultado_faq)) {
                    $id_collapse = "elemento" . $contador;
            ?>
                    <div class="accordion-item faq-item" data-categoria="<?php echo $fila['id_categoria']; ?>">
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

        <div class="text-center mt-4">
    <button id="btn-cargar-mas" class="btn btn-outline-primary px-4 py-2" style="border-radius: 50px;">
        Ver más preguntas
    </button>
</div>
        
        <div id="sin-resultados">
            No encontramos ninguna pregunta que coincida con tu búsqueda.
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador');
    const faqItems = document.querySelectorAll('.faq-item');
    const btnCategorias = document.querySelectorAll('#filtro-categorias .nav-link');
    const msjSinResultados = document.getElementById('sin-resultados');
    const btnCargarMas = document.getElementById('btn-cargar-mas');

    let limiteActual = 10; 
    const incremento = 10; 

    function filtrarContenido() {
        const textoBusqueda = buscador.value.toLowerCase();
        const categoriaActiva = document.querySelector('#filtro-categorias .nav-link.active').getAttribute('data-filter');
        let itemsVisibles = 0;
        const hayBusquedaActiva = (textoBusqueda !== '') || (categoriaActiva !== 'all');

        faqItems.forEach((item, index) => {
            const pregunta = item.querySelector('.accordion-button').textContent.toLowerCase();
            const respuesta = item.querySelector('.accordion-body').textContent.toLowerCase();
            const categoriaItem = item.getAttribute('data-categoria');

            const coincideTexto = pregunta.includes(textoBusqueda) || respuesta.includes(textoBusqueda);
            const coincideCategoria = (categoriaActiva === 'all') || (categoriaActiva === categoriaItem);

            if (hayBusquedaActiva) {
                if (coincideTexto && coincideCategoria) {
                    item.style.display = 'block';
                    itemsVisibles++;
                } else {
                    item.style.display = 'none';
                }
                btnCargarMas.style.display = 'none';
            } else {
                if (index < limiteActual) {
                    item.style.display = 'block';
                    itemsVisibles++;
                } else {
                    item.style.display = 'none';
                }
            }
        });
        if (!hayBusquedaActiva) {
            if (limiteActual >= faqItems.length) {
                btnCargarMas.style.display = 'none';
            } else {
                btnCargarMas.style.display = 'inline-block';
            }
        }
        if (itemsVisibles === 0 && hayBusquedaActiva) {
            msjSinResultados.style.display = 'block';
        } else {
            msjSinResultados.style.display = 'none';
        }
    }
    if(btnCargarMas) {
        btnCargarMas.addEventListener('click', function() {
            limiteActual += incremento;
            filtrarContenido();
        });
    }
    buscador.addEventListener('input', filtrarContenido);

    btnCategorias.forEach(btn => {
        btn.addEventListener('click', function() {
            btnCategorias.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            limiteActual = 10; 
            filtrarContenido();
        });
    });
    filtrarContenido();
});
</script>

</body>
</html>