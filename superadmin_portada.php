<?php
session_start();
if ($_SESSION['rol'] != 'superadmin') {
    header("Location: index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SuperAdmin Portada</title>
</head>
<body>
    <h1>Bienvenido, SuperAdmin</h1>
    <!-- Contenido para superadmin -->
    <form id="logout-form" action="logout.php" method="POST">
    <button type="submit" class="btn btn-danger">Cerrar sesión</button>
</form>
</body>
</html>
