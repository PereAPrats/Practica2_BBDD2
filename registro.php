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

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = mysqli_real_escape_string($con, $_POST['correo']);
    $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
    $apellidos = mysqli_real_escape_string($con, $_POST['apellidos']);
    $telefono = mysqli_real_escape_string($con, $_POST['telefono']);
    $pass = mysqli_real_escape_string($con, $_POST['pass']);
    $confirm_pass = mysqli_real_escape_string($con, $_POST['confirm_pass']);

    // Verificar si la contraseña y la confirmación coinciden
    if ($pass !== $confirm_pass) {
        echo "<p style='color:red;'>Las contraseñas no coinciden, por favor intenta nuevamente.</p>";
    } else {
        // Verificar si el correo ya está registrado
        $query = "SELECT * FROM usuario WHERE correo = '$correo'";
        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) > 0) {
            echo "<p style='color:red;'>El correo ya está registrado. Intenta con otro.</p>";
        } else {
            // Generar un "sal" para la contraseña
            $salt = uniqid(mt_rand(), true);

            // Encriptar la contraseña con el "sal"
            $hash = crypt($pass, '$2y$10$' . $salt);

            // Insertar el nuevo usuario en la base de datos
            $query_insert = "
                INSERT INTO usuario (nombre, apellidos, correo, telefono) 
                VALUES ('$nombre', '$apellidos', '$correo', '$telefono')
            ";
            if (mysqli_query($con, $query_insert)) {
                // Obtener el user_id del nuevo usuario
                $user_id = mysqli_insert_id($con);

                // Insertar la contraseña en la tabla contraseñas con la fecha de creación
                $query_pass = "
                    INSERT INTO contrasenya (idPersonaU, contrasenya, fechaCreacionContrasenya) 
                    VALUES ('$user_id', '$hash', NOW())
                ";
                if (mysqli_query($con, $query_pass)) {
                    echo "<p style='color:green;'>¡Registro exitoso! Ahora puedes iniciar sesión.</p>";
                } else {
                    echo "<p style='color:red;'>Hubo un error al registrar la contraseña.</p>";
                }
            } else {
                echo "<p style='color:red;'>Hubo un error al registrar el usuario.</p>";
            }
        }
    }
}
?>
