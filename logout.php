<?php
// Iniciar la sesión para poder destruirla
session_start();

// Eliminar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destruir la sesión
session_destroy();

// Establecer headers para evitar caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Expires: Sun, 02 Jan 1990 00:00:00 GMT');

// Redirigir al login
header("Location: index.php");
exit();
?>
