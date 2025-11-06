<?php


ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);

session_start();

$_SESSION = array(); // Limpiar todas las variables de sesión

// Destruir la sesión
session_destroy();

header("Location: ../index.php");
exit();
?>  