<?php
include("conexion.php");
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['transferir_pedido'])) {
    $pedido_id = $_POST['pedido_id'];

    // Iniciar transacción
    $conn->autocommit(FALSE);

    try {
        // Obtener los datos del pedido
        $sql_pedido = "SELECT idClientes, idProductos, Cantidad, FechaPedido FROM Pedidos WHERE idPedidos = ?";
        $stmt = $conn->prepare($sql_pedido);
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedido = $result->fetch_assoc();

        // Insertar en la tabla Total
        $sql_insertar = "INSERT INTO Total (idClientes, idProductos, Cantidad, FechaPedido) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_insertar);
        $stmt->bind_param("iiis", $pedido['idClientes'], $pedido['idProductos'], $pedido['Cantidad'], $pedido['FechaPedido']);
        $stmt->execute();

        // Eliminar de la tabla Pedidos
        $sql_eliminar = "DELETE FROM Pedidos WHERE idPedidos = ?";
        $stmt = $conn->prepare($sql_eliminar);
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();

        // Confirmar transacción
        $conn->commit();
        $conn->autocommit(TRUE);

        header("Location: inicio.php");
        exit();
    } catch (Exception $e) {
        // Si hay un error, deshacer la transacción
        $conn->rollback();
        $conn->autocommit(TRUE);
        echo "Error: " . $e->getMessage();
    }
}

// Si no se envió el formulario, redirigir a inicio.php
header("Location: inicio.php");
exit();
?>