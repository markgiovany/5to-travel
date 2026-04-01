<!DOCTYPE html>
<html>
<head>

<title>Login</title>

<link rel="stylesheet" href="styles/logins.css">
<script src="https://accounts.google.com/gsi/client" async defer></script>

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

<script src="js/google.js"></script>

</body>
</html>