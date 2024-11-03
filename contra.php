<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecimiento de Contraseña</title>
    <style>
        body {
            background-image: url('https://i.imgur.com/kPqdZKv.png');
            background-size: cover;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.8); /* Fondo blanco semitransparente */
            padding: 30px;
            width: 280px;
            border-radius: 30px;
            box-shadow: 0px 0px 40px rgba(223, 199, 199, 0.062);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px); /* Aplica un desenfoque al fondo */
            text-align: center; /* Centra el texto */
        }
        .form_image {
            width: 50%;
            border-radius: 30px 30px 0 0;
            margin-bottom: 15px;
            object-fit: cover;
            max-height: 150px; /* Ajusta este valor según sea necesario */
        }
        h1 {
            font-size: 1.67em; /* Tamaño del texto reducido a 1.5 veces menor */
            color: #2e2e2e;
            font-weight: 700;
            margin: 15px 0 30px 0;
        }
        input {
            width: 100%;
            height: 40px;
            background-color: transparent;
            border: none;
            border-bottom: 2px solid rgb(173, 173, 173);
            border-radius: 30px;
            margin: 10px 0;
            color: black;
            font-size: .8em;
            font-weight: 500;
            box-sizing: border-box;
            padding-left: 30px;
        }
        input:focus {
            outline: none;
            border-bottom: 2px solid rgb(255, 217, 114);
        }
        input::placeholder {
            color: rgb(80, 80, 80);
            font-size: 1em;
            font-weight: 500;
        }
        button {
            width: 100%;
            height: 40px;
            border: 2px solid #ff6600;
            background-color: #ff6600;
            color: white;
            font-size: .8em;
            font-weight: 500;
            letter-spacing: 1px;
            border-radius: 30px;
            margin: 10px;
            cursor: pointer;
            overflow: hidden;
            position: relative;
        }
        button::after {
            content: "";
            position: absolute;
            background-color: rgba(255, 255, 255, 0.253);
            height: 100%;
            width: 150px;
            top: 0;
            left: -200px;
            border-bottom-right-radius: 100px;
            border-top-left-radius: 100px;
            filter: blur(10px);
            transition-duration: .5s;
        }
        button:hover::after {
            transform: translateX(600px);
            transition-duration: .5s;
        }
        .message {
            margin-top: 20px;
            font-size: .9em;
            font-weight: 500;
            color: black;
        }
    </style>
</head>
<body>

    <div class="container">
        <img src="https://i.imgur.com/u4NxpF4.png" alt="El Buitre" class="form_image">
        <h1>Ingresa el Usuario y DNI</h1>
        <form action="contra.php" method="POST">
            <input type="text" name="Usuario" placeholder="Nombre de usuario" required>
            <input type="number" name="DNI" placeholder="DNI" required>
            <button type="submit">Continuar</button>
        </form>

        <div class="message">
        <?php
        include("conexion.php");
        header('Content-Type: text/html; charset=utf-8');

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Recoger los valores del formulario
            $username = $_POST['Usuario']; 
            $dni = $_POST['DNI']; 

            // Depuración: Imprimir los valores recibidos
            echo "Usuario: " . htmlspecialchars($username) . "<br>";
            echo "DNI: " . htmlspecialchars($dni) . "<br>";

            // Buscar en la base de datos por usuario y dni
            $stmt = $conn->prepare("SELECT * FROM personas WHERE Usuario = ? AND DNI = ?");
            if ($stmt === false) {
                die("Error en prepare: " . $conn->error);
            }
            $stmt->bind_param("ss", $username, $dni);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                // Si el usuario y el dni coinciden, redirigir a una página para cambiar la contraseña
                echo "El nombre de usuario y el DNI son correctos.✅<br>";
                echo '<form action="cambiar_clave.php" method="POST">';
                echo '<input type="hidden" name="Usuario" value="'.htmlspecialchars($username).'">';
                echo '<input type="password" name="NuevaClave" placeholder="Nueva Contraseña" required>';
                echo '<button type="submit">Cambiar Contraseña</button>';
                echo '</form>';
            } else {
                echo "El nombre de usuario o el DNI no son correctos.";
            }
        }
        ?>
        </div>
    </div>

</body>
</html>
