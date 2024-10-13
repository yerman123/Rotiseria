<?php
include("conexion.php");

# Verificar si se ha enviado el formulario de guardado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pedido_id'])) {
    $pedido_id = $_POST['pedido_id'];
    $cliente = $_POST['cliente'];
    $producto = $_POST['producto'];
    $cantidad = $_POST['cantidad'];

    # Actualizar el pedido en la base de datos
    $sql_update = "UPDATE Pedidos p
    JOIN Clientes c ON p.idClientes = c.idClientes
    JOIN Productos pr ON p.idProductos = pr.idProductos
    SET c.Nombre = ?, pr.Nombre = ?, p.Cantidad = ?
    WHERE p.idPedidos = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssii", $cliente, $producto, $cantidad, $pedido_id);

    if ($stmt->execute()) {
        header("Location: inicio.php");  # Redirigir a la página principal
        exit();
    } else {
        echo "Error al actualizar el pedido: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
