<?php
include("../config/config.php");

// Habilitar el reporte de errores para ver detalles si algo más falla
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 1. Recibir y limpiar datos del formulario
    $first_name = mysqli_real_escape_string($config, $_POST['nombre']);
    $last_name  = mysqli_real_escape_string($config, $_POST['apellido']);
    $email      = mysqli_real_escape_string($config, $_POST['email']);
    $telefono   = mysqli_real_escape_string($config, $_POST['telefono']);
    
    // Usamos password_hash para mayor seguridad en lugar de MD2
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    // 2. Generar UUID único para el usuario
    $query_uuid = mysqli_query($config, "SELECT UUID() as uuid");
    $uuid_data  = mysqli_fetch_assoc($query_uuid);
    $uuid       = $uuid_data['uuid'];

    // 3. Iniciar Transacción para asegurar que se guarden todos los datos o ninguno
    mysqli_begin_transaction($config);

    // SQL 1: Insertar en usr_users (Sin created_at)
    // Campos según tu diagrama: uuid, first_name, last_name, id_status
    mysqli_query($config, "INSERT INTO usr_users (uuid, first_name, last_name, id_status) 
                           VALUES ('$uuid', '$first_name', '$last_name', 1)");

    // SQL 2: Insertar en usr_emails
    mysqli_query($config, "INSERT INTO usr_emails (email, user_uuid) 
                           VALUES ('$email', '$uuid')");

    // SQL 3: Insertar en usr_users_login (Sin created_at para evitar el error)
    // Campos: user_uuid, password, role, id_status
    mysqli_query($config, "INSERT INTO usr_users_login (user_uuid, password, role, id_status) 
                           VALUES ('$uuid', '$password', 'user', 1)");

    // SQL 4: Insertar en usr_telefonos
    mysqli_query($config, "INSERT INTO usr_telefonos (telefono, user_uuid) 
                           VALUES ('$telefono', '$uuid')");

    // Si todo salió bien, guardamos los cambios
    mysqli_commit($config);
    
    // Redireccionar al login con éxito
    header("Location: ../index.php?reg=success");
    exit();

} catch (mysqli_sql_exception $e) {
    // Si hay un error, deshacemos los cambios incompletos
    mysqli_rollback($config);
    
    echo "<h3>ERROR TÉCNICO DETECTADO:</h3>";
    echo "<p>El sistema no pudo completar el registro. Detalle:</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<p>Verifica que las tablas en tu base de datos no tengan la columna 'created_at' o agrégala manualmente.</p>";
}
?>