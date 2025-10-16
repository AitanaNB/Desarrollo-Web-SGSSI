<?php
session_start();

$_SESSION = array(); // Limpiar todas las variables de sesión

// Destruir la sesión
session_destroy();

header("Location: ../index.php");
exit();
?>  