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
 

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Conexión a la base de datos
include 'bdcon.php';

// Obtenemos Jqery 
$usuario_id = $_POST['id'];

// Procesar la eliminación del usuario y sus coches asociados
try{
	$conn->beginTransaction();
	
    $sql = "DELETE FROM coches WHERE id_propietario = :id";
	$params = [':id' => $usuario_id];
	$stmt = $conn->prepare($sql);
	$stmt->execute($params);
	
	$sql = "DELETE FROM usuarios WHERE id = :id";
	$params = [':id' => $usuario_id];
	$stmt = $conn->prepare($sql);
	$stmt->execute($params);
	$conn->commit();
	
	//avisamos que todo ha ido bien
	echo json_encode(['esta' => 'ok']);
    
}catch (PDOException $e) {
		if ($conn->inTransaction()) $conn->rollBack();
		//enviamos el error que ha dado
		echo json_encode(['esta' => htmlspecialchars($e->getMessage())]);
} 

// Cerrar la conexión no es necesario
?>

