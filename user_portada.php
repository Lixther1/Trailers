<?php
include 'config.php';
session_start();

if ($_SESSION['rol'] != 'mortal') {
    header("Location: index.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    $id_unidad = isset($_POST['id_unidad']) ? $_POST['id_unidad'] : null;
    $estatus_unidad = isset($_POST['estatus_unidad']) ? $_POST['estatus_unidad'] : null;
    $creado_por = $_SESSION['id_usuario'];
    $fecha_creacion = date('Y-m-d H:i:s');

    $items_nox = [];
    $items_check = [];
    $total_items = 0;

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'item_') === 0) {
            $total_items++;
            $item_id = str_replace('item_', '', $key);
            if ($value === '❌') {
                $items_nox[] = (int)$item_id;
            } elseif ($value === '✔') {
                $items_check[] = (int)$item_id;
            }
        }
    }

    if (count($items_nox) + count($items_check) !== $total_items) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los items deben estar marcados.']);
        exit;
    }

    $conn->begin_transaction();

    try {
        $stmt_evento = $conn->prepare("INSERT INTO eventos_de_item (id_lista, id_item, chk, fecha_creacion, id_user) VALUES (?, ?, ?, NOW(), ?)");

        // Insertar los items marcados con ❌
        if (!empty($items_nox)) {
            $items_str_nox = implode(',', $items_nox);
            $stmt = $conn->prepare("INSERT INTO lista_de_verificacion (id_unidad, items, estatus_items, estatus_unidad, creado_por, fecha_creacion) VALUES (?, ?, 0, ?, ?, NOW())");
            $stmt->bind_param("issi", $id_unidad, $items_str_nox, $estatus_unidad, $creado_por);

            if (!$stmt->execute()) {
                throw new Exception('Error al enviar el checklist: ' . $stmt->error);
            }
            $id_lista_nox = $conn->insert_id;

            foreach ($items_nox as $item_id) {
                $chk = 0; // ❌
                $stmt_evento->bind_param("iiii", $id_lista_nox, $item_id, $chk, $creado_por);
                $stmt_evento->execute();
                
                $photo_key = 'photo_' . $item_id;
                if (isset($_FILES[$photo_key])) {
                    $photo = $_FILES[$photo_key];
                    if ($photo['error'] === UPLOAD_ERR_OK) {
                        $photo_name = uniqid('photo_', true) . '.' . pathinfo($photo['name'], PATHINFO_EXTENSION);
                        $photo_path = 'img/' . $photo_name;
                        move_uploaded_file($photo['tmp_name'], $photo_path);

                        $stmt_photo = $conn->prepare("INSERT INTO fotos (id_evento_item, foto_path) VALUES (?, ?)");
                        $stmt_photo->bind_param("is", $id_lista_nox, $photo_path);
                        $stmt_photo->execute();
                    }
                }
            }
        }

        // Insertar los items marcados con ✔
        if (!empty($items_check)) {
            $items_str_check = implode(',', $items_check);
            $stmt = $conn->prepare("INSERT INTO lista_de_verificacion (id_unidad, items, estatus_items, estatus_unidad, creado_por, fecha_creacion) VALUES (?, ?, 1, ?, ?, NOW())");
            $stmt->bind_param("issi", $id_unidad, $items_str_check, $estatus_unidad, $creado_por);

            if (!$stmt->execute()) {
                throw new Exception('Error al enviar el checklist: ' . $stmt->error);
            }
            $id_lista_check = $conn->insert_id;

            foreach ($items_check as $item_id) {
                $chk = 1; // ✔
                $stmt_evento->bind_param("iiii", $id_lista_check, $item_id, $chk, $creado_por);
                $stmt_evento->execute();
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Checklist enviado con éxito.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

$conn->close();

include 'header.html';
?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>User Portada</title>
    <link rel="stylesheet" href="./estilosMortales.css">
    <link rel="stylesheet" href="./estilosMortales2.css">
    <script src="scriptuser.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
        #checklist-container {
            display: flex;
        }
        .checklist-column {
            margin-right: 20px;
        }
    </style>
</head>
<body>
<div class="contenedor">
    <a href="#" class="boton-verificacion" id="boton-verificacion">Verificación vehicular</a>
    <div id="botones-extra" style="display: none;">
        <button class="boton-adicional" id="boton-tractos">Tractos</button>
        <button class="boton-adicional" id="boton-dolly">Dolly</button>
        <button class="boton-adicional" id="boton-cajas">Cajas</button>
    </div>
    <div id="desplegable" style="display: none;">
        <select class="boton-adicional" id="seleccion-opcion" name="id_unidad" required>
            <option value="">Selecciona una Unidad</option>
            <?php include_once 'get_unidades.php'; ?>
        </select>
        <button class="boton-adicional" id="boton-continuar" style="margin-left: 10px;">Continuar</button>
    </div>
    <div id="botones-entrada-salida" style="display: none;">
        <button class="boton-adicional" id="boton-entrada">Entrada</button>
        <button class="boton-adicional" id="boton-salida">Salida</button>
    </div>
    <div id="contenedor-regresar" style="display: none;">
        <button class="boton-regresar" id="boton-regresar">
            <img src="img/arrow.png" alt="Logout">
        </button>
    </div>
    <form id="checklist-form" method="post">
        <h3>Checklist de Items</h3>
        <div id="checklist-container"></div>
        <div style="display: inline-block;">
            <label for="lista_verificacion">Selecciona Estatus de la Unidad:</label>
            <select name="estatus_unidad" id="lista_verificacion" required>
                <option value="">Estatus Unidad</option>
                <option value="Disponible">Disponible</option>
                <option value="Mantenimiento">Mantenimiento</option>
                <option value="Fuera de servicio">Fuera de servicio</option>
            </select>
        </div>
        <input type="hidden" name="id_unidad" id="id_unidad_input">
        <button type="submit">Enviar Checklist</button>
        <div id="error-message" style="color: red; display: none;"></div>
    </form>
    <form id="logoutForm" action="logout.php" method="post">
        <button type="submit" id="logoutButton">
            <img src="img/logout.png" alt="Logout" style="width: 30px; height: 30px;">
        </button>
    </form>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("checklist-form");
    const checklistContainer = document.getElementById("checklist-container");

    function getChecklistItems() {
        return fetch('get_items.php')
            .then(response => response.json())
            .then(data => data);
    }

    function displayItems() {
        getChecklistItems().then(items => {
            let html = "";

            const itemsPerColumn = 9;
            const columnCount = Math.ceil(items.length / itemsPerColumn);

            for (let col = 0; col < columnCount; col++) {
                html += '<div class="checklist-column">';
                html += '<table>';
                html += '<thead><tr><th>Items</th><th>Check</th></tr></thead>';
                html += '<tbody>';
                for (let i = col * itemsPerColumn; i < (col + 1) * itemsPerColumn && i < items.length; i++) {
                    const item = items[i];
                    html += '<tr>';
                    html += `<td>${item.descripcion}</td>`;
                    html += '<td class="checkbox">';
                    html += '<span data-value="✔">✔️</span>';
                    html += '<span data-value="❌">❌</span>';
                    html += '<span class="emoji" style="display: none; cursor: pointer;">📷</span>';
                    html += `<input type="file" accept="image/*" capture="camera" name="photo_${item.id_item}" style="display:none;">`;
                    html += `<input type="hidden" name="item_${item.id_item}" value="">`;
                    html += '</td>';
                    html += '</tr>';
                }
                html += '</tbody>';
                html += '</table>';
                html += '</div>';
            }
            checklistContainer.innerHTML = html;
        });
    }

    form.addEventListener("submit", function(event) {
        event.preventDefault();

        const formData = new FormData(form);
        let allItemsMarked = true;

        formData.forEach((value, key) => {
            if (key.startsWith('item_') && (value !== '❌' && value !== '✔')) {
                allItemsMarked = false;
            }
        });

        if (!allItemsMarked) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Todos los items deben estar marcados.'
            });
            return;
        }

        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'error') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: data.message
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al enviar el checklist.'
            });
            console.error('Error:', error);
        });
    });

    checklistContainer.addEventListener('click', function(event) {
        const target = event.target;
        if (target.classList.contains('emoji')) {
            const fileInput = target.parentElement.querySelector('input[type="file"]');
            fileInput.click(); // Abre la cámara
        } else if (target.matches('.checkbox span')) {
            const span = target;
            const checkbox = span.parentElement;
            const input = checkbox.querySelector('input[type="hidden"]');
            const value = span.getAttribute('data-value');
            
            const emoji = checkbox.querySelector('.emoji');
            
            if (value === '❌') {
                emoji.style.display = 'inline'; 
            } else if (value === '✔') {
                emoji.style.display = 'none'; 
            }
            
            input.value = value;
            checkbox.classList.remove('checked', 'unchecked');
            checkbox.classList.add(value === '✔' ? 'checked' : 'unchecked');
        }
    });

    displayItems();
});
</script>


</body>
</html>
