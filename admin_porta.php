<?php
session_start();
if ($_SESSION['rol'] != 'admin') {
    header("Location: index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Portada</title>
</head>
<body>
    <h1>Bienvenido, Admin</h1>
    <!-- Contenido para admin -->
    <form id="logout-form" action="logout.php" method="POST">
    <button type="submit" class="btn btn-danger">Cerrar sesión</button>
</form>
</body>
</html>
