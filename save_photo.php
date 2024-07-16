<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'img/';
        $uploadFile = $uploadDir . basename($_FILES['photo']['name']);

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar el archivo.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir el archivo.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de solicitud no válido.']);
}
?>
