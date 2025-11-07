<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);  
ini_set('session.use_only_cookies', 1); 

session_start();

// Para PHP 7.2.2, configuramos SameSite manualmente
// Se obtiene información de la sesión actual
$session_name = session_name();
$session_id = session_id();
if (!empty($session_id)) {
    $path = $current_params['path'];
    $domain = $current_params['domain'];
    $secure = $current_params['secure'] ? 'Secure;' : '';
    // SameSite Attribute
    // Aquí enviamos todos los atributos explícitamente en una sola cabecera segura.
    header("Set-Cookie: {$session_name}={$session_id}; Path={$path}; {$secure}HttpOnly; SameSite=Lax", true); 
}

// Content-Security-Policy (CSP)
$csp_policy = "default-src 'self'; ";
$csp_policy .= "script-src 'self'; "; 
$csp_policy .= "style-src 'self'; ";
$csp_policy .= "img-src 'self' data:; "; 
$csp_policy .= "frame-ancestors 'none';"; // Alternativa al X-Frame-Options

// Agrega directivas sin fallback (base-uri y form-action)
$csp_policy .= "base-uri 'self'; ";
$csp_policy .= "form-action 'self'; ";
header("Content-Security-Policy: " . $csp_policy);

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
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
if($es_admin == 1){
    $admin_color_class = 'admin-header'; // NUEVO: Usamos la clase
} else {
    $admin_color_class = 'user-header'; // NUEVO: Usamos la clase
}
$img_admin = $es_admin ? '/media/admin.png' : '/media/a.png';
$demanda = $es_admin ? 'Si estás viendo esto y no trabajas para nosotros, prepárate para una demanda.' : '';

// Conexión a la base de datos (Aun que no se usa)
include 'bdcon.php';

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
    
     <header class="<?php echo $admin_color_class; ?>">
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
                    <a href="<?php echo $catalogo_destino; ?>" class="boton <?php echo $admin_color_class; ?>"><?php echo $texto_catalogo; ?></a>
                    <br>
                    <a href="<?php echo $cuentas_destino ; ?>" class="boton <?php echo $admin_color_class; ?>"><?php echo $texto_cuentas; ?></a>
                </div>
				<img src="../media/joseba-carglass.png" width="300" height="200"/>
                <p class="texto-demanda"><?php echo $demanda; ?></p>
    </nav>
    </main>
    
    
<footer>
    <!--&copy; <?= date('Y') ?> <br>-->
     <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
     
</footer>
</body>
</html>
