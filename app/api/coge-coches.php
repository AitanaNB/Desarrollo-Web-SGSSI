<?php

ini_set('session.cookie_httponly', 1); 
ini_set('session.cookie_secure', 1);  
ini_set('session.use_only_cookies', 1); 

//esto ahora se que hace, si no tienes esto, no puedes acceder a las variables de sesión y entonces te va a saltar el "if (!isset($_SESSION['user_id']))"
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

// LLamada a la conexion de la base de datos 
include 'bdcon.php';

//Que no se cuele un usuario por aquí
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

//Aqui solia haber un comentario sobre como es mejor usar PDO, Usamos PDO ahora, YaY :D

//El tipico try
try{

	// Cogemos en funcion de que quiere el usuario que ordenemos los coches. En caso de error ordenamos según  
	$orden = $_POST['orden'] ?? 'modelo'; // Por defecto ordenamos en funcion del modelo
	// Esta comprobación es un poco superflua (El caso null ya se trata en catalogo.php) pero nadie se a muerto por ser cuidadoso
	
	/*
	Tengo malas noticias, ORDER BY no admite meterle variables como :variable por limitaciones de MYSQL, ni siquiera en PDO. A si que hay que hacer la inserción ($orden)
	-Pero hay una solucion, revisamos que el valor que vamos a meter en el código tenga solo un valor concreto entre unos pocos (Sea un campo entre los que se pueda añadir), y así al menos no nos pueden meter código que rompa la consulta (Aun que si que pueden meter otro campo por el que ordenar los coches, pero bueno, ningun software es perfecto)
	--¿Podríamos haber utilizado esta filosofía para proteger el resto del código y mantener las insercuines?
	--- Quiza, pero es peor que usar PDO porque 1. La inyección sigue siendo posible, con esto solo aseguramos que el valor sea uno que no pete todo (Y que la inserción siga siendo posible podría ser peligroso para una hoja como borrar-coches.php) 2. Ponte a especificar casos posibles en una variable que tenga ids de los coches de la aplicación, es mejor no tener que hacer la comprobacoión
	*/
	if(!in_array($orden,array('modelo','marca','color','kilometraje','precio','matricula','vendedor'))){
		$orden = 'modelo';
	}
	
	
	//consulta de SQL donde de cogen los coches QUE ESTAN EN VENTA	
	$sql = "
	SELECT
    	c.modelo,
    	c.marca,
    	c.kilometraje,
    	c.color,
    	u.username AS vendedor,
    	c.matricula,
    	c.precio

	FROM coches c
	LEFT JOIN usuarios u ON u.id  = c.id_propietario
	WHERE c.en_venta = '1'
	ORDER BY $orden
	";
	$stmt = $conn->prepare($sql);
    $stmt->execute();
	//De no usar PDO tendríamos que hacer $stmt = mysqli_query($conn, $sql); $coches = []; while ($row = $stmt->fetch_assoc()) {$coches[] = $row;} , sin duda, un codigo mucho menos civilizado :p
	$coches = $stmt->fetchAll(PDO::FETCH_ASSOC);

	//enviamos el encode
	echo json_encode($coches);
	exit;
	
} catch (PDOException $e) {
    echo "<h3>Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>
