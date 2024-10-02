<?php
include("conexion.php");
header('Content-Type: text/html; charset=utf-8');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

#NAVBAR
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' rel='stylesheet'>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Total de Pedidos</title>";
echo "<link rel='stylesheet' href='style/inicio.css'>";
echo "<link rel='stylesheet' href='style/navbar.css'>";
echo "</head>";
echo "<body>";
echo "<div class='navbar'>";
echo "<a href='inicio.php?section=inicio'>Inicio</a>";
echo "<a href='pedidos.php?section=pedidos'>Agregar Pedidos</a>";
echo "<a href='total.php?section=total' class='active'>Total de Pedidos</a>";
echo "<a href='clientes.php?section=clientes'>Clientes</a>";
echo "<a href='index.php' style='float:right;'>Cerrar sesión</a>";
echo "</div>";
echo "<div class='content'>";
echo "<h1>Total de Pedidos</h1>";

#MOSTRAR TABLA TOTAL
$sql_total = "SELECT t.idTotal, t.FechaPedido, c.Nombre AS Cliente, prod.Nombre AS Producto, t.Cantidad 
FROM Total t 
INNER JOIN Clientes c ON t.idClientes = c.idClientes
INNER JOIN Productos prod ON t.idProductos = prod.idProductos
ORDER BY t.FechaPedido DESC";
$result_total = $conn->query($sql_total);

if ($result_total) {
    if ($result_total->num_rows > 0) {
        echo "<table border='1'>
            <tr>
                <th>ID Total</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Producto</th>
                <th>Cantidad</th>
            </tr>";
        while ($row = $result_total->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row["idTotal"] . "</td>
                    <td>" . $row["FechaPedido"] . "</td>
                    <td>" . $row["Cliente"] . "</td>
                    <td>" . $row["Producto"] . "</td>
                    <td>" . $row["Cantidad"] . "</td>
                </tr>";
        }
        echo "</table><br>";
    } else {
        echo "No hay pedidos completados.<br>";
    }
    $result_total->free();
} else {
    echo "Error en la consulta de total de pedidos: " . $conn->error . "<br>";
}

echo "</div>"; 
echo "</body>";
echo "</html>";
?>
