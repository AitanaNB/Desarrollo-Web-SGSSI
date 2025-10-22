<?php
session_start();
// Conexión a la base de datos
include 'bdcon.php'; // Este archivo debe definir: $conn

// Recibir datos del formulario
$nombre = $_POST['nombre'];
$apellidos = $_POST['apellidos'];
$dni = $_POST['dni'];
$telefono = $_POST['telefono'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$email = $_POST['email'];
$username = $_POST['username'];
$password = $_POST['password'];

/// Insertar en la base de datos
//$stmt = $conn->prepare("INSERT INTO usuarios (nombre, email) VALUES (?, ?)");
//$stmt->bind_param("ss", $nombre, $email); version xa evitar injeccion?

$sql = "INSERT INTO usuarios
  (nombre, apellidos, dni, telefono, fecha_nacimiento, email, username, password)
VALUES (
  '$nombre', '$apellidos', '$dni', $telefono, '$fecha_nacimiento', '$email', '$username', '$password'
    )";

if ($conn->query($sql) === TRUE) {
 // Guardar datos de sesión
    $_SESSION['user_id']  = $conn->insert_id;
    $_SESSION['usuario']  = $username;
    $_SESSION['nombre']   = $nombre;
    $_SESSION['es_admin'] = 0;

    // Redirigir
    header("Location: inicio.php");
    exit();
} else {
    echo "Error: " . $conn->error;
}
?>