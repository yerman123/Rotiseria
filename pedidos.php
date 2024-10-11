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

    #colocar cliente
    $sql_cliente = "SELECT idClientes FROM Clientes WHERE Nombre = ?";
    $stmt_cliente = $conn->prepare($sql_cliente);
    $stmt_cliente->bind_param("s", $nombreCliente);
    $stmt_cliente->execute();
    $result_cliente = $stmt_cliente->get_result();

    if ($result_cliente->num_rows > 0) {
        $row_cliente = $result_cliente->fetch_assoc();
        $idCliente = $row_cliente['idClientes'];
    } else {
        #colocar nuevo cliente si no existe
        $sql_insert_cliente = "INSERT INTO Clientes (Nombre) VALUES (?)";
        $stmt_insert_cliente = $conn->prepare($sql_insert_cliente);
        $stmt_insert_cliente->bind_param("s", $nombreCliente);
        if ($stmt_insert_cliente->execute()) {
            $idCliente = $stmt_insert_cliente->insert_id;
        } else {
            echo "Error al agregar el cliente: " . $conn->error;
            exit();
        }
    }

    #INSERTAR PEDIDOS EN LA BASE DE DATOS
    foreach ($productos as $index => $idProducto) {
        $cantidad = $cantidades[$index]; # Obtener la cantidad correspondiente
        $sql_insert_pedido = "INSERT INTO Pedidos (idProductos, idClientes, Cantidad) VALUES (?, ?, ?)";
        $stmt_insert_pedido = $conn->prepare($sql_insert_pedido);
        $stmt_insert_pedido->bind_param("iii", $idProducto, $idCliente, $cantidad);

        if (!$stmt_insert_pedido->execute()) {
            echo "Error al agregar el pedido: " . $conn->error;
        }
    }

    #REDIRIGIR AL INICIO LUEGO DE QUE SE COMPLETEN LOS PEDIDOS
    header("Location: inicio.php");
    exit();
}

#CONSULTA PARA OBTENER LOS PRODUCTOS
$sql_productos = "SELECT * FROM Productos";
$result_productos = $conn->query($sql_productos);

$productos = [];
if ($result_productos->num_rows > 0) {
    while($row = $result_productos->fetch_assoc()) {
        #AGRUPAR POR CATEGORÍA (asumiendo que la categoría es el primer término del nombre)
        $categoria = explode(' ', $row["Nombre"])[0]; #por ejemplo: 'pizza' de 'pizza mozzarela'
        $productos[$categoria][] = $row; #agrupar productos por categoría
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

<div class='navbar'>
    <a href='inicio.php'>Inicio</a>
    <a href='pedidos.php' class='active'>Agregar Pedidos</a>
    <a href='total.php'>Total de Pedidos</a>
    <a href='clientes.php'>Clientes</a>
    <a href='productos.php'>Productos<a>
    <a href='index.php' style='float:right;'>Cerrar sesión</a>
</div>

<h2 id="realizarpedido">Realizar Pedido</h2>

<form method="POST" action="pedidos.php">
    <div class="form-group">
        <label for="nombreCliente">Nombre del Cliente:</label>
        <input type="text" id="nombreCliente" name="nombreCliente" required>
    </div>

    <div class="form-group">
        <h3>Seleccionar Producto y Cantidad</h3>

        <?php foreach ($productos as $categoria => $items): ?>
    <div class="categoria-producto">
        <!-- Mostrar la imagen correspondiente a la categoría -->
        <img src="images/<?php echo strtolower($categoria); ?>.png" alt="<?php echo $categoria; ?>" class="categoria-imagen">
        
        <!-- Aquí agregamos un div para agrupar el texto y las casillas de verificación debajo de la imagen -->
        <div class="categoria-items">
            <?php foreach ($items as $producto): ?>
                <label>
                    <input type="checkbox" name="productos[]" value="<?php echo $producto['idProductos']; ?>">
                    <?php echo $producto['Nombre']; ?>
                </label>
                <label for="cantidad_<?php echo $producto['idProductos']; ?>">Cantidad:</label>
                <input type="number" id="cantidad_<?php echo $producto['idProductos']; ?>" name="cantidades[]" value="1" min="1">
                <br>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

    </div>

    <input type="submit" value="Completar Pedido">
</form>

</body>
</html>
