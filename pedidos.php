<?php
include("conexion.php");

#verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

#verificar si se envió un pedido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['productos']) && isset($_POST['nombreCliente'])) {
    $productos = $_POST['productos'];
    $cantidades = $_POST['cantidades'];
    $nombreCliente = $_POST['nombreCliente'];

    # Dividir nombre y DNI
    preg_match("/^(.*)\((\d+)\)$/", $nombreCliente, $matches);
    $nombreCliente = trim($matches[1]);
    $dniCliente = trim($matches[2]);

    # Verificar si el cliente existe
    $sql_cliente = "SELECT idClientes FROM Clientes WHERE Nombre = ? AND DNI = ?";
    $stmt_cliente = $conn->prepare($sql_cliente);
    $stmt_cliente->bind_param("ss", $nombreCliente, $dniCliente);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();

    if ($result_cliente->num_rows > 0) {
        $row_cliente = $result_cliente->fetch_assoc();
        $idCliente = $row_cliente['idClientes'];
    } else {
        # Agregar nuevo cliente si no existe
        $sql_insert_cliente = "INSERT INTO Clientes (Nombre, DNI) VALUES (?, ?)";
        $stmt_insert_cliente = $conn->prepare($sql_insert_cliente);
        $stmt_insert_cliente->bind_param("ss", $nombreCliente, $dniCliente);
        if ($stmt_insert_cliente->execute()) {
            $idCliente = $stmt_insert_cliente->insert_id;
        } else {
            die("Error al agregar el cliente: " . $conn->error);
        }
    }

    # Insertar pedidos en la base de datos
    foreach ($productos as $idProducto) {
        $cantidad = intval($cantidades[$idProducto]);
        if ($cantidad < 1) {
            $cantidad = 1;  # Asegurar que la cantidad mínima es 1
        }
        $sql_insert_pedido = "INSERT INTO Pedidos (idProductos, idClientes, Cantidad) VALUES (?, ?, ?)";
        $stmt_insert_pedido = $conn->prepare($sql_insert_pedido);
        $stmt_insert_pedido->bind_param("iii", $idProducto, $idCliente, $cantidad);

        if (!$stmt_insert_pedido->execute()) {
            die("Error al agregar el pedido: " . $conn->error);
        }
    }

    # Redirigir al inicio después de completar los pedidos
    header("Location: inicio.php");
    exit();
}

# Consulta para obtener productos y clientes
$sql_productos = "SELECT * FROM Productos";
$result_productos = $conn->query($sql_productos);

$sql_clientes = "SELECT Nombre, DNI FROM Clientes";
$result_clientes = $conn->query($sql_clientes);

$productos = [];
if ($result_productos->num_rows > 0) {
    while ($row = $result_productos->fetch_assoc()) {
        # Agrupar por categoría
        $categoria = explode(' ', $row["Nombre"])[0];
        $productos[$categoria][] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' rel='stylesheet'>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Pedido</title>
    <link rel="stylesheet" href="style/pedidos.css">
    <link rel='stylesheet' href='style/navbar.css'>
    <style>
        .suggestion-item:hover {
            background-color: #f2f2f2;
            cursor: pointer;
        }
        .suggestion-item {
            padding: 5px;
        }
    </style>
</head>
<body>

<div class='navbar'>
    <a href='inicio.php'>Inicio</a>
    <a href='pedidos.php' class='active'>Agregar Pedidos</a>
    <a href='total.php'>Total de Pedidos</a>
    <a href='clientes.php?section=clientes'>Clientes</a>
    <a href='productos.php?section=productos'>Productos</a>
    <a href='index.php' style='float:right;'>Cerrar sesión</a>
</div>

<h2 id="realizarpedido">Realizar Pedido</h2>

<form method="POST" action="pedidos.php">
    <div class="form-group">
        <label for="nombreCliente">Nombre del Cliente:</label>
        <input type="text" id="nombreCliente" name="nombreCliente" oninput="mostrarSugerencias(this.value)" required>
        <div id="sugerenciasClientes"></div>
    </div>

    <div class="form-group">
        <h3>Seleccionar Producto y Cantidad</h3>

        <?php foreach ($productos as $categoria => $items): ?>
            <div class="categoria-producto">
                <img src="images/<?php echo strtolower($categoria); ?>.png" alt="<?php echo $categoria; ?>" class="categoria-imagen">
                
                <div class="categoria-items">
                    <?php foreach ($items as $producto): ?>
                        <label>
                            <input type="checkbox" name="productos[]" value="<?php echo $producto['idProductos']; ?>">
                            <?php echo $producto['Nombre']; ?>
                        </label>
                        <label for="cantidad_<?php echo $producto['idProductos']; ?>">Cantidad:</label>
                        <input type="number" id="cantidad_<?php echo $producto['idProductos']; ?>" 
                                name="cantidades[<?php echo $producto['idProductos']; ?>]" value="1" min="1">
                        <br>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <input type="submit" value="Completar Pedido">
</form>

<script>
    function mostrarSugerencias(valor) {
        let sugerenciasDiv = document.getElementById("sugerenciasClientes");
        sugerenciasDiv.innerHTML = "";  // Limpiar sugerencias previas

        if (valor.length === 0) {
            sugerenciasDiv.style.display = "none";
            return;
        }

        fetch('buscar_clientes.php?q=' + valor)
        .then(response => response.json())
        .then(data => {
            sugerenciasDiv.style.display = "block";
            data.forEach(cliente => {
                let opcion = document.createElement("div");
                opcion.classList.add("suggestion-item");
                opcion.textContent = cliente.Nombre + " (" + cliente.DNI + ")";
                opcion.onclick = function() {
                    document.getElementById("nombreCliente").value = cliente.Nombre + " (" + cliente.DNI + ")";
                    sugerenciasDiv.style.display = "none";
                };
                sugerenciasDiv.appendChild(opcion);
            });
        });
    }
</script>

</body>
</html>
