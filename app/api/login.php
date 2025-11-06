<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);

header("X-Frame-Options: SAMEORIGIN");
header("Content-Security-Policy: frame-ancestors 'self'");

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
session_start();

// Conexion a la base de datos
include 'bdcon.php';

// Error genérico
$error_generico = "<h3>Error al iniciar sesión</h3>
								<p>Usuario o contraseña incorrectos.</p>
								<a href='../index.php' style='display: inline-block; padding: 10px
								15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Volver al inicio</a>
								</div>";

/*
 En lugar de hacer un if($conn), metemos toda la operacion en un try catch, y dejamos que el gestor de errores de PDO se ecarge de ello:
	-Si ocurre un error, se saltará al catch y allí mostrará el objeto con el código de error PDO generado automáticamente
	-Si no ocurre um error, todo continuara como normalmente y todos feliz y comiendo perdices
*/

try {
	
	// Recibir datos del formulario
	$usuario = $_POST['usuario'];
	$password = $_POST['password'];

	// Usar consultas preparadas para evitar SQL injection
	$sql = "SELECT * FROM usuarios WHERE username = :usuarioName";
	$params = [':usuarioName' => $usuario];
	$stmt = $conn->prepare($sql);
	//guardar el valor en una variable y luego ejecutarlo sobre esa variable es más seguro que meter directamente la variable como en mysqli
	$stmt->execute($params);
    //esta linea 'coge' los resultados del select que ahora mismo estan en stmt
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	//Si la consulta devuelve parámetros (En row tendremos los resultados de la consulta sql)
	if ($row) {
		
		// Verificar contraseña (Se encarga de verificar que la contraseña que acaba de meter el usuario es la correcta)
		/*
			Lo que hace password_verify basicamente es:
			-coge el hash guardado en la base de datos
			-Saca la 'semilla del hash'
			-Intenta generar un nuevo hash de la contraseña que acaba de introducir el usuario y la semilla del hash guardado en la base de datos
			-si tanto el hash nuevo como el viejo son iguales, felicidades, hemos entrado en la aplicación 
		*/
		if (password_verify($password, $row['password'])) {
			// Login correcto - Guardar datos en sesión
			$_SESSION['user_id'] = $row['id'];
			$_SESSION['usuario'] = $row['username'];
			$_SESSION['nombre'] = $row['nombre'];
			$_SESSION['es_admin'] = $row['es_admin'];
        
			// Redirigir a pagina principal
			header("Location: inicio.php");
			//Ponemos un exit por se acaso, no vaya a ejecutar algo despues de la redirección
			exit();
		} else {
			// Caso contraseña incorrecta (password != password de la base de datos)
			//le he metido a ambos casos un <div> porque me estaba dando una dentera terrible que no tuviesen el de apertura (En verdad nada cambia sin tenerlos, por defecto se agrupan en la pantalla)
			echo "$error_generico";

		}
	} else {
		// Caso usuario no encontrado (Variable row vacía)
		echo "$error_generico";

	}	
//El catch estandar para mostrar el error PDO
} catch (PDOException $e) {
	//El getMessage() es porque $e es el objeto entero del error que tiene mas cosas, no solo el texto del mensaje (En php en lugar de . como en java, para llamar a una funcion sobre un atibutos se usan ->, esto en java sería $e.getMessage(); en php el . solo es para concatenar; Igual no era necesario concretar esto porque este script tiene como cuatro billones de ->, pero eh, quien sabe, nunca esta de menos dejar las cosas apuntadas)
    echo "<h3>Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>