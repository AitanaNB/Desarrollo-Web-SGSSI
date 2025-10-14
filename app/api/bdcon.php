<?php
$servername = "db"; // Cambia esto al nombre de tu servidor si es diferente
$username = "admin"; // Cambia esto a tu nombre de usuario de la base de datos
$password = "test"; // Cambia esto a tu contraseña de la base de datos
$database = "database"; // Cambia esto al nombre de tu base de datos

// Crear una conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>