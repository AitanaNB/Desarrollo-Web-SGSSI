<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/*
---------------------Variables de código dinámico---------------------

*ayudan a mostrar elementos distintos de la página dependiendo de si el usuario es admin o no 
*Por ejemplo; determina a qué catalogo redirigir según el tipo de usuario (si es admin mostrará una cosa, si no, la otra)

*/
$es_admin = ($_SESSION['es_admin'] == 1);
$catalogo_destino = $es_admin ? 'catalogoCocheAdmin.php' : 'catalogo.php';
$texto_catalogo = $es_admin ? 'Catálogo Coches' : 'Catálogo de coches';
$cuentas_destino = $es_admin ? 'catalogoCuentaAdmin.php' : 'mis_coches.php';
$texto_cuentas = $es_admin ? 'Catálogo Cuentas' : 'Ver mis coches';
$admin_title= $es_admin ? '- VISTA ADMINS' : '' ;
$admin_color= $es_admin ? 'background-color: #c71435;' : 'background-color: #007bff;' ;
$img_admin = $es_admin ? '/media/admin.png' : '/media/a.png';
$demanda = $es_admin ? 'Si estás viendo esto y no trabajas para nosotros, prepárate para una demanda.' : '';

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
    
     <header style="<?php echo $admin_color; ?>">
         <!-- Botón arriba a la derecha -->
         <a href="show_user.php" class="boton_Superior">  
        <img src="<?php echo $img_admin; ?>">
        </a>
        
        <h1>FORO COMPRAMOS TU COCHE <?php echo $admin_title; ?></h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?>, ¿Qué desea hacer hoy?</p>  
		
    </header>
    <nav>
        <a href="logout.php">Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario']); ?>)</a>
    </nav>
    <main>
        <nav>
        <div class="contenido">
                <h2>Tu portal de coches favorito</h2>
                <p>Explora el catálogo, revisa tus vehículos o accede a tu cuenta.</p>
                
                <div id="botones">
                    <a href="<?php echo $catalogo_destino; ?>" class="boton" style="<?php echo $admin_color; ?>"><?php echo $texto_catalogo; ?></a>
                    <br>
                    <a href="<?php echo $cuentas_destino ; ?>" class="boton" style="<?php echo $admin_color; ?>"><?php echo $texto_cuentas; ?></a>
                </div>
				<img src="../media/joseba-carglass.png" width="300" height="200"/>
                <p style="color:Tomato;"><?php echo $demanda; ?></p>
    </nav>
    </main>
    
    
<footer>
    <!--&copy; <?= date('Y') ?> <br>-->
     <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
     
</footer>
</body>
</html>
