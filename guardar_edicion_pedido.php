<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pedido_id'])) {
    $pedido_id = $_POST['pedido_id'];
    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'];

    // Actualizar pedido con el nuevo id de producto y cantidad
    $sql_update = "UPDATE Pedidos SET idProductos = ?, Cantidad = ? WHERE idPedidos = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("iii", $producto_id, $cantidad, $pedido_id);

    if ($stmt->execute()) {
        // Redirecciona de vuelta a la página de pedidos o a otra página
        header("Location: inicio.php");
        exit();
    } else {
        echo "Error al actualizar el pedido.";
    }

    $stmt->close();
}
$conn->close();
?>
