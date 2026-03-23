<!DOCTYPE html>
<html>

<head>

<title>Registro</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

</head>

<body>

<div class="container">

<!-- LADO IZQUIERDO -->

<div class="left">

<img src="img/fondo.jpg" class="bg">

<div class="text">
<h2>Descubre destinos increíbles</h2>
<p>Reserva hoteles, vuelos y experiencias inolvidables</p>
</div>

</div>

<!-- LADO DERECHO -->

<div class="right">

<h2>Crear Cuenta</h2>

<form action="auth/registrar.php" method="POST">

<input type="text" name="nombre" placeholder="Nombre" required>

<input type="text" name="apellido" placeholder="Apellidos" required>

<select name="lada" required>

<option value="">Selecciona lada</option>
<option value="+52">+52 México</option>
<option value="+1">+1 USA</option>
<option value="+34">+34 España</option>

</select>

<input type="text" name="telefono" placeholder="Número telefónico">

<input type="email" name="email" placeholder="Correo electrónico" required>

<input type="password" name="password" placeholder="Contraseña" required>

<input type="password" name="confirmar" placeholder="Confirmar contraseña" required>

<button type="submit">Registrar</button>

</form>

<p>¿Ya tienes cuenta?</p>

<a href="index.php">
<button class="register">Iniciar sesión</button>
</a>

</div>

</div>

</body>
</html>