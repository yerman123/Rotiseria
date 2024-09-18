<?php
include("conexion.php");

$q = $_GET['q']; #Esto busca que el nombre empiece con la letra o letras ingresadas

$sql = "SELECT Nombre FROM Clientes WHERE Nombre LIKE ?";
$stmt = $conn->prepare($sql);
$like = $q . "%";  // Coincide solo con nombres que empiezan con el valor de $q
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();

$clientes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
}

echo json_encode($clientes);

$conn->close();
?>
