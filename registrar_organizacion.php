<?php
session_start();

if (!isset($_SESSION)) {
    echo "session no iniciada";
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $_SESSION['user'] = $user;
}

// Conexión a la base de datos
include "conexion.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger parámetros del formulario
    $nombre_organizacion = mysqli_real_escape_string($con, $_POST['nombre_organizacion']);
    $telefono_organizacion = mysqli_real_escape_string($con, $_POST['telefono']);
    $direccion_organizacion = mysqli_real_escape_string($con, $_POST['direccion']);
    $correo_organizacion = mysqli_real_escape_string($con, $_POST['correo_organizacion']);
    $CIF = mysqli_real_escape_string($con, $_POST['CIF']);

    $nombre_usuario = mysqli_real_escape_string($con, $_POST['nombre_usuario']);
    $apellidos_usuario = mysqli_real_escape_string($con, $_POST['apellidos']);
    $telefono_usuario = mysqli_real_escape_string($con, $_POST['telefono']);
    $correo_usuario = mysqli_real_escape_string($con, $_POST['correo_usuario']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $confirm_pass = mysqli_real_escape_string($con, $_POST['confirm_pass']);

    // Verificar si las contraseñas coinciden
    if ($password !== $confirm_pass) {
        echo "<p style='color:red;'>Las contraseñas no coinciden. Intenta nuevamente.</p>";
        exit;
    }

    // Iniciar transacción
    mysqli_begin_transaction($con);

    try {
        // Verificar si el correo de la organización ya está registrado
        $query_organizacion = "SELECT * FROM organizacion WHERE correoO = '$correo_organizacion'";
        $result_organizacion = mysqli_query($con, $query_organizacion);
        if (mysqli_num_rows($result_organizacion) > 0) {
            throw new Exception("El correo de la organización ya está registrado.");
        }

        // Verificar si el CIF ya está registrado
        $query_cif = "SELECT * FROM organizacion WHERE CIF = '$CIF'";
        $result_cif = mysqli_query($con, $query_cif);
        if (mysqli_num_rows($result_cif) > 0) {
            throw new Exception("El CIF de la organización ya está registrado.");
        }

        // Insertar la organización
        $query_insert_organizacion = "
            INSERT INTO organizacion (nombreO, telefonoO, direccionO, correoO, CIF) 
            VALUES ('$nombre_organizacion', '$telefono_organizacion', '$direccion_organizacion', '$correo_organizacion', '$CIF')
        ";
        if (!mysqli_query($con, $query_insert_organizacion)) {
            throw new Exception("Error al registrar la organización.");
        }

        // Obtener el ID de la organización recién insertada
        $id_organizacion = mysqli_insert_id($con);

        // Verificar si el correo del usuario ya está registrado
        $query_usuario = "SELECT * FROM usuario WHERE correo = '$correo_usuario'";
        $result_usuario = mysqli_query($con, $query_usuario);
        if (mysqli_num_rows($result_usuario) > 0) {
            throw new Exception("El correo del usuario ya está registrado.");
        }

        // Generar nuevo grupo con privilegios máximos
        $query_insert_grupo = "
            INSERT INTO grupo (nombreGrupo, idPrivilegio, idOrganizacion) 
            VALUES ('Administrador', '1', '$id_organizacion')
        ";
        if (!mysqli_query($con, $query_insert_grupo)) {
            throw new Exception("Error al registrar el grupo.");
        }

        // Obtener el ID del nuevo grupo
        $id_grupo = mysqli_insert_id($con);

        // Generar sal y hash para la contraseña
        $salt = uniqid(mt_rand(), true);
        $hash = crypt($password, '$2y$10$' . $salt);

        // Insertar el usuario
        $query_insert_usuario = "
            INSERT INTO usuario (nombre, apellidos, correo, telefono, idGrupo) 
            VALUES ('$nombre_usuario', '$apellidos_usuario', '$correo_usuario', '$telefono_usuario', '$id_grupo')
        ";
        if (!mysqli_query($con, $query_insert_usuario)) {
            throw new Exception("Error al registrar el usuario.");
        }

        // Obtener el ID del nuevo usuario
        $user_id = mysqli_insert_id($con);

        // Insertar la contraseña
        $query_insert_pass = "
            INSERT INTO contrasenya (idPersonaU, contrasenya, fechaCreacionContrasenya) 
            VALUES ('$user_id', '$hash', NOW())
        ";
        if (!mysqli_query($con, $query_insert_pass)) {
            throw new Exception("Error al registrar la contraseña.");
        }

        // Confirmar la transacción
        mysqli_commit($con);
        echo "<p style='color:green;'>¡Registro exitoso! Ahora puedes iniciar sesión.</p>";
        header("Location: html/index.html");
    } catch (Exception $e) {
        // Revertir cambios si ocurre un error
        mysqli_rollback($con);
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    }
}
?>
