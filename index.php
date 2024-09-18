<?php
include("conexion.php");
header('Content-Type: text/html; charset=utf-8');

session_start();

$error = ''; // Variable para almacenar mensajes de error

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!preg_match("/^[a-zA-Z]+$/", $username)) {
        $error = "El nombre de usuario solo debe contener letras.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM personas WHERE Usuario = ?");
        if ($stmt === false) {
            die("Error en prepare: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = "El usuario no existe en la base de datos.";
        } else {
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
                $error = "Usuario o clave incorrectos.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" type="text/css" href="style/style.css">
  <link rel="icon" href="https://i.imgur.com/sMYm4bC.png">
  <title>Buitre Delivery</title>
  <style>
    body {
        font-family: 'Roboto', sans-serif;
    }
    .form_main {
        /* Agrega tus estilos para el formulario aquí */
    }
    .error-message {
        color: red;
        font-size: 0.9em;
        margin-top: 10px;
    }
  </style>
</head>
<body>
  <form class="form_main" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
    <img src="https://i.imgur.com/u4NxpF4.png" alt="El Buitre" class="form_image">
    <p class="heading">Iniciar sesión</p>
    <div class="inputContainer">
        <input placeholder="Usuario" id="username" name="username" class="inputField" type="text" required>
    </div>
    
    <div class="inputContainer">
        <input placeholder="Clave" id="password" name="password" class="inputField" type="password" required>
    </div>

    <input type="hidden" name="login" value="1">
    <button id="button" type="submit">Ingresar</button>
    <div class="signupContainer">
        <a href="contra.php">¿Olvidaste tu contraseña?</a>
    </div>
    <?php if ($error): ?>
      <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>
  </form>
</body>
</html>