<?php
include("conexion.php");
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['Usuario'];
    $nuevaClave = $_POST['NuevaClave']; #no encriptar contraseña(símbolos)

    #ACTUALIZAR CONTRASEÑA EN BASE DE DATOS
    $stmt = $conn->prepare("UPDATE personas SET Clave = ? WHERE Usuario = ?");
    if ($stmt === false) {
        die("Error en prepare: " . $conn->error);
    }
    $stmt->bind_param("ss", $nuevaClave, $username);
    $stmt->execute();

    if ($stmt->affected_rows === 1) {
        #MOSTRAR QUE SE REDIRIGIRÁ EN 3 SEGUNDOS
        echo "<p>Contraseña actualizada con éxito. Redirigiendo en 3 segundos...</p>";
        header("Refresh: 3; url=index.php");
        exit(); #asegura que no se ejecute ningún código adicional después de la redirección
    } else {
        echo "Hubo un problema al actualizar la contraseña.";
    }
}
?>