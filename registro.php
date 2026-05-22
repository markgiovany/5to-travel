<!DOCTYPE html>
<html>

<head>
<title>Registro</title>

<link rel="stylesheet" href="styles/logins.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

</head>

<body>

<div class="container">

<!-- LADO IZQUIERDO -->
<div class="left">

<img src="imagenes/loginfondo.jpg" class="bg">

<div class="text">
<h2>"Tu próxima aventura comienza aquí"</h2>
</div>

</div>

<!-- LADO DERECHO -->
<div class="right">

<h2>Crear Cuenta</h2>

<form action="auth/registrar.php" method="POST">

<input type="text" name="nombre" placeholder="Nombre" required>

<input type="text" name="apellido" placeholder="Apellidos" required>

<input type="tel" name="telefono" placeholder="Número telefónico" required minlength="10" maxlength="10" pattern="[0-9]{10}" title="El teléfono debe tener exactamente 10 números.">

<input type="email" name="email" placeholder="Correo electrónico" required>

<input type="password" name="password" placeholder="Contraseña" required>

<input type="password" name="confirmar" placeholder="Confirmar contraseña" required>

<button type="submit">Registrar</button>

</form>

<p>¿Ya tienes cuenta?</p>

<a href="confirmar_regristro.html">
<button class="register">Iniciar sesión</button>
</a>

</div>

</div>

</body>
</html>