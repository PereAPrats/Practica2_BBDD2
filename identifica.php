<?php
session_start();

if (!isset($_SESSION)) {
    echo "session no iniciada";
    // Recollida de paràmetres
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $_SESSION['user'] = $user;
}

// Connexió a bd
include "conexion.php";

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = mysqli_real_escape_string($con, $_POST['correo']);
    $pass = mysqli_real_escape_string($con, $_POST['pass']);

    // Consulta para obtener el user_id basado en el correo
    $query = "SELECT user_id FROM usuarios WHERE correo = '$correo'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        // Si el correo existe, obtener el user_id
        $user = mysqli_fetch_assoc($result);
        $user_id = $user['user_id'];

        // Ahora buscamos en la tabla contraseñas si existe la contraseña para ese user_id
        $query_pass = "SELECT contrasena FROM contraseñas WHERE user_id = '$user_id'";
        $result_pass = mysqli_query($con, $query_pass);

        if (mysqli_num_rows($result_pass) > 0) {
            // Obtener el hash almacenado en la base de datos
            $row = mysqli_fetch_assoc($result_pass);
            $stored_hash = $row['contrasena'];

            // Verificar si la contraseña ingresada coincide con el hash almacenado usando crypt()
            if (crypt($pass, $stored_hash) === $stored_hash) {
                // Iniciar sesión y redirigir a la página principal
                session_start();
                $_SESSION['usuario'] = $correo;
                header("Location: pagina_principal.php"); // Redirige a la página principal después de iniciar sesión
                exit;
            } else {
                // Si la contraseña es incorrecta
                echo "<p style='color:red;'>Contraseña incorrecta, por favor intenta nuevamente.</p>";
            }
        } else {
            echo "<p style='color:red;'>No se encontraron contraseñas para este usuario.</p>";
        }
    } else {
        echo "<p style='color:red;'>Correo no registrado, por favor intenta nuevamente.</p>";
    }
}