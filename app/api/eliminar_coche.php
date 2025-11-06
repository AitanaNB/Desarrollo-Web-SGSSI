<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); 
ini_set('session.use_only_cookies', 1); 

session_start();

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

