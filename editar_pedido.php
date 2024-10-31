<?php
include("conexion.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pedido</title>
    <link rel="stylesheet" href="style/inicio.css"> <!-- Asegúrate de ajustar la ruta correcta a tu archivo CSS -->
    <link rel="stylesheet" href="style/table-styles.css"> <!-- Incluimos también los estilos de tabla si los necesitas -->
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
        # Verificar si se ha enviado el formulario de edición
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pedido_id'])) {
            $pedido_id = $_POST['pedido_id'];

            # Obtener datos del pedido a editar
            $sql_pedido = "SELECT p.idPedidos, c.Nombre AS Cliente, pr.Nombre AS Producto, p.Cantidad 
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
                ?>

                <!-- Formulario para editar el pedido -->
                <form method="POST" action="guardar_edicion_pedido.php" class="edit-form">
                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['idPedidos']; ?>">

                    <div class="form-group">
                        <label for="cliente">Cliente:</label>
                        <input type="text" id="cliente" name="cliente" value="<?php echo $pedido['Cliente']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="producto">Producto:</label>
                        <input type="text" id="producto" name="producto" value="<?php echo $pedido['Producto']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="cantidad">Cantidad:</label>
                        <input type="number" id="cantidad" name="cantidad" value="<?php echo $pedido['Cantidad']; ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>

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
