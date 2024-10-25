<?php
include("conexion.php");

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener todos los clientes
$sql_clientes = "SELECT * FROM Clientes";
$result_clientes = $conn->query($sql_clientes);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
    <link rel="stylesheet" href="style/clientes.css">
    <link rel="stylesheet" href="style/navbar.css">
</head>
<body>

<div class='navbar'>
<a href='inicio.php?section=inicio'>Inicio</a>
<a href='pedidos.php?section=pedidos'>Agregar Pedidos</a>
<a href='total.php?section=total'>Total de Pedidos</a>
<a href='clientes.php?section=clientes' class='active'>Clientes</a>
<a href='productos.php'>Productos<a>
<a href='index.php' style='float:right;'>Cerrar sesión</a>
</div>

<h1>Lista de Clientes</h1>

<table border="1">
    <tr>
        <th>ID Cliente</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Dirección</th>
        <th>Teléfono</th>
        <th>Email</th>
        <th>DNI</th>
    </tr>
    <?php
    if ($result_clientes->num_rows > 0) {
        while ($row = $result_clientes->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['idClientes'] . "</td>";
            echo "<td>" . $row['Nombre'] . "</td>";
            echo "<td>" . $row['Apellido']. "</td>";
            echo "<td>" . $row['Direccion'] . "</td>";
            echo "<td>" . $row['Telefono'] . "</td>";
            echo "<td>" . $row['Email'] . "</td>";
            echo "<td>" . $row['DNI'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No hay clientes disponibles.</td></tr>";
    }
    ?>
</table>

</body>
</html>

<?php
$conn->close();
?>
