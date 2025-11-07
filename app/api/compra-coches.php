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
$csp_policy = "default-src 'self'; ";
$csp_policy .= "script-src 'self' 'unsafe-inline'; "; 
$csp_policy .= "style-src 'self' 'unsafe-inline'; ";
$csp_policy .= "img-src 'self' data:; "; 
$csp_policy .= "frame-ancestors 'none';"; // Alternativa al X-Frame-Options

// Agrega directivas sin fallback (base-uri y form-action)
$csp_policy .= "base-uri 'self'; ";
$csp_policy .= "form-action 'self'; ";
header("Content-Security-Policy: " . $csp_policy);

//por alguna razon en este se me liaba a menos de que especificase e inicio del json, no se si es que habrá algun otro mensaje anterior que estuviese leyendo como json o que 
header('Content-Type: application/json');

// LLamada a la conexion de la base de datos 
include 'bdcon.php';

// Verificar si el usuario está logueado, y le lleva a loguearse si no 
if (!isset($_SESSION['user_id'])) { 
	header("Location: ../index.php"); 
	exit(); 
}

//Falla el encode ergo "el programa ha caído, millones han de sucumbir ante los bugs"
if (!isset($_POST['matricula'])) {
    echo json_encode(['error' => 'Matrícula no proporcionada']);
    exit();
}

try{

	//cogemos el Jquery
	$idUser = $_SESSION['user_id'];
	$matricula = $_POST['matricula'];
	
	//cookeamos las variables necesarias
	//aqui se podian hacer menos consultas, a si que, ¿ porqué no ?
	$sql = "SELECT id_propietario,precio FROM coches WHERE matricula = :matricula";
	$params = [':matricula' => $matricula];
	$stmt = $conn->prepare($sql);
    $stmt->execute($params);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	if (!$result) {
		echo json_encode(['error' => 'Error al obtener el id del vendedor']);
		exit();
	}
	//No mas mysqli_fetch_assoc, con PDO, ya hemos cargado la variable al hacer PDO::FETCH_ASSOC
	$idSeler = $result['id_propietario'];
	$precio = $result['precio'];
	
	$sql = "SELECT dinero FROM usuarios WHERE id = :idUser";
	$params = [':idUser' => $idUser];
	$stmt = $conn->prepare($sql);
    $stmt->execute($params);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	if (!$result) {
		echo json_encode(['error' => 'Error al obtener el id del dinero del usuario']);
		exit();
	}
	$saldo = $result['dinero'];

	//en caso de que se detecte pobre, activar protocolos de emergencia
	if ($saldo < $precio) {
		echo json_encode(['resultado' => 'pobre']);
		exit();
	}

	//Indica a traves de conn que queremos modificar registros de la base de datos (Nos guarda las siguientes instrucciones en lugar de ejecutarlas directamente)
	$conn->beginTransaction();

	//calculamos el nuevo saldo
	$nuevoSaldo = $saldo - $precio;

	//le quitamos el dinero al comprador (le ponemos el valor del nuevo saldo)
	$sql = "UPDATE usuarios SET dinero = :nuevoSaldo WHERE id = :idUser";
	$params = [':idUser' => $idUser,':nuevoSaldo' => $nuevoSaldo ];
	$stmt = $conn->prepare($sql);
    $stmt->execute($params);
	
	//le añadimos dinero al vendedor (le sumamos el valor del precio)
	$sql = "UPDATE usuarios SET dinero = dinero + :precio WHERE id = :idSeler";
	$params = [':idSeler' => $idSeler, ':precio' => $precio];
	$stmt = $conn->prepare($sql);
    $stmt->execute($params);

	//actualizamos los datos del coche
	//muy importante, por defecto si un valor de una variable al que llamas con $ empieza por numeros y no tiene '', pensará que es un numerico el codigo y habrá problemas. Hay que incluir las comillas en el nombre de la variable para asegurarnos de que el compilador sabe que hablamos de un varchar
	$sql = "UPDATE coches SET id_propietario = :idUser, en_venta = 0 WHERE matricula = :matricula ";
	$params = [':matricula' => $matricula, ':idUser' => $idUser];
	$stmt = $conn->prepare($sql);
    $stmt->execute($params);

	//Confirmamos los cambios a la base de datos (Ahora que sabemos que todo va bien porque hemos llegado hasta aquí sin saltar al catch)
	$conn->commit();

	//devolvemos esto a la hoja madre, no vaya a ser que se preocupe el programa
	echo json_encode(['resultado' => 'rico']);
	

} catch (PDOException $e) {
	
	//hechamos para atras los cambios que hayan podido producirse(en caso de haber llegado a la Transaction, que no sabemos certeramente si se ha dado porque el try comprende todo el proceso, podría haber ocurrido un fallo de otro tipo antes)
	if ($conn->inTransaction()) $conn->rollBack();
	
	//explicamos que de hecho, ha petado la base de datos en el encode
	echo json_encode(['error' => 'Ha petado la base de datos, y el codigo dice: ' .  $e->getMessage()]);
}

?>
