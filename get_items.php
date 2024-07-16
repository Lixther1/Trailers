<?php
require_once 'config.php';

$sql = "SELECT id_item, descripcion, fecha_creacion FROM items";
$result = $conn->query($sql);

$items = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
} else {
    echo "0 resultados";
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($items);
?>
