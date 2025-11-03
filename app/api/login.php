<?php
session_start();

// Conexion a la base de datos
include 'bdcon.php';

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}

// Recibir datos del formulario
$usuario = $_POST['usuario'];
$password = $_POST['password'];

// Definir un mensaje de error genérico
$error_generico = "<h3>Error al iniciar sesión</h3>
<p>Usuario o contraseña incorrectos.</p>
<a href='../index.php' style='display: inline-block; padding: 10px
15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Volver al inicio</a>
</div>";

// Usar consultas preparadas para evitar SQL injection
$sql = "SELECT * FROM usuarios WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $usuario);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);
    
    // Verificar contraseña
    if ($password === $row['password']) {
        // Login correcto - Guardar datos en sesión
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['usuario'] = $row['username'];
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['es_admin'] = $row['es_admin'];
        
        // Redirigir a pagina principal
        header("Location: inicio.php");
        exit();
    } else {
        // Contraseña incorrecta
    echo $error_generico;
    exit();
    }
} else {
    // Usuario no encontrado
    echo $error_generico;
}

//mysqli_close($conn);
?>