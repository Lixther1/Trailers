<?php
include 'config.php';

$sql = "SELECT id_unidad, descripcion FROM Unidades";
$result = $conn->query($sql);

$options = "";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $id_unidad = $row["id_unidad"];
        $descripcion = $row["descripcion"];
        $options .= "<option value='$id_unidad'>$descripcion</option>";
    }
} else {
    $options = "<option value=''>No hay opciones disponibles</option>";
}

$conn->close();

echo $options;
?>
