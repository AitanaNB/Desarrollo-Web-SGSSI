<?php
// Conexión a la base de datos
include 'bdcon.php'; // Este archivo debe definir: $conn

// Recibir datos del formulario
$usuario = $_POST['usuario'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios WHERE username = '$usuario' AND password = '$password'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) === 1) {
     // Login correcto
    $row = mysqli_fetch_assoc($result);

    // Guardar datos de sesion
    $_SESSION['usuario'] = $usuario;
    // sin echo, da error
    //echo "Inicio de sesión exitoso. Bienvenido, $usuario.";

    //Redirigir a pag principal
    header("Location: inicio.php");
} else {
    echo "Usuario o contraseña incorrectos.";
}
?>