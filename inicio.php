<?php
include("conexion.php");
session_start();

# INICIO DE SESIÓN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    # Verificación del formato del nombre de usuario
    if (!preg_match("/^[a-zA-Z]+$/", $username)) {
        echo "El nombre de usuario solo debe contener letras.";
        exit();
    }

    # Verificar si el usuario existe en la base de datos
    $stmt = $conn->prepare("SELECT * FROM personas WHERE Usuario = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "El usuario no existe en la base de datos.";
        exit();
    }

    # Verificar si el usuario y la contraseña coinciden
    $stmt = $conn->prepare("SELECT * FROM personas WHERE Usuario = ? AND Clave = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['Usuario'];
        header("Location: inicio.php");
        exit();
    } else {
        echo "Usuario o clave incorrectos.";
        exit();
    }

    $stmt->close();
} else {
    if (!isset($_SESSION['username'])) {
        header("Location: index.php");
        exit();
    }
}

# Navbar y bienvenida
echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' rel='stylesheet'>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Bienvenido</title>";
echo "<link rel='stylesheet' href='style/inicio.css'>";
echo "<link rel='stylesheet' href='style/navbar.css'>"; 
echo "<link rel='stylesheet' href='style/ayuda.css'>";
echo "</head>";
echo "<body>";

# Navbar
echo "<div class='navbar'>";
echo "<a href='inicio.php?section=inicio' class='active'>Inicio</a>";
echo "<a href='pedidos.php?section=pedidos'>Agregar Pedidos</a>";
echo "<a href='total.php?section=total'>Total de Pedidos</a>";
echo "<a href='clientes.php?section=clientes'>Clientes</a>";
echo "<a href='productos.php?section=productos'>Productos</a>";
echo "<a href='index.php' style='float:right;'>Cerrar sesión</a>";
echo "</div>";

echo "<div class='content'>";
echo "<h1>¡Bienvenido, " . $_SESSION['username'] . "!🍕</h1>";  

# Consultar pedidos y sumar precios por cliente
$sql_pedidos = "SELECT c.Nombre AS Cliente, c.Apellido, pr.Nombre AS Producto, p.Cantidad, pr.Precio, p.FechaPedido, p.idPedidos, 
                       (p.Cantidad * pr.Precio) AS PrecioTotalProducto
                FROM Pedidos p
                JOIN Clientes c ON p.idClientes = c.idClientes
                JOIN Productos pr ON p.idProductos = pr.idProductos
                ORDER BY p.FechaPedido DESC";
$result_pedidos = $conn->query($sql_pedidos);

$pedidosPorCliente = [];
$totalPorCliente = [];
if ($result_pedidos->num_rows > 0) {
    while ($row = $result_pedidos->fetch_assoc()) {
        $cliente = $row['Cliente'] . ' ' . $row['Apellido'];
        $pedidosPorCliente[$cliente][] = $row;

        # Acumular el precio total para cada cliente
        if (!isset($totalPorCliente[$cliente])) {
            $totalPorCliente[$cliente] = 0;
        }
        $totalPorCliente[$cliente] += $row['PrecioTotalProducto'];
    }
}

echo "<h2>Pedidos agrupados por clientes</h2>";

if (count($pedidosPorCliente) > 0) {
    echo "<table class='tabla-pedidos' border='0'>";
    foreach ($pedidosPorCliente as $cliente => $pedidos) {
        echo "<tr><th colspan='5'>Cliente: $cliente | Total Acumulado: $" . number_format($totalPorCliente[$cliente], 2) . "</th></tr>";
        echo "<tr><th>Producto</th><th>Cantidad</th><th>Precio Total</th><th>Fecha de Pedido</th><th>Acciones</th></tr>";
        foreach ($pedidos as $pedido) {
            echo "<tr>";
            echo "<td>" . $pedido['Producto'] . "</td>";
            echo "<td>" . $pedido['Cantidad'] . "</td>";
            echo "<td>" . number_format($pedido['PrecioTotalProducto'], 2) . "</td>";
            echo "<td>" . $pedido['FechaPedido'] . "</td>";
            echo "<td>";
            echo "<form method='POST' action='completar.php' style='display:inline-block;'>";
            echo "<input type='hidden' name='pedido_id' value='" . $pedido["idPedidos"] . "'>";
            echo "<button type='submit' name='transferir_pedido'>Completar</button>";
            echo "</form>";
            echo "<form method='POST' action='editar_pedido.php' style='display:inline-block;'>";
            echo "<input type='hidden' name='pedido_id' value='" . $pedido["idPedidos"] . "'>";
            echo "<button type='submit' name='editar_pedido'>Editar</button>";
            echo "</form>";
            echo "<form method='POST' action='eliminar_pedido.php' style='display:inline-block;'>";
            echo "<input type='hidden' name='pedido_id' value='" . $pedido["idPedidos"] . "'>";
            echo "<button type='submit' name='eliminar_pedido'>Eliminar</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} else {
    echo "No hay pedidos disponibles.";
}

echo "<button class='faq-button'>
    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 320 512'>
        <path d='M80 160c0-35.3 28.7-64 64-64h32c35.3 0 64 28.7 64 64v3.6c0 21.8-11.1 42.1-29.4 53.8l-42.2 27.1c-25.2 16.2-40.4 44.1-40.4 74V320c0 17.7 14.3 32 32 32s32-14.3 32-32v-1.4c0-8.2 4.2-15.8 11-20.2l42.2-27.1c36.6-23.6 58.8-64.1 58.8-107.7V160c0-70.7-57.3-128-128-128H144C73.3 32 16 89.3 16 160c0 17.7 14.3 32 32 32s32-14.3 32-32zm80 320a40 40 0 1 0 0-80 40 40 0 1 0 0 80z'></path>
    </svg>
    <span class='tooltip'>Contactanos por ayuda al: (número)</span>
</button>";

echo "</div>"; 
echo "</body>";
echo "</html>";
?>
