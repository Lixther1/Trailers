<?php
session_start();
include 'config.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] == 'admin') {
        header("Location: admin_portada.php");
    } elseif ($_SESSION['rol'] == 'mortal') {
        header("Location: user_portada.php");
    } elseif ($_SESSION['rol'] == 'superadmin') {
        header("Location: superadmin_portada.php");
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];

    $sql = "SELECT * FROM usuarios WHERE correo = ? AND contraseña = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $correo, $contraseña);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['rol'] = $row['rol'];
        $_SESSION['correo'] = $row['correo'];

        if ($row['rol'] == 'admin') {
            header("Location: admin_portada.php");
        } elseif ($row['rol'] == 'mortal') {
            header("Location: user_portada.php");
        } elseif ($row['rol'] == 'superadmin') {
            header("Location: superadmin_portada.php");
        }
        exit;
    } else {
        header("Location: index.html?error=true");
        exit;
    }
    $stmt->close();
}
?>
