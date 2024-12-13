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

// Obtener los privilegios disponibles
$query_privilegios = "SELECT idPrivilegio, nombrePrivilegio FROM privilegio";
$result_privilegios = mysqli_query($con, $query_privilegios);
if (!$result_privilegios) {
    die("Error: No se pudieron obtener los privilegios.");
}

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombreGrupo = mysqli_real_escape_string($con, $_POST['nombre_grupo']);
    $idPrivilegio = mysqli_real_escape_string($con, $_POST['id_privilegio']);

    // Insertar el nuevo grupo en la base de datos
    $query_insert = "
        INSERT INTO Grupo (nombreGrupo, idPrivilegio, idOrganizacion)
        VALUES ('$nombreGrupo', $idPrivilegio, $idOrganizacion)
    ";

    if (mysqli_query($con, $query_insert)) {
        echo "<p style='color:green;'>¡Grupo registrado exitosamente!</p>";
    } else {
        echo "<p style='color:red;'>Error al registrar el grupo.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Grupo</title>

    <link rel="stylesheet" href="../estilo/header.css">
    <link rel="stylesheet" href="../estilo/registrar_usuario.css">
</head>
<body>
<div class="header">
    <img src="../img/logo.png" alt="Logo">
    <h1>REGISTRAR GRUPO</h1>
    <a href="pagina_principal.php" >Menu Principal</a>
</div>
<div class="main-container">
    <div class="form-container">
        <h2>Registrar Nuevo Grupo</h2>
        <?php if (isset($error_message)): ?>
            <p style="color:red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form id="form-grupo" method="POST" action="">
            <table>
                <tr>
                    <td>Nombre del Grupo:</td>
                    <td><input name="nombre_grupo" type="text" maxlength="64" required></td>
                </tr>
                <tr>
                    <td>Privilegio:</td>
                    <td>
                        <select name="id_privilegio" required>
                            <?php while ($privilegio = mysqli_fetch_assoc($result_privilegios)): ?>
                                <option value="<?php echo $privilegio['idPrivilegio']; ?>">
                                    <?php echo $privilegio['nombrePrivilegio']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <div class="submit-container">
                <button type="submit" class="btn-fancy">Guardar Grupo</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
