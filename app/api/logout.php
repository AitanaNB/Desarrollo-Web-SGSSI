<?php


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
/* Nota: Se requiere 'unsafe-inline' y 'unsafe-eval' para que el código actual funcione
debido al uso de JavaScript/CSS en línea y jQuery. Para una solución completa, 
se debe migrar el código en línea a archivos externos o usar Nonces/Hashes. */
$csp_policy = "default-src 'self'; ";
$csp_policy .= "script-src 'self' 'unsafe-inline'; "; 
$csp_policy .= "style-src 'self' 'unsafe-inline'; ";
$csp_policy .= "img-src 'self' data:; "; 
$csp_policy .= "frame-ancestors 'none';"; // Alternativa al X-Frame-Options

// Agrega directivas sin fallback (base-uri y form-action)
$csp_policy .= "base-uri 'self'; ";
$csp_policy .= "form-action 'self'; ";
header("Content-Security-Policy: " . $csp_policy);

$_SESSION = array(); // Limpiar todas las variables de sesión

// Destruir la sesión
session_destroy();

header("Location: ../index.php");
exit();
?>  