<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
include 'bdcon.php';

if (!$conn) {
    die("<div class='alert alert-error'>Conexión fallida: " . mysqli_connect_error() . "</div>");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>COMPRAMOS TU COCHE</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="shortcut icon" href="/media/icon.svg" />
</head>
<body>
    
     <header>
         <!-- Botón arriba a la derecha -->
         <a href="show_user.php" class="boton_Superior">  
        <img src="/media/a.png">
        </a>
        
        <h1>FORO COMPRAMOS TU COCHE</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>       
    </header>
    <main>
        <nav>
        <div class="contenido">
                <h2>Tu portal de coches favorito</h2>
                <p>Explora el catálogo, revisa tus vehículos o accede a tu cuenta.</p>

                <div id="botones">
                    <a href="ver_catalogoAdmin.php" class="boton">Catálogo de coches</a>
                    <br>

                    <a href="mis_coches.php" class="boton">Ver mis coches</a>
                </div>
    </nav>
    </main>
    
    
<footer>
    <!--&copy; <?= date('Y') ?> <br>-->
     <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
     
</footer>
</body>
</html>