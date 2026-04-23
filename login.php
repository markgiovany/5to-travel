<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>

<title>Login</title>

<link rel="stylesheet" href="styles/logins.css">

</head>

<body>

<div class="container">

<div class="left">

<img src="imagenes/loginfondo.jpg" class="bg">

<div class="text">

<h2>"Tu próxima aventura comienza aquí"</h2>

</div>

</div>

<div class="right">

<h2>Iniciar Sesión</h2>

<?php if(isset($_SESSION['error_login'])): ?>
    <div style="background-color: #f8d7da; color: #842029; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
        <?php 
            echo $_SESSION['error_login']; 
            unset($_SESSION['error_login']); 
        ?>
    </div>
<?php endif; ?>

<form action="auth/login.php" method="POST">

<input type="email" name="email" placeholder="Gmail" required>

<input type="password" name="password" placeholder="Contraseña" required>

<button type="submit">Acceder</button>

</form>

<div id="g_id_onload"
data-client_id="598354696647-ro7off4rgjplgm0cuvipd47b5jkffekh.apps.googleusercontent.com"
data-callback="handleCredentialResponse">
</div>


<p>¿No tienes una cuenta?</p>

<a href="registro.php">
<button class="register">¡Regístrate!</button>
</a>

</div>

</div>

</body>
</html>