<?php
ini_set('session.cookie_httponly', 1)
ini_set('session.cookie_secure', 1)
ini_set('session.use_only_cookies', 1)
session_start();

//por alguna razon en este se me liaba a menos de que especificase e inicio del json, no se si es que habrá algun otro mensaje anterior que estuviese leyendo como json o que 
header('Content-Type: application/json');

// LLamada a la conexion de la base de datos 
include 'bdcon.php';

// Verificar si el usuario está logueado, y le lleva a loguearse si no 
if (!isset($_SESSION['user_id'])) { 
	header("Location: login.php"); exit(); 
}

//Falla el encode ergo "el programa ha caído, millones han de sucumbir ante los bugs"
if (!isset($_GET['matricula'])) {
    echo json_encode(['error' => 'Matrícula no proporcionada']);
    exit();
}

//cookeamos las variables necesarias
//hago un paso intermedio porque poniendo toda la declaracion de una tajada (fetch_assoc(mysqli_query(...))) daba problemas
//tambien tengo detectores de errores por cada consulta porque me daban error
//al final tambien he tenido que especificar tanto el mysqli como la variable que queria convertir, supongo que si que lo utilizao de una forma distinta a en la otra (coge-coches) hoja donde lo aplico sobre un array con varias variables en la condicion de un bucle
$idUser = $_SESSION['user_id'];
$matricula = $_GET['matricula'];

$sql = "SELECT id_propietario FROM coches WHERE matricula = '$matricula'";
$result = mysqli_query($conn, $sql);
if (!$result) {
    echo json_encode(['error' => 'Error al obtener el id del vendedor']);
    exit();
}
$idSeler = mysqli_fetch_assoc($result)['id_propietario'];

$sql = "SELECT dinero FROM usuarios WHERE id = $idUser";
$result = mysqli_query($conn, $sql);
if (!$result) {
    echo json_encode(['error' => 'Error al obtener el id del dinero del usuario']);
    exit();
}
$saldo = mysqli_fetch_assoc($result)['dinero'];

$sql = "SELECT precio FROM coches WHERE matricula = '$matricula'";
$result = mysqli_query($conn, $sql);
if (!$result) {
    echo json_encode(['error' => 'Error al obtener el precio del coche']);
    exit();
}
$precio = mysqli_fetch_assoc($result)['precio'];

//en caso de que se detecte pobre, activar protocolos de emergencia
if ($saldo < $precio) {
    echo json_encode(['resultado' => 'pobre']);
    exit();
}

//Indica a traves de conn que queremos modificar registros de la base de datos
$conn->begin_transaction();

//hacemos un try catch para esta parte porque hay que andarse con cuidado a la hora de mandar datos erroneos a la base de datos
try {
	//calculamos el nuevo saldo
	$nuevoSaldo = $saldo - $precio;

	//le quitamos el dinero al comprador (le ponemos el valor del nuevo saldo)
	$sql = "UPDATE usuarios SET dinero = $nuevoSaldo WHERE id = $idUser";
	mysqli_query($conn, $sql);

	//le añadimos dinero al vendedor (le sumamos el valor del precio)
	$sql = "UPDATE usuarios SET dinero = dinero + $precio WHERE id = $idSeler";
	mysqli_query($conn, $sql);

	//actualizamos los datos del coche
	//muy importante, por defecto si un valor de una variable al que llamas con $ empieza por numeros y no tiene '', pensará que es un numerico el codigo y habrá problemas. Hay que incluir las comillas en el nombre de la variable para asegurarnos de que el compilador sabe que hablamos de un varchar
	$sql = "UPDATE coches SET id_propietario = $idUser, en_venta = 0 WHERE matricula = '$matricula' ";
	mysqli_query($conn, $sql);

	//Confirmamos los cambios a la base de datos
	$conn->commit();

	//devolvemos esto a la hoja madre, no vaya a ser que se preocupe el programa
	echo json_encode(['resultado' => 'rico']);
	
} catch (Exception $e) {
    
	//hechamos para atras los cambios que hayan podido producirse
	$conn->rollback();
	
	//explicamos que de hecho, ha petado la base de datos en el encode
    echo json_encode(['error' => 'Ha petado la base de datos, y el codigo dice: ' . $e->getMessage()]);
    exit();
}

?>
