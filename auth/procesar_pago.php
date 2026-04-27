<?php
session_start();
include("../config/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_uuid'])) {
    $user_uuid = $_SESSION['user_uuid'];
    
    // LIMPIEZA DE DATOS
    $nombre   = trim(mysqli_real_escape_string($config, $_POST['nombre']));
    $email    = trim(mysqli_real_escape_string($config, $_POST['email']));
    $telefono = trim(mysqli_real_escape_string($config, $_POST['telefono']));
    $metodo   = $_POST['metodo']; 

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: El formato del correo electrónico no es válido.");
    }
    if (!preg_match('/^[0-9]{10}$/', $telefono)) {
        die("Error: El teléfono debe tener exactamente 10 dígitos numéricos.");
    }

    // Dividir nombre para la tabla de usuarios
    $partes = explode(' ', $nombre, 2);
    $first_name = $partes[0];
    $last_name  = $partes[1] ?? '';

    // 2. INICIO DE TRANSACCIÓN
    mysqli_begin_transaction($config);

    try {
        // Actualizar datos del perfil del usuario
        mysqli_query($config, "UPDATE usr_users SET first_name = '$first_name', last_name = '$last_name' WHERE uuid = '$user_uuid'");

        // Manejo de Email (Update o Insert)
        $res_e = mysqli_query($config, "SELECT id_email FROM usr_emails WHERE user_uuid = '$user_uuid'");
        if (mysqli_num_rows($res_e) > 0) {
            mysqli_query($config, "UPDATE usr_emails SET email = '$email' WHERE user_uuid = '$user_uuid'");
        } else {
            mysqli_query($config, "INSERT INTO usr_emails (email, user_uuid) VALUES ('$email', '$user_uuid')");
        }

        // Manejo de Teléfono
        $res_t = mysqli_query($config, "SELECT id_telefono FROM usr_telefonos WHERE user_uuid = '$user_uuid' AND telefono = '$telefono'");
        if (mysqli_num_rows($res_t) == 0) {
            mysqli_query($config, "INSERT INTO usr_telefonos (telefono, user_uuid) VALUES ('$telefono', '$user_uuid')");
        }

        // 3. CREAR RESERVA (res_reserva)
        $uuid_reserva = bin2hex(random_bytes(16));
        // Efectivo (2) -> 3 (Pendiente), Tarjeta (1) -> 8 (Revisión/Confirmado)
        $id_status_reserva = 8; 

$q_reserva = "INSERT INTO res_reserva (uuid_reserva, user_uuid, id_habitacion, id_catalogo, fecha_entrada, fecha_salida, cantidad_personas, id_status) 
              VALUES ('$uuid_reserva', '$user_uuid', '{$_POST['id_habitacion']}', '{$_POST['id_catalogo']}', '{$_POST['fecha_entrada']}', '{$_POST['fecha_salida']}', '{$_POST['cantidad_personas']}', '$id_status_reserva')";
        
        if (!mysqli_query($config, $q_reserva)) {
            throw new Exception(mysqli_error($config));
        }
        
        $id_reserva_new = mysqli_insert_id($config);

        // Tarjeta (1) -> 5 (Aprobado), Efectivo (2) -> 3 (Pendiente)
        $id_status_pago = ($metodo == 1) ? 5 : 3; 
        $uuid_pago = bin2hex(random_bytes(16));
        
        $q_pago = "INSERT INTO res_registro_pago (uuid_pago, id_reserva, id_metodo_pago, monto, fecha_pago, id_status) 
                   VALUES ('$uuid_pago', '$id_reserva_new', '$metodo', '{$_POST['total_pago']}', NOW(), '$id_status_pago')";
        
        if (!mysqli_query($config, $q_pago)) {
            throw new Exception(mysqli_error($config));
        }

        mysqli_commit($config);

        // include("../correo_confirmacion.php");

        echo "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta http-equiv='refresh' content='5;url=../home.php'>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <title>Reserva Exitosa</title>
        </head>
        <body class='d-flex align-items-center justify-content-center' style='height: 100vh; background-color: #f4f7f6;'>
            <div class='text-center p-5 bg-white rounded-4 shadow border'>
                <h1 class='text-success fw-bold'>¡Reserva realizada con éxito!</h1>
                <p class='text-muted small'>Serás redirigido al inicio en 5 segundos...</p>
                <div class='spinner-border text-primary' role='status'></div>
            </div>
        </body>
        </html>";
        exit();

    } catch (Exception $e) {
        // Si algo falla, revertimos todos los cambios para no dejar basura en la DB
        mysqli_rollback($config);
        die("Error crítico: " . $e->getMessage());
    }
}