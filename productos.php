<?php
include("conexion.php");
header('Content-Type: text/html; charset=utf-8');

#INICIO DE SESIÓN
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!preg_match("/^[a-zA-Z]+$/", $username)) {
        echo "El nombre de usuario solo debe contener letras.";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM personas WHERE Usuario = ?");
    if ($stmt === false) {
        die("Error en prepare: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "El usuario no existe en la base de datos.";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM personas WHERE Usuario = ? AND Clave = ?");
    if ($stmt === false) {
        die("Error en prepare: " . $conn->error);
    }
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

#NAVBAR
$section = isset($_GET['section']) ? $_GET['section'] : 'productos';

    echo "<!DOCTYPE html>";
    echo "<html lang='en'>";
    echo "<head>";
    echo "<link href='https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' rel='stylesheet'>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Bienvenido</title>";
    echo "<link rel='stylesheet' href='style/inicio.css'>";
    echo "<link rel='stylesheet' href='style/navbar.css'>";
    echo "</head>";
    echo "<body>";

    // Barra de navegación
    echo "<div class='navbar'>";
    echo "<a href='inicio.php?section=inicio'>Inicio</a>";
    echo "<a href='pedidos.php?section=pedidos'>Agregar Pedidos</a>";
    echo "<a href='total.php?section=total'>Total de Pedidos</a>";
    echo "<a href='clientes.php?section=clientes'>Clientes</a>";
    echo "<a href='productos.php?section=productos' class='active'>Productos</a>";
    echo "<a href='index.php' style='float:right;'>Cerrar sesión</a>";
    echo "</div>";
    
    echo "<div class='content'>";


    // Sección de Productos
    if ($section == 'productos') {
        // Mostrar productos de la tabla
        $sql_productos = "SELECT idProductos, Nombre, Precio, Descripcion FROM productos";
        $result_productos = $conn->query($sql_productos);

        if ($result_productos->num_rows > 0) {
            echo "<h2>Tabla de Productos</h2>";
            echo "<table>";
            echo "<tr><th>Producto</th><th>Precio</th><th>Descripcion</th><th>Acciones</th></tr>";
            while($row = $result_productos->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["Nombre"] . "</td>";
                echo "<td>" . $row["Precio"] . "</td>";
                echo "<td>" . $row["Descripcion"] . "</td>";
                echo "<td>";
                // Botón para editar el producto
                echo "<a href='productos.php?section=editar&producto_id=" . $row["idProductos"] . "'><button type='submit' name='editar_producto'>Editar</button></a>";
                // Botón para eliminar el producto
                echo "<form method='POST' style='display:inline-block;'>";
                echo "<input type='hidden' name='producto_id' value='" . $row["idProductos"] . "'>";
                echo "<button type='submit' name='eliminar_producto'>Eliminar</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No hay productos disponibles.";
        }
    }

    // Acción de eliminar producto
    if (isset($_POST['eliminar_producto'])) {
        $producto_id = $_POST['producto_id'];

        // Eliminar de la tabla de productos
        $sql_delete = "DELETE FROM productos WHERE idProductos = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $producto_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        // Recargar la página
        header("Location: productos.php?section=productos");
        exit();
    }

    // Acción de editar producto
    if ($section == 'editar' && isset($_GET['producto_id'])) {
        $producto_id = $_GET['producto_id'];

        // Obtener los datos actuales del producto
        $sql_editar = "SELECT idProductos, Nombre, Precio, Descripcion FROM productos WHERE idProductos = ?";
        $stmt_editar = $conn->prepare($sql_editar);
        $stmt_editar->bind_param("i", $producto_id);
        $stmt_editar->execute();
        $result_editar = $stmt_editar->get_result();

        if ($result_editar->num_rows == 1) {
            $row = $result_editar->fetch_assoc();
        
            // Mostrar formulario para editar producto
            echo "<div class='edit-product-form'>";
            echo "<h2>Editar Producto</h2>";
            echo "<form method='POST'>";
            
            echo "<div class='form-group'>";
            echo "<label for='Nombre'>Nombre del Producto</label>";
            echo "<input type='text' id='Nombre' name='Nombre' value='" . htmlspecialchars($row['Nombre']) . "' required>";
            echo "</div>";
            
            echo "<div class='form-group'>";
            echo "<label for='Precio'>Precio</label>";
            echo "<input type='number' step='0.01' id='Precio' name='Precio' value='" . htmlspecialchars($row['Precio']) . "' required>";
            echo "</div>";
            
            echo "<div class='form-group'>";
            echo "<label for='Descripcion'>Descripción</label>";
            echo "<textarea id='Descripcion' name='Descripcion' required>" . htmlspecialchars($row['Descripcion']) . "</textarea>";
            echo "</div>";
            
            echo "<input type='hidden' name='producto_id' value='" . $row['idProductos'] . "'>";
            
            echo "<div class='button-container'>";
            echo "<button type='submit' name='actualizar_producto'>Actualizar Producto</button>";
            echo "<button type='button' onclick='window.location.href=\"productos.php?section=productos\"'>Cancelar</button>";
            echo "</div>";
            
            echo "</form>";
            echo "</div>";
        } else {
            echo "<p class='error-message'>Producto no encontrado.</p>";
        }
        $stmt_editar->close();
    }

    // Acción para actualizar producto
    if (isset($_POST['actualizar_producto'])) {
        $producto_id = $_POST['producto_id'];
        $nombre = $_POST['Nombre'];
        $precio = $_POST['Precio'];
        $descripcion = $_POST['Descripcion'];

        // Actualizar el producto en la base de datos
        $sql_actualizar = "UPDATE productos SET Nombre = ?, Precio = ?, Descripcion = ? WHERE idProductos = ?";
        $stmt_actualizar = $conn->prepare($sql_actualizar);
        $stmt_actualizar->bind_param("sdsi", $nombre, $precio, $descripcion, $producto_id);
        $stmt_actualizar->execute();
        $stmt_actualizar->close();

        // Recargar la página después de actualizar
        header("Location: productos.php?section=productos");
        exit();
    }

    echo "</div>"; // Cierre del div content
    echo "</body>";
    echo "</html>";
}
?>
