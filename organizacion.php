<?php
session_start();

// Comprobamos si la sesión está iniciada y si el usuario tiene permisos
if (!isset($_SESSION['usuario'])) {
    echo "No has iniciado sesión. Por favor, inicia sesión.";
    exit;
}

$user_id = $_SESSION['usuario']; // El ID del usuario que ha iniciado sesión

// Conexión a la base de datos
include "conexion.php";

// Obtener la organización de la que forma parte el usuario
$query_organizacion = "
    SELECT o.idOrganizacion, o.nombreO, o.telefonoO, o.direccionO, o.correoO, g.idPrivilegio
    FROM organizacion o
    JOIN grupo g ON o.idOrganizacion = g.idOrganizacion
    JOIN usuario u ON g.idGrupo = u.idGrupo
    WHERE u.correo = '$user_id'
";
$result_organizacion = mysqli_query($con, $query_organizacion);

if (mysqli_num_rows($result_organizacion) > 0) {
    $organizacion = mysqli_fetch_assoc($result_organizacion);
    $id_organizacion = $organizacion['idOrganizacion'];
    $privilegio_usuario = $organizacion['idPrivilegio']; // ID del privilegio del usuario actual
} else {
    echo "<p>No se encontró la organización asociada a tu usuario.</p>";
    exit;
}

// Obtener todos los usuarios de la organización
$query_usuarios = "
    SELECT u.idPersonaU, u.nombre, u.apellidos, u.correo, u.telefono, g.idPrivilegio
    FROM usuario u
    JOIN grupo g ON u.idGrupo = g.idGrupo
    WHERE g.idOrganizacion = '$id_organizacion'
";
$result_usuarios = mysqli_query($con, $query_usuarios);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicia Sesión</title>
    <link rel="stylesheet" href="../estilo/header.css">
    <link rel="stylesheet" href="../estilo/organizacion.css">
</head>
<body>
<div class="header">
    <img src="../img/logo.png" alt="Logo">
    <h1>Organizacion</h1>
    <a href="pagina_principal.php" >Menu Principal</a>
</div>
<body>

<div class="container">
    <section class="info-organizacion">
        <h2>Detalles de la Organización</h2>
        <p><strong>Teléfono:</strong> <?php echo $organizacion['telefonoO']; ?></p>
        <p><strong>Dirección:</strong> <?php echo $organizacion['direccionO']; ?></p>
        <p><strong>Correo:</strong> <?php echo $organizacion['correoO']; ?></p>

        <?php if ($privilegio_usuario == 1) { ?>
            <button class="btn-fancy" onclick="window.location.href='registrar_usuario.php';">Añadir Usuario</button>
            <button class="btn-fancy" onclick="window.location.href='registrar_grupo.php';">Añadir Grupo</button>
        <?php } ?>
    </section>

    <section class="usuarios">
        <h2>Miembros de la Organización</h2>
        <?php if (mysqli_num_rows($result_usuarios) > 0) { ?>
            <table class="user-table">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Privilegio</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($usuario = mysqli_fetch_assoc($result_usuarios)) {
                    $privilegio = $usuario['idPrivilegio'] == 1 ? "Administrador" : "Usuario"; ?>
                    <tr>
                        <td><?php echo $usuario['nombre']; ?></td>
                        <td><?php echo $usuario['apellidos']; ?></td>
                        <td><?php echo $usuario['correo']; ?></td>
                        <td><?php echo $usuario['telefono']; ?></td>
                        <td><?php echo $privilegio; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No hay usuarios registrados en esta organización.</p>
        <?php } ?>
    </section>
</div>

</body>
</html>
