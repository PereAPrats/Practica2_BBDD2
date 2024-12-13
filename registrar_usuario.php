<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: html/index.html"); // Redirigir al formulario de inicio de sesión si no está autenticado
    exit;
}

// Conexión a la base de datos
include "conexion.php";

// Obtener el correo del usuario actual desde la sesión
$correo_actual = $_SESSION['usuario'];

// Obtener la organización del usuario actual
$query_org = "
    SELECT o.idOrganizacion, o.nombreO
    FROM Usuario u
    JOIN Grupo g ON u.idGrupo = g.idGrupo
    JOIN Organizacion o ON g.idOrganizacion = o.idOrganizacion
    WHERE u.correo = '$correo_actual'
";
$result_org = mysqli_query($con, $query_org);
if (!$result_org || mysqli_num_rows($result_org) === 0) {
    die("Error: No se pudo obtener la organización del usuario.");
}
$org = mysqli_fetch_assoc($result_org);
$idOrganizacion = $org['idOrganizacion'];

// Obtener los grupos de la organización
$query_grupos = "
    SELECT idGrupo, nombreGrupo 
    FROM Grupo 
    WHERE idOrganizacion = $idOrganizacion
";
$result_grupos = mysqli_query($con, $query_grupos);
if (!$result_grupos) {
    die("Error: No se pudieron obtener los grupos.");
}

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = mysqli_real_escape_string($con, $_POST['correo_usuario']);
    $nombre = mysqli_real_escape_string($con, $_POST['nombre_usuario']);
    $apellidos = mysqli_real_escape_string($con, $_POST['apellidos']);
    $telefono = mysqli_real_escape_string($con, $_POST['telefono']);
    $idGrupo = mysqli_real_escape_string($con, $_POST['id_grupo']);
    $pass = mysqli_real_escape_string($con, $_POST['password']);
    $confirm_pass = mysqli_real_escape_string($con, $_POST['confirm_pass']);

// Verificar si las contraseñas coinciden
    if ($pass !== $confirm_pass) {
        echo "<p style='color:red;'>Las contraseñas no coinciden. Por favor, intenta nuevamente.</p>";
    } else {
        mysqli_begin_transaction($con);
        try {
            // Registro de usuario en la base de datos
            $salt = uniqid(mt_rand(), true);
            $hash = crypt($pass, '$2y$10$' . $salt);
            $query_insert_usuario = "
            INSERT INTO Usuario (nombre, apellidos, correo, telefono, idGrupo)
            VALUES ('$nombre', '$apellidos', '$correo', '$telefono', $idGrupo)
        ";
            if (!mysqli_query($con, $query_insert_usuario)) {
                throw new Exception("Error al registrar el usuario.");
            } else {
                echo "<p style='color:red;'>Error al registrar el usuario.</p>";
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
            echo "<p style='color:green;'>¡Registro exitoso!</p>";
            header("Location: pagina_principal.php");

        } catch (Exception $e) {
            // Revertir cambios si ocurre un error
            mysqli_rollback($con);
            echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
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
    <link rel="stylesheet" href="../estilo/header.css">
    <link rel="stylesheet" href="../estilo/registrar_usuario.css">
</head>
<body>
<div class="header">
    <img src="../img/logo.png" alt="Logo">
    <h1>REGISTRAR USUARIO</h1>
    <a href="pagina_principal.php">Menu Principal</a>
</div>
<div class="main-container">
    <div class="form-container">
        <h2>Registrar Nuevo Usuario</h2>
        <?php if (isset($error_message)): ?>
            <p style="color:red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form id="form-usuario" method="POST" action="">
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
                    <td>Grupo:</td>
                    <td>
                        <select name="id_grupo" required>
                            <?php while ($grupo = mysqli_fetch_assoc($result_grupos)): ?>
                                <option value="<?php echo $grupo['idGrupo']; ?>">
                                    <?php echo $grupo['nombreGrupo']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
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
                <button type="submit" class="btn-fancy">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
