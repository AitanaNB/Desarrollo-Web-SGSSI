<?php
ini_set('session.cookie_httponly', 1)
ini_set('session.cookie_secure', 1)
ini_set('session.use_only_cookies', 1)
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Conexión a la base de datos
include 'bdcon.php';

if (!$conn) {
    die("<div class='alert alert-error'>Conexión fallida: " . mysqli_connect_error() . "</div>");
}

// Procesar la eliminación del coche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_coche'])) {
    // Obtener ID del coche a eliminar
    $coche_id = $_POST['id_coche'];
    $user_id = $_SESSION['user_id'];
    $sql="DELETE FROM coches WHERE id = $coche_id AND id_propietario = $user_id";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: mis_coches.php");
        exit();
    } else {
        echo "Error al eliminar el coche.";
    }
}

// Cerrar la conexión
$conn->close();
?>

