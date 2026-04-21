<?php 
session_start();
include("config/config.php"); 
if (!isset($_SESSION['user_uuid'])) {
    header("Location: index.php");
    exit();
}

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

    function filtrarContenido() {
        const textoBusqueda = buscador.value.toLowerCase();
        const categoriaActiva = document.querySelector('#filtro-categorias .nav-link.active').getAttribute('data-filter');
        let itemsVisibles = 0;

        faqItems.forEach(item => {
            const pregunta = item.querySelector('.accordion-button').textContent.toLowerCase();
            const respuesta = item.querySelector('.accordion-body').textContent.toLowerCase();
            const categoriaItem = item.getAttribute('data-categoria');

            const coincideTexto = pregunta.includes(textoBusqueda) || respuesta.includes(textoBusqueda);
            const coincideCategoria = (categoriaActiva === 'all') || (categoriaActiva === categoriaItem);

            if (coincideTexto && coincideCategoria) {
                item.style.display = 'block';
                itemsVisibles++;
            } else {
                item.style.display = 'none';
            }
        });

        if (itemsVisibles === 0) {
            msjSinResultados.style.display = 'block';
        } else {
            msjSinResultados.style.display = 'none';
        }
    }

    buscador.addEventListener('input', filtrarContenido);

    btnCategorias.forEach(btn => {
        btn.addEventListener('click', function() {
            btnCategorias.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            filtrarContenido();
        });
    });
});
</script>

</body>
</html>