<?php
include("conexion.php");

# Verificar si se ha enviado el formulario de eliminación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pedido_id'])) {
    $pedido_id = $_POST['pedido_id'];

    # Eliminar el pedido de la tabla pedidos
    $sql_delete = "DELETE FROM Pedidos WHERE idPedidos = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $pedido_id);

    if ($stmt->execute()) {
        header("Location: inicio.php");  # Redirigir a la página principal
        exit();
    } else {
        echo "Error al eliminar el pedido: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
