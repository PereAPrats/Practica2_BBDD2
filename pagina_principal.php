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
$nombreUsuario = null;

// Consultar la base de datos para obtener el nombre del usuario
$sql = "SELECT nombre FROM Usuario WHERE correo = '$correoUsuario'";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nombreUsuario = $row['nombre']; // Puede ser NULL si no está configurado
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    <link rel="stylesheet" href="estilo/header.css">
    <link rel="stylesheet" href="estilo/pagPrincipal.css">
</head>
<body>
<div class="header">
    <img src="img/logo.png" alt="Logo">
    <h1>PÁGINA PRINCIPAL</h1>
    <div class="user-info">
        <p>Bienvenido, 
            <?php if ($nombreUsuario): ?>
                <span id="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></span>
            <?php else: ?>
                <a href="datos_personales.php">Configurar datos personales</a>
            <?php endif; ?>
        </p>
    </div>
</div>
<div class="main-container">
    
    <!-- Contenido Principal Diferenciado -->
    <div class="buttons-container">
        <a href="pagina_paas.php" class="boton-link">
            <button class="boton">PaaS</button>
        </a>
        <a href="pagina_saas.php" class="boton-link">
            <button class="boton">SaaS</button>
        </a>
    </div>

    <div id="cliente-section" class="role-section">
        <h2>Panel del Usuario</h2>
        <ul>
            <li><a href="datos_personales.php">Ver datos personales</a></li>
            <li><a href="organizacion.php">Información de la organización</a></li>
        </ul>
    </div>

    <?php if ($tipoUsuario == "personal"): ?>
    <div id="personal-section" class="role-section">
        <h2>Panel del Personal</h2>
        <ul>
            <li><a href="gestionar_usuarios.html">Gestión de usuarios</a></li>
        </ul>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
