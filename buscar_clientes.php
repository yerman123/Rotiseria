<?php
include("conexion.php");

$q = $_GET['q']; #Esto busca que el nombre empiece con la letra o letras ingresadas

$query = "SELECT Nombre, DNI FROM Clientes WHERE Nombre LIKE ? LIMIT 10";
$stmt = $conn->prepare($query);
$search = "%" . $_GET['q'] . "%";
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();

$clientes = [];
while ($row = $result->fetch_assoc()) {
    $clientes[] = [
        "Nombre" => $row['Nombre'],
        "DNI" => $row['DNI']
    ];
}

echo json_encode($clientes);


$conn->close();
?>
