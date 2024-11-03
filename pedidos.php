<?php
include("conexion.php");

# Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

# Verificar si se envió un pedido
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['productos']) && isset($_POST['nombreCliente']) && isset($_POST['apellidoCliente']) && isset($_POST['telefonoCliente'])) {
    $productos = $_POST['productos'];
    $cantidades = $_POST['cantidades'];
    $nombreCliente = $_POST['nombreCliente'];
    $apellidoCliente = $_POST['apellidoCliente'];
    $telefonoCliente = '+' . $_POST['telefonoCliente'];

    // Consulta para verificar si el cliente ya existe por nombre y apellido
    $sql_cliente = "SELECT idClientes FROM Clientes WHERE Nombre = ? AND Apellido = ?";
    $stmt_cliente = $conn->prepare($sql_cliente);
    $stmt_cliente->bind_param("ss", $nombreCliente, $apellidoCliente);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();

    if ($result_cliente->num_rows > 0) {
        $row_cliente = $result_cliente->fetch_assoc();
        $idCliente = $row_cliente['idClientes'];
    } else {
        // Inserta un nuevo cliente si no existe
        $sql_insert_cliente = "INSERT INTO Clientes (Nombre, Apellido, Telefono) VALUES (?, ?, ?)";
        $stmt_insert_cliente = $conn->prepare($sql_insert_cliente);
        $stmt_insert_cliente->bind_param("sss", $nombreCliente, $apellidoCliente, $telefonoCliente);
        if ($stmt_insert_cliente->execute()) {
            $idCliente = $stmt_insert_cliente->insert_id;
        } else {
            die("Error al agregar el cliente: " . $conn->error);
        }
    }

    // INSERTAR PEDIDOS EN LA BASE DE DATOS
    foreach ($productos as $idProducto) {
        $cantidad = intval($cantidades[$idProducto]);
        if ($cantidad < 1) {
            $cantidad = 1;
        }
        $sql_insert_pedido = "INSERT INTO Pedidos (idProductos, idClientes, Cantidad) VALUES (?, ?, ?)";
        $stmt_insert_pedido = $conn->prepare($sql_insert_pedido);
        $stmt_insert_pedido->bind_param("iii", $idProducto, $idCliente, $cantidad);

        if (!$stmt_insert_pedido->execute()) {
            die("Error al agregar el pedido: " . $conn->error);
        }
    }

    header("Location: inicio.php");
    exit();
}


# Consulta para obtener los productos
$sql_productos = "SELECT * FROM Productos";
$result_productos = $conn->query($sql_productos);
$sql_clientes = "SELECT Nombre, Apellido, Telefono FROM Clientes";
$result_clientes = $conn->query($sql_clientes);

$productos = [];
if ($result_productos->num_rows > 0) {
    while ($row = $result_productos->fetch_assoc()) {
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
</head>
<body>

<h2 id="realizarpedido">Realizar Pedido</h2>

<form method="POST" action="pedidos.php">
    <div class="form-group">
        <label for="nombreCliente">Nombre del Cliente:</label>
        <input type="text" id="nombreCliente" name="nombreCliente" required pattern="[A-Za-z\s]+" title="Solo letras y espacios permitidos" list="clientes_sugeridos">
    </div>

    <div class="form-group">
        <label for="apellidoCliente">Apellido del Cliente:</label>
        <input type="text" id="apellidoCliente" name="apellidoCliente"  pattern="[A-Za-z\s]+" title="Solo letras y espacios permitidos">
    </div>

    <div class="form-group">
        <label for="telefonoCliente">Teléfono de Cliente:</label>
        <input type="text" id="telefonoCliente" name="telefonoCliente"  pattern="[0-9]+" title="Solo números permitidos">
    </div>

    <datalist id="clientes_sugeridos">
        <?php while ($row = $result_clientes->fetch_assoc()): ?>
            <option value="<?php echo $row['Nombre']; ?>">
                <?php echo $row['Nombre'] . " " . $row['Apellido'] . " - " . $row['Telefono']; ?>
            </option>
        <?php endwhile; ?>
    </datalist>

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

<div class='navbar'>
    <a href='inicio.php'>Inicio</a>
    <a href='pedidos.php' class='active'>Agregar Pedidos</a>
    <a href='total.php'>Total de Pedidos</a>
    <a href='clientes.php?section=clientes'>Clientes</a>
    <a href='productos.php?section=productos'>Productos</a>
    <a href='logout.php' style='float:right;'>Cerrar sesión</a>
</div>

</body>
</html>
