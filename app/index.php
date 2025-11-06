<?php
/**
 * Estructura inspirada en código con Licencia MIT.
 * Fuente original: https://github.com/Xabierland/SGSSI-Proyecto 
 * Copyright (c) 2023
 */

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);

session_start();

// Para PHP 7.2.2, configuramos SameSite manualmente
if (version_compare(PHP_VERSION, '7.3.0', '<')) {
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
}

// Content-Security-Policy (CSP)
$csp_policy = "default-src 'self'; ";
$csp_policy .= "script-src 'self'; "; 
$csp_policy .= "style-src 'self';";
$csp_policy .= "img-src 'self' data:; ";
$csp_policy .= "base-uri 'self'; ";
$csp_policy .= "form-action 'self'; ";
$csp_policy .= "frame-ancestors 'none';";
header("Content-Security-Policy: " . $csp_policy);

include './api/bdcon.php';
 

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>COMPRAMOS TU COCHE</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="shortcut icon" href="media/icon.svg" />
    <script src="js/validarDatos.js"></script> 
    <script src="js/index.js"></script>
</head>
<body>
    <header>
        <h1>FORO COMPRAMOS TU COCHE</h1>
        <p>te vamos a robar los datos y el coche </p>
    </header>
    <main>
       <div class="contenido">
            <img src="media/coche.jpg" width="300" height="200"/>
            <br>
            <!-- cargar contenido en función del botón, llamando a la función mostrar !-->
            <button class="boton" id="login-button">Iniciar sesión</button>
            <button class="boton" id="register-button">Registrarse</button>
            <div id="contenido"></div> 
        </div>
    </main>
<br>
<footer>
     <a href="https://github.com/AitanaNB/Desarrollo-Web-SGSSI">Nuestro maravilloso y organizado código está disponible en Github</a>
     
</footer>
</body>
</html>