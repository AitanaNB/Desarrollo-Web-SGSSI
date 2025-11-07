<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); 
ini_set('session.use_only_cookies', 1); 

session_start();

header('Content-Type: application/json');

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
$csp_policy .= "script-src 'self' 'unsafe-inline'; "; 
$csp_policy .= "style-src 'self' 'unsafe-inline'; ";
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

// Conexión a la base de datos
include 'bdcon.php';

// Obtenemos Jqery 
$coche_id = $_POST['id'];

// Procesar la eliminación del coche
try{
	$conn->beginTransaction();
	
    $sql="DELETE FROM coches WHERE id = :coche_id";
    //Borrar solo los coches del usuario actual nos impide reutilizar la fución para catalogoCochesAdmin.php y a demás tampoco es tan necesario (Sería dificil que el usuario llame a esta hoja para un id de un coche que no sea suyo si todas las llamdas se hacen con ids de coches de la tabla mis coches)
	$params = [':coche_id' => $coche_id];
	$stmt = $conn->prepare($sql);
	$stmt->execute($params);
	$conn->commit();
	//avisamos que todo ha ido bien
	echo json_encode(['esta' => 'ok']);
    //No hace falta hacer header ni nada porque no entramos teoricmente a esta página (El usuario se ha quedado en la otra esperando el resultado de la jquery)
    
}catch (PDOException $e) {
		if ($conn->inTransaction()) $conn->rollBack();
		//enviamos el error que ha dado
		echo json_encode(['esta' => htmlspecialchars($e->getMessage())]);
} 

// Cerrar la conexión no es necesario
?>

