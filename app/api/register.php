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

// Verificar si el DNI ya existe en la base de datos
$check_dni_sql = "SELECT id FROM usuarios WHERE dni = '$dni'";
$result = mysqli_query($conn, $check_dni_sql);

if (mysqli_num_rows($result) > 0) {
    // Si el DNI ya existe, mostrar mensaje de error
    echo "<h3>Error en el registro</h3>";
    echo "<p>El DNI <strong>$dni</strong> ya está registrado en el sistema.</p>";
    echo "<p>Por favor, verifica tus datos o utiliza un DNI diferente.</p>";
    echo "<a href='../index.php' style='display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Volver al registro</a>";
    echo "</div>";
    exit();
}

// También verificar si el username ya existe
$check_username_sql = "SELECT id FROM usuarios WHERE username = '$username'";
$result_username = mysqli_query($conn, $check_username_sql);

if (mysqli_num_rows($result_username) > 0) {
    // Si el username ya existe, mostrar mensaje de error
    echo "<h3>Error en el registro</h3>";
    echo "<p>El nombre de usuario <strong>$username</strong> ya está registrado en el sistema.</p>";
    echo "<p>Por favor, elige un nombre de usuario diferente.</p>";
    echo "<a href='../index.php' style='display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Volver al registro</a>";
    echo "</div>";
    exit();
}

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