<?php
include("conexion.php");
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Proceso de eliminación de pedidos
if (isset($_POST['eliminar_total'])) {
    $idTotal = $_POST['idTotal'];
    $sql_delete_total = "DELETE FROM Total WHERE idTotal = ?";
    $stmt_delete = $conn->prepare($sql_delete_total);
    $stmt_delete->bind_param("i", $idTotal);
    if ($stmt_delete->execute()) {
        echo "<script>alert('Pedido eliminado correctamente.'); window.location.href = 'total.php';</script>";
    } else {
        echo "Error al eliminar el pedido: " . $conn->error;
    }
    exit();
}

// Formulario HTML para buscar clientes
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

// Formulario de búsqueda de clientes
echo "<form method='GET' action='total.php'>
        <label for='cliente'>Buscar pedidos por cliente:</label>
        <input type='text' name='cliente' id='cliente' placeholder='Nombre del Cliente'>
        <button type='submit'>Buscar</button>
      </form>";

// Obtener el nombre del cliente desde la solicitud
$nombre_cliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';

$sql_total = "SELECT DATE(t.FechaPedido) AS Fecha, MONTH(t.FechaPedido) AS Mes, YEAR(t.FechaPedido) AS Anio, 
              t.idTotal, t.FechaPedido, c.Nombre AS Cliente, c.DNI AS DNICliente, prod.Nombre AS Producto, 
              t.Cantidad, prod.Precio, (t.Cantidad * prod.Precio) AS PrecioTotal
              FROM Total t 
              INNER JOIN Clientes c ON t.idClientes = c.idClientes
              INNER JOIN Productos prod ON t.idProductos = prod.idProductos";

// Añadir filtro por nombre de cliente si se ingresó
if ($nombre_cliente != '') {
    $sql_total .= " WHERE c.Nombre LIKE ?";
}

// Ordenar por fecha de pedido
$sql_total .= " ORDER BY t.FechaPedido DESC";

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql_total);
if ($nombre_cliente != '') {
    $search_param = "%" . $nombre_cliente . "%";
    $stmt->bind_param("s", $search_param);
}
$stmt->execute();
$result_total = $stmt->get_result();

if ($result_total) {
    if ($result_total->num_rows > 0) {
        $current_date = null;
        $daily_total = 0;
        
        while ($row = $result_total->fetch_assoc()) {
            $row_date = $row["Fecha"];
            $row_month = $row["Mes"] . "-" . $row["Anio"];

            if ($current_date !== $row_date) {
                if ($current_date !== null) {
                    echo "<tr><td colspan='7' style='text-align:right; font-weight:bold;'>Total del Día:</td><td>$" . number_format($daily_total, 2) . "</td></tr>";
                    echo "</table><br>";
                }
                $current_date = $row_date;
                $daily_total = 0;

                echo "<h3>Pedidos del " . $current_date . "</h3>";
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
            }

            $daily_total += $row["PrecioTotal"];
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
                            <button type='submit' name='eliminar_total' onclick=\"return confirm('¿Estás seguro de que deseas eliminar este pedido?')\">Eliminar</button>
                        </form>
                    </td>
                </tr>";
        }

        echo "<tr><td colspan='7' style='text-align:right; font-weight:bold;'>Total del Día:</td><td>$" . number_format($daily_total, 2) . "</td></tr>";
        echo "</table><br>";
    } else {
        echo "No se encontraron pedidos para el cliente especificado.<br>";
    }
    $result_total->free();
} else {
    echo "Error en la consulta de total de pedidos: " . $conn->error . "<br>";
}

echo "</div>";
echo "</body>";
echo "</html>";
?>
