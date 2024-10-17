<?php
include("conexion.php");
header('Content-Type: text/html; charset=utf-8');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

# Verificar si se ha enviado una solicitud para eliminar un pedido
if (isset($_POST['eliminar_total'])) {
    $idTotal = $_POST['idTotal'];

    # Eliminar el pedido de la tabla Total
    $sql_delete_total = "DELETE FROM Total WHERE idTotal = ?";
    $stmt_delete = $conn->prepare($sql_delete_total);
    $stmt_delete->bind_param("i", $idTotal);

    if ($stmt_delete->execute()) {
        echo "Pedido eliminado correctamente.";
    } else {
        echo "Error al eliminar el pedido: " . $conn->error;
    }

    # Recargar la página después de la eliminación
    header("Location: total.php");
    exit();
}

# NAVBAR
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
echo "<a href='productos.php?section=productos'>Productos<a>";
echo "<a href='index.php' style='float:right;'>Cerrar sesión</a>";
echo "</div>";
echo "<div class='content'>";
echo "<h1>Total de Pedidos</h1>";

# Mostrar tabla total con precios calculados
$sql_total = "SELECT t.idTotal, t.FechaPedido, c.Nombre AS Cliente, c.DNI AS DNICliente, 
              prod.Nombre AS Producto, t.Cantidad, prod.Precio, (t.Cantidad * prod.Precio) AS PrecioTotal
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
                <th>DNI del Cliente</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Precio Total</th>
                <th>Acciones</th> <!-- Nueva columna para el botón de eliminar -->
            </tr>";
        while ($row = $result_total->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row["idTotal"] . "</td>
                    <td>" . $row["FechaPedido"] . "</td>
                    <td>" . $row["Cliente"] . "</td>
                    <td>" . $row["DNICliente"] . "</td>
                    <td>" . $row["Producto"] . "</td>
                    <td>" . $row["Cantidad"] . "</td>
                    <td>" . $row["Precio"] . "</td>
                    <td>" . $row["PrecioTotal"] . "</td>
                    <td>
                        <form method='POST' action='total.php' style='display:inline-block;'>
                            <input type='hidden' name='idTotal' value='" . $row["idTotal"] . "'>
                            <button type='submit' name='eliminar_total'>Eliminar</button>
                        </form>
                    </td>
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

# Calcular y mostrar el total general de todos los pedidos
$sql_suma_total = "SELECT SUM(t.Cantidad * prod.Precio) AS SumaTotal
                    FROM Total t
                    INNER JOIN Productos prod ON t.idProductos = prod.idProductos";
$result_suma_total = $conn->query($sql_suma_total);

if ($result_suma_total) {
    $row_suma_total = $result_suma_total->fetch_assoc();
    $total_general = $row_suma_total['SumaTotal'];
    echo "<h2>Total de Todos los Pedidos: $" . number_format($total_general, 2) . "</h2>";
    $result_suma_total->free();
} else {
    echo "Error al calcular el total general: " . $conn->error . "<br>";
}

echo "</div>"; 
echo "</body>";
echo "</html>";
?>
