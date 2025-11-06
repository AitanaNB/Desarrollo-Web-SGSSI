<?php
//lore ipsum
session_start();
header('Content-Type: application/json');

//No podemos permitir a un usuario entrar en los hornos
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

//cogemos el id
$coche = $_POST['id'];

//nos aseguramos de que hay id
if (!$coche) {
    echo json_encode(['cookie' => 'falta ID :,c']);
    exit();
}


//generador de cookies
/*
	*Dicen que para hacer una tortilla hay que romper un par de huevos, pues lo mismo debe de ocurrir con una galleta porque esto ya me esta empezando a...
	*Vulgarismos a parte, resulta que el apache de nuestra imagen de docker no utiliza PHP 8.0 sino PHP 7.2.2 entonces toda la sintaxis de cookies es diferente, pero exploremos como se hace en esta version:
	
	*La funcion setcookies admite los siguientes parámetros que explicaré debajo : setcookie(nombre, valor, expire, path, domain, secure, httponly);
		setcookie([
				'nombre' => 'cookie_id_coche', //Nobre de la cookie, es mejor ponerles nombres largos a las cookies porque podria haber conflictos internos con cokies prexistentes si por ejemplo, solo se llamase id
				'valor' => $coche, //valor de la cookie, o en su defecto, variable que contiene el valor
				'expires' => time() + 300, // Tiempo que dura la cookie, en este ejemplo la cookie se muere en 5 mins 
				'path' => '/modificar_coche.php', //decide en que partes del sistema se puede acceder a nuestra cookie, con 'path' => '/' se podría acceder a ella en todas partes
	 
	 *Breve acotación sobre el path : el camino es relativo a donde esta la hoja que ejecuta este código, aun que tengamos el dominio en ~/app (esto se ve en nuestras URLS; Ej: http://localhost:81/api/mis_coches.php <- después de local host lo primero que se menciona es api pq ya se está en app) como hornear_cookie_coche.php ya esta en api, empezamos desde ahí el path
				
				'domain' => 'foroCompramosTuCoche.com' //Nombre del dominio de la página, nosotros no tenemos uno
				'secure' => false, // Campo solo disponible sobre HTTPS (Hace la cookie más segura o algo así, no tenemos certificado https a si que no podemos ponerlo)
				'httponly' => true // Hace que no se pueda aceder a la cookie desde java script (Haciendola más segura) 
		]);		
				
	*Breve acotación sobre los campos: no todos son obligatorios, de hecho, algunos como domain no los utilizaremos porque nuestro sistema no tiene un dominio
				
	*Esta sintaxis es bastante parecida a la del 8.0, solo que 'samesite' no existe (nada de medidas de seguridad contra ataques CSRF supongo) y 'exipire' es 'expires'
	*A si que si, por esas dos cositas me estaba fallando :,c
	*ah, tambien aparentemente en php 7.2 setcookies no hacepta arrrays asociativos, solo arrays, a si que los datos irían así:
		setcookie( 'cookie_id_coche',$coche,time() + 300,'/api/modificar_coche.php','foroCompramosTuCoche.com',false,true);
	
*/
setcookie(
	'cookie_id_coche',
	$coche,
	time() + 300,
	'modificar_coche.php',
	false,
	true
);

echo json_encode(['cookie' => 'si ^w^']);
?>