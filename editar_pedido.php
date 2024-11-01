<?php
include("conexion.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido</title>
    <link rel="stylesheet" href="style/inicio.css">
    <link rel="stylesheet" href="style/table-styles.css">
</head>
<body>

<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
        }
        .edit-form {
            display: flex;
            flex-direction: column;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #e4491a;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #bc3813;
        }
        .error {
            color: #e74c3c;
            text-align: center;
            margin-top: 20px;
        }
    </style>

    <div class="container">
        <h1>Editar Pedido</h1>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pedido_id'])) {
            $pedido_id = $_POST['pedido_id'];

            // Consultar datos actuales del pedido
            $sql_pedido = "SELECT p.idPedidos, c.Nombre AS Cliente, pr.idProductos, pr.Nombre AS Producto, pr.Precio, p.Cantidad 
                           FROM Pedidos p
                           JOIN Clientes c ON p.idClientes = c.idClientes
                           JOIN Productos pr ON p.idProductos = pr.idProductos
                           WHERE p.idPedidos = ?";
            $stmt = $conn->prepare($sql_pedido);
            $stmt->bind_param("i", $pedido_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $pedido = $result->fetch_assoc();

                // Obtener todos los productos para la lista desplegable
                $productos_result = $conn->query("SELECT idProductos, Nombre, Precio FROM Productos");
                ?>

                <!-- Formulario de edición -->
                <form method="POST" action="guardar_edicion_pedido.php" class="edit-form">
                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['idPedidos']; ?>">

                    <div class="form-group">
                        <label for="cliente">Cliente:</label>
                        <input type="text" id="cliente" name="cliente" value="<?php echo $pedido['Cliente']; ?>" required readonly>
                    </div>

                    <div class="form-group">
                        <label for="producto">Producto:</label>
                        <select id="producto" name="producto_id" required onchange="actualizarPrecio()">
                            <option value="">Seleccione un producto</option>
                            <?php while ($producto = $productos_result->fetch_assoc()) { ?>
                                <option value="<?php echo $producto['idProductos']; ?>" 
                                    data-precio="<?php echo $producto['Precio']; ?>" 
                                    <?php if ($producto['idProductos'] == $pedido['idProductos']) echo 'selected'; ?>>
                                    <?php echo $producto['Nombre']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cantidad">Cantidad:</label>
                        <input type="number" id="cantidad" name="cantidad" value="<?php echo $pedido['Cantidad']; ?>" required oninput="actualizarPrecio()">
                    </div>

                    <div class="form-group">
                        <label for="total">Total:</label>
                        <input type="text" id="total" name="total" value="<?php echo $pedido['Cantidad'] * $pedido['Precio']; ?>" readonly>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="inicio.php" class="btn btn-secondary" style="background-color: #888; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center;">Cancelar</a>

                </form>

                <script>
                    function actualizarPrecio() {
                        let productoSelect = document.getElementById("producto");
                        let precio = parseFloat(productoSelect.options[productoSelect.selectedIndex].getAttribute("data-precio"));
                        let cantidad = parseFloat(document.getElementById("cantidad").value);
                        document.getElementById("total").value = (precio * cantidad).toFixed(2);
                    }
                </script>

                <?php
            } else {
                echo "<p class='error'>No se encontró el pedido.</p>";
            }

            $stmt->close();
        } else {
            echo "<p class='error'>No se recibió un ID de pedido válido.</p>";
        }
        $conn->close();
        ?>
    </div>
</body>
</html>
