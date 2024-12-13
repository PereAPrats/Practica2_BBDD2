<?php
session_start();

if (!isset($_SESSION)) {
    echo "session no iniciada";
    // Recollida de paràmetres
    $user = $_POST['correo'];
    $pass = $_POST['pass'];
    $_SESSION['usuario'] = $user;
    $_SESSION['tipoUsuario'] = $user;
}

// Connexió a bd
include "conexion.php";

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos de entrada y escapar posibles caracteres peligrosos
    $correo = mysqli_real_escape_string($con, $_POST['correo']);
    $pass = mysqli_real_escape_string($con, $_POST['pass']);

    // Consulta para obtener el idPersonaU basado en el correo
    $query = "SELECT idPersonaU FROM usuario WHERE correo = '$correo'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        // Si el correo existe, obtener el idPersonaU
        $user = mysqli_fetch_assoc($result);
        $user_id = $user['idPersonaU'];

        // Ahora buscamos en la tabla contrasenya si existe la contraseña para ese idPersonaU
        $query_pass = "SELECT contrasenya FROM contrasenya WHERE idPersonaU = '$user_id'";
        $result_pass = mysqli_query($con, $query_pass);

        if (mysqli_num_rows($result_pass) > 0) {
            // Obtener el hash almacenado en la base de datos
            $row = mysqli_fetch_assoc($result_pass);
            $stored_hash = $row['contrasenya'];  // Asegúrate de que el campo en la BD se llama 'contrasenya'

            // Verificar si la contraseña ingresada coincide con el hash almacenado usando crypt()
            if (crypt($pass, $stored_hash) === $stored_hash) {
                // Iniciar sesión y redirigir a la página principal
                $_SESSION['usuario'] = $correo;  // Guardamos el correo del usuario en la sesión
                $_SESSION['tipoUsuario'] = "usuario";  // Guardamos el correo del usuario en la sesión
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
?>