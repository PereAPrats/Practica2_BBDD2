<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: html/index.html"); // Redirigir al formulario de inicio de sesión si no está autenticado
    exit;
}

include 'conexion.php';

$tipoUsuario = $_SESSION['tipoUsuario'];
$correoUsuario = $_SESSION['usuario'];
$mensaje = "";

// Obtener los datos actuales del usuario
if ($tipoUsuario == "personal") {
    $sql = "SELECT nombre, apellidos, telefono, especialidad FROM Personal WHERE correo = '$correoUsuario'";

    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre = $row['nombre'];
        $apellidos = $row['apellidos'];
        $telefono = $row['telefono'];
        $especialidad = $row['especialidad'];
    } else {
        $nombre = $apellidos = $telefono = $especialidad = "";
    }

} elseif ($tipoUsuario == "usuario") {
    $sql = "SELECT nombre, apellidos, telefono FROM Usuario WHERE correo = '$correoUsuario'";

    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre = $row['nombre'];
        $apellidos = $row['apellidos'];
        $telefono = $row['telefono'];
    } else {
        $nombre = $apellidos = $telefono = "";
    }
}

// Actualizar los datos si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoNombre = $con->real_escape_string($_POST['nombre']);
    $nuevosApellidos = $con->real_escape_string($_POST['apellidos']);
    $nuevoTelefono = $con->real_escape_string($_POST['telefono']);
    $nuevaEspecialidad = $con->real_escape_string($_POST['especialidad']);

    if ($tipoUsuario == "personal") {
        $sql = "UPDATE Personal SET nombre = '$nuevoNombre', apellidos = '$nuevosApellidos', telefono = '$nuevoTelefono', especialidad = '$nuevaEspecialidad' WHERE correo = '$correoUsuario'";
    } elseif ($tipoUsuario == "usuario") {
        $sql = "UPDATE Usuario SET nombre = '$nuevoNombre', apellidos = '$nuevosApellidos', telefono = '$nuevoTelefono' WHERE correo = '$correoUsuario'";
    }

    if ($con->query($sql) === TRUE) {
        $mensaje = "Datos actualizados con éxito.";
    } else {
        $mensaje = "Error al actualizar los datos: " . $con->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Datos Personales</title>
    <link rel="stylesheet" href="estilo/header.css">
    <link rel="stylesheet" href="estilo/datosPersonales.css">
</head>
<body>
    <div class="header">
        <img src="img/logo.png" alt="Logo">
        <h1>PÁGINA PRINCIPAL</h1>
        <div class="user-info">
            <p>Bienvenido, 
                <?php if ($nombre): ?>
                    <span id="user-name"><?php echo htmlspecialchars($nombre); ?></span>
                <?php else: ?>
                    <a href="datos_personales.php">Configurar datos personales</a>
                <?php endif; ?>
            </p>
        </div>
    </div>
    <div class="container">
        <h1>Actualizar Datos Personales</h1>
        <?php if ($mensaje): ?>
            <p class="message"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>

            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($apellidos); ?>" required>

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" required>

            <?php if ($tipoUsuario == "personal"):?>
            
            <label for="especialidad">Teléfono:</label>
            <input type="text" id="especialidad" name="especialidad" value="<?php echo htmlspecialchars($especialidad); ?>" required>
            
            <?php endif; ?>

            <button type="submit">Actualizar</button>
        </form>
        <a href="pagina_principal.php">Volver a la Página Principal</a>
    </div>
</body>
</html>
