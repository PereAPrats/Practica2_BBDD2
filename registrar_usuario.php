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
    $correo = mysqli_real_escape_string($con, $_POST['correo_usuario']);
    $nombre = mysqli_real_escape_string($con, $_POST['nombre_usuario']);
    $apellidos = mysqli_real_escape_string($con, $_POST['apellidos']);
    $telefono = mysqli_real_escape_string($con, $_POST['telefono']);
    $pass = mysqli_real_escape_string($con, $_POST['password']);
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
                    header("Location: pagina_principal.php");
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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="../estilo/registrar_usuario.css">
    <link rel="stylesheet" href="../estilo/header.css">
</head>
<body>
<div class="header">
    <img src="../img/logo.png" alt="Logo">
    <h1>Registrar Usuario</h1>
</div>
<div class="main-container">
    <div class="form-container">
        <h2>Registrar Usuario Administrador</h2>
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?= htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <table>
                <tr>
                    <td>Nombre:</td>
                    <td><input name="nombre_usuario" type="text" maxlength="64" required></td>
                </tr>
                <tr>
                    <td>Apellidos:</td>
                    <td><input name="apellidos" type="text" maxlength="64" required></td>
                </tr>
                <tr>
                    <td>Teléfono:</td>
                    <td><input name="telefono" type="tel" maxlength="16" pattern="[0-9+()\- ]{1,16}" required></td>
                </tr>
                <tr>
                    <td>Correo:</td>
                    <td><input name="correo_usuario" type="email" required></td>
                </tr>
                <tr>
                    <td>Contraseña:</td>
                    <td><input name="password" type="password" required></td>
                </tr>
                <tr>
                    <td>Confirmar Contraseña:</td>
                    <td><input name="confirm_pass" type="password" required></td>
                </tr>
            </table>
            <div class="submit-container">
                <input type="submit" value="Guardar Usuario" class="btn-fancy">
            </div>
        </form>
    </div>
</div>
</body>
</html>

