<?php
include("conexion.php");
header('Content-Type: text/html; charset=utf-8');

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Verificar si se ha enviado una solicitud para eliminar un pedido
if (isset($_POST['eliminar_total'])) {
    $idTotal = $_POST['idTotal'];

    // Eliminar el pedido de la tabla Total
    $sql_delete_total = "DELETE FROM Total WHERE idTotal = ?";
    $stmt_delete = $conn->prepare($sql_delete_total);
    $stmt_delete->bind_param("i", $idTotal);

    if ($stmt_delete->execute()) {
        echo "Pedido eliminado correctamente.";
    } else {
        echo "Error al eliminar el pedido: " . $conn->error;
    }

    // Recargar la página después de la eliminación
    header("Location: total.php");
    exit();
}

// NAVBAR
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
echo "<a href='productos.php?section=productos'>Productos</a>";
echo "<a href='index.php' style='float:right;'>Cerrar sesión</a>";
echo "</div>";

echo "<h1>Total de Pedidos</h1>";

// Formulario de búsqueda de pedidos por cliente
echo "<div class='busqueda-cliente'>";
echo "<form method='GET' action='total.php'>";
echo "<label for='cliente'>Buscar pedidos por cliente:</label>";
echo "<input type='text' id='cliente' name='cliente' placeholder='Nombre del cliente'>";
echo "<button type='submit'>Buscar</button>";
echo "</form>";
echo "</div>";

// Verificar si se ingresó un nombre de cliente en el formulario de búsqueda
if (isset($_GET['cliente']) && !empty($_GET['cliente'])) {
    $cliente = "%" . $_GET['cliente'] . "%"; // Añadir comodines para búsqueda parcial
    $sql_total = "SELECT DATE(t.FechaPedido) AS Fecha, t.idTotal, t.FechaPedido, c.Nombre AS Cliente, c.DNI AS DNICliente, 
                  prod.Nombre AS Producto, t.Cantidad, prod.Precio, (t.Cantidad * prod.Precio) AS PrecioTotal
                  FROM Total t 
                  INNER JOIN Clientes c ON t.idClientes = c.idClientes
                  INNER JOIN Productos prod ON t.idProductos = prod.idProductos
                  WHERE c.Nombre LIKE ?
                  ORDER BY t.FechaPedido DESC";
    $stmt = $conn->prepare($sql_total);
    $stmt->bind_param("s", $cliente);
    $stmt->execute();
    $result_total = $stmt->get_result();
} else {
    // Mostrar todos los pedidos si no se busca un cliente específico
    $sql_total = "SELECT DATE(t.FechaPedido) AS Fecha, t.idTotal, t.FechaPedido, c.Nombre AS Cliente, c.DNI AS DNICliente, 
                  prod.Nombre AS Producto, t.Cantidad, prod.Precio, (t.Cantidad * prod.Precio) AS PrecioTotal
                  FROM Total t 
                  INNER JOIN Clientes c ON t.idClientes = c.idClientes
                  INNER JOIN Productos prod ON t.idProductos = prod.idProductos
                  ORDER BY t.FechaPedido DESC";
    $result_total = $conn->query($sql_total);
}

// Continuar mostrando la tabla de pedidos
if ($result_total) {
    $current_date = null;
    $daily_total = 0;  // Variable para almacenar el total de cada día
    if ($result_total->num_rows > 0) {
        while ($row = $result_total->fetch_assoc()) {
            // Detectar un cambio de día
            if ($current_date !== $row["Fecha"]) {
                if ($current_date !== null) {
                    // Mostrar el total del día anterior antes de cerrar la tabla
                    echo "<tr><td colspan='7' style='text-align:right; font-weight:bold;'>Total del Día:</td><td>$" . number_format($daily_total, 2) . "</td></tr>";
                    echo "</table><br>";
                }
                // Actualizar la fecha actual y abrir una nueva tabla
                $current_date = $row["Fecha"];
                echo "<h2>Pedidos del " . $current_date . "</h2>";
                echo "<table>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>DNI del Cliente</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Precio Total</th>
                        <th>Acciones</th>
                    </tr>";
                // Reiniciar el total diario para el nuevo día
                $daily_total = 0;
            }
            // Sumar el precio total de cada pedido al total del día
            $daily_total += $row["PrecioTotal"];
            
            // Mostrar fila del pedido
            echo "<tr>
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
        // Mostrar el total del último día
        echo "<tr><td colspan='7' style='text-align:right; font-weight:bold;'>Total del Día:</td><td>$" . number_format($daily_total, 2) . "</td></tr>";
        echo "</table><br>";
    } else {
        echo "No hay pedidos completados para este cliente.<br>";
    }
    $result_total->free();
} else {
    echo "Error en la consulta de total de pedidos: " . $conn->error . "<br>";
}

// Calcular y mostrar el total general de todos los pedidos
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
